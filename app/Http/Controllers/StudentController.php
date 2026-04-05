<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Booking;
use App\Models\Student;
use App\Models\EnrollmentRequest;
use App\Models\Course;
use App\Models\PhaseProgression;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http\Requests\StoreEnrollmentRequestRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\EnrollmentRequestReceived;
use App\Models\Notification;
use App\Models\Admin;

class StudentController extends Controller
{
    /**
     * Handle enrollment request for Active Students (Bypasses Guest lock)
     */
    public function enroll(StoreEnrollmentRequestRequest $request, School $school, Course $course)
    {
        Log::info('Student Enroll method called', [
            'school' => $school->id,
            'course' => $course->id,
            'user' => Auth::guard('student')->user()?->id
        ]);

        $student = Auth::guard('student')->user();

        // Ensure the course belongs to this school
        if ($course->school_id !== $school->id) {
            abort(403, 'This course does not belong to this school.');
        }

        // PDC (Practical Driving Course) requires a verified Student Driver's License
        /** @var \App\Models\Student $student */
        $canEnroll = \App\Support\EnrollmentValidator::canEnrollInCourse($student, $course);
        if (!$canEnroll['allowed']) {
            Log::warning('Student enrollment blocked', [
                'user' => $student->id,
                'course' => $course->id,
                'message' => $canEnroll['message']
            ]);
            return redirect()->back()->with('error', $canEnroll['message']);
        }

        // School-scoped single-active guard (single active enrollment per school).
        $hasActiveEnrollment = EnrollmentRequest::where('learner_id', $student->id)
            ->where('school_id', $school->id)
            ->where('status', 'approved')
            ->exists();

        if ($hasActiveEnrollment) {
            return redirect()->back()->with('error', 'You already have an active course in this school. Complete or cancel it before enrolling in another course.');
        }

        // Check if already enrolled for this course (excluding previous rejections/cancellations)
        $existingRequest = EnrollmentRequest::where('learner_id', $student->id)
            ->where('course_id', $course->id)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->first();

        if ($existingRequest) {
            if (in_array($existingRequest->status, ['pending', 'approved'])) {
                return redirect()->back()->with('warning', 'You already have an active enrollment request for this course.');
            }
        }

        try {
            $data = [
                'school_id' => $school->id,
                'learner_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'requested_license_type' => $course->license_type ?? 'non_professional',
                'experience_level' => $request->experience_level,
                'package_id' => $request->input('package_id'),
                'remarks' => $request->notes,
                'location' => $request->location ?? $student->location,
                'branch_id' => $request->input('branch_id'),
            ];

            // Snapshot the price
            if ($request->filled('package_id')) {
                $package = \App\Models\CoursePackage::find($request->package_id);
                $data['price'] = $package ? $package->price : $course->price;
            } else {
                $data['price'] = $course->price;
            }

            // Handle credential file upload for experienced drivers
            if ($request->hasFile('credential_file')) {
                $file = $request->file('credential_file');
                $path = $file->store('credentials', 'local');
                $data['credentials_file_path'] = $path;
            }

            $enrollmentRequest = EnrollmentRequest::create($data);
        } catch (\Exception $e) {
            Log::error('Failed to create student enrollment request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to submit enrollment request. Please try again.');
        }

        try {
            // Send confirmation email
            Mail::to($student->email)
                ->send(new EnrollmentRequestReceived($enrollmentRequest, $school));
        } catch (\Exception $e) {
            Log::warning('Failed to send student enrollment received email: ' . $e->getMessage());
        }

        try {
            // Create in-app notification for the student
            Notification::send(
                $student,
                'enrollment_received',
                'Enrollment Request Submitted',
                "Your enrollment request for {$course->title} has been submitted. Proceed to Payments to upload your receipt.",
                'enrollment',
                "/{$school->slug}/student/payments?enrollment_id={$enrollmentRequest->id}"
            );

            // Notify all admins of this school
            $admins = Admin::where('school_id', $school->id)->where('is_active', true)->get();
            foreach ($admins as $admin) {
                Notification::send(
                    $admin,
                    'new_enrollment_request',
                    'New Course Enrollment',
                    "Active Student {$student->name} has requested enrollment in {$course->title}.",
                    'enrollment',
                    "/{$school->slug}/admin/enrollments"
                );
            }
        } catch (\Exception $e) {
            Log::warning('Student enrollment created but notification dispatch failed: ' . $e->getMessage());
        }

        Log::info('Student enrollment request created successfully', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'experience_level' => $request->experience_level
        ]);

        return redirect()
            ->route('schools.student.payments.index', ['school' => $school->slug, 'enrollment_id' => $enrollmentRequest->id])
            ->with('success', 'Your enrollment request has been submitted. Please upload your payment receipt to continue approval.');
    }

    public function dashboard(Request $request, School $school)
    {
        $student = Auth::guard('student')->user();
        $studentModel = Student::with('branchRelation')->find($student->id);
        
        // Get active enrollments with course and session data
        $activeEnrollments = EnrollmentRequest::where('learner_id', $student->id)
            ->where('school_id', $school->id)
            ->with(['course', 'sessionCompletions'])
            ->where('status', 'approved')
            ->get();
        
        // Calculate hours from session completions (actual logged hours)
        $hoursCompleted = 0;
        $totalSessionsCompleted = 0;
        foreach ($activeEnrollments as $enrollment) {
            $completions = $enrollment->sessionCompletions ?? collect();
            $hoursCompleted += $completions->where('status', 'completed')->sum('hours_completed');
            $totalSessionsCompleted += $completions->where('status', 'completed')->count();
        }
        
        // Total required hours from enrolled courses
        $requiredHours = $activeEnrollments->sum(function($enrollment) {
            return $enrollment->course->hours_required ?? 0;
        });
        
        // Get all bookings for this student
        $allBookings = Booking::where('student_id', $student->id)
            ->with([
                'timeSlot:id,date,start_time,end_time',
                'instructor:id,name,email',
                'course:id,title,duration_hours'
            ])
            ->select('id', 'student_id', 'instructor_id', 'course_id', 'time_slot_id', 'status', 'scheduled_at')
            ->get();
        
        // Total scheduled sessions (all statuses)
        $totalScheduledSessions = $allBookings->count();
        $completedBookings = $allBookings->where('status', 'completed')->count();
        
        // Get upcoming lessons (next 7 days) - only confirmed/scheduled
        $upcomingLessons = Booking::where('student_id', $student->id)
            ->whereIn('status', ['confirmed', 'scheduled'])
            ->where('booking_date', '>=', Carbon::now()->toDateString())
            ->where('booking_date', '<=', Carbon::now()->addDays(7)->toDateString())
            ->count();
        
        // Get next lessons for display - only confirmed/scheduled
        $nextLessons = Booking::where('student_id', $student->id)
            ->whereIn('status', ['confirmed', 'scheduled'])
            ->where('booking_date', '>=', Carbon::now()->toDateString())
            ->with([
                'timeSlot:id,date,start_time,end_time',
                'instructor:id,name',
                'course:id,title'
            ])
            ->orderBy('booking_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function($booking) {
                return (object)[
                    'course' => $booking->course,
                    'instructor' => $booking->instructor,
                    'date' => $booking->timeSlot->date ?? $booking->booking_date,
                    'timeSlot' => $booking->timeSlot,
                ];
            });
        
        // Calculate progress percentage based on hours
        $progressPercentage = $requiredHours > 0 ? min(100, round(($hoursCompleted / $requiredHours) * 100)) : 0;
        
        // Theoretical status
        $hasPassedTheoretical = $studentModel ? $studentModel->hasPassedTheoretical() : false;
        $canEnrollPractical = $studentModel ? $studentModel->canEnrollPractical() : false;
        
        // Enrolled course info (primary active enrollment)
        $primaryEnrollment = $activeEnrollments->first();
        $enrolledCourseName = $primaryEnrollment ? ($primaryEnrollment->course->title ?? 'N/A') : 'No Active Course';
        $enrolledCourseType = $primaryEnrollment && $primaryEnrollment->course ? ucfirst($primaryEnrollment->course->course_type ?? 'N/A') : 'N/A';
        
        // Recent graded sessions for feedback visibility
        $recentGrades = Booking::where('student_id', $student->id)
            ->whereNotNull('session_grade')
            ->with(['instructor:id,name', 'course:id,title'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
        
        return view($school->resolveView('student.dashboard'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'student' => $studentModel,
            'sessionsCompleted' => $totalSessionsCompleted,
            'totalScheduledSessions' => $totalScheduledSessions,
            'hoursCompleted' => round($hoursCompleted, 1),
            'requiredHours' => $requiredHours,
            'upcomingLessons' => $upcomingLessons,
            'nextLessons' => $nextLessons,
            'progressPercentage' => $progressPercentage,
            'activeEnrollments' => $activeEnrollments,
            'enrolledCourseName' => $enrolledCourseName,
            'enrolledCourseType' => $enrolledCourseType,
            'hasPassedTheoretical' => $hasPassedTheoretical,
            'canEnrollPractical' => $canEnrollPractical,
            'recentGrades' => $recentGrades,
        ]);
    }

    public function profile(Request $request, School $school)
    {
        $student = Auth::guard('student')->user();
        $student->load('branchRelation');

        return view($school->resolveView('student.profile'), [
            'school' => $school,
            'student' => $student,
            'isAjax' => $request->ajax(),
        ]);
    }

    public function updateProfile(Request $request, School $school)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('students', 'email')
                    ->where('school_id', $school->id)
                    ->ignore($student->id),
                'regex:/@(gmail\.com|yahoo\.com)$/i',
            ],
            'contact' => ['nullable', 'string', 'max:20', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'current_password' => 'nullable|string|min:6',
            'new_password' => ['nullable', 'confirmed', new StrongPassword()],
        ]);

        $data = $request->only(['name', 'email', 'contact', 'address', 'date_of_birth']);

        // Check current password if user wants to change password
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $student->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $data['password'] = Hash::make($request->new_password);
        }
        //False positive
        $student->update($data);

        return redirect()
            ->route('schools.student.profile', $school)
            ->with('success', 'Profile updated successfully!');
    }

    public function updateProfilePicture(Request $request, School $school)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'profile_picture' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048|dimensions:max_width=2000,max_height=2000',
        ]);

        // Delete old profile picture if exists
        if ($student->profile_picture) {
            Storage::disk('public')->delete($student->profile_picture);
        }

        // Store new profile picture
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        $student->update([
            'profile_picture' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully!',
            'path' => $path,
        ]);
    }

    /**
     * Handle student driver's license upload
     */
    public function uploadLicense(Request $request, School $school)
    {
        /** @var \App\Models\Student|null $student */
        $student = Auth::guard('student')->user();

        if (!$student || !$student->isStudent() || (int) $student->school_id !== (int) $school->id) {
            abort(403, 'Only active students can upload a license.');
        }

        $request->validate([
            'student_license' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'student_license.required' => 'Please select a file to upload.',
            'student_license.mimes' => 'File must be PDF, JPG, or PNG format.',
            'student_license.max' => 'File size must not exceed 5MB.',
        ]);

        try {
            // Delete old file if re-uploading
            if ($student->student_license_path) {
                Storage::disk('local')->delete($student->student_license_path);
            }

            $uploadedFile = $request->file('student_license');
            $path = $uploadedFile->store('student-licenses', 'local');

            $student->update([
                'student_license_path' => $path,
                'student_license_data' => null,
                'student_license_mime_type' => $uploadedFile->getMimeType(),
                'student_license_filename' => $uploadedFile->getClientOriginalName(),
                'student_license_status' => 'pending',
                'student_license_verified_at' => null,
                'student_license_verified_by' => null,
                'student_license_rejection_reason' => null,
            ]);

            Log::info('Student license uploaded to disk', [
                'student_id' => $student->id,
                'school_id' => $school->id,
                'path' => $path,
            ]);

            // Notify admins about pending license verification
            $admins = Admin::where('school_id', $school->id)->where('is_active', true)->get();
            foreach ($admins as $admin) {
                Notification::send(
                    $admin,
                    'license_uploaded',
                    'License Pending Review',
                    "{$student->name} has uploaded a student driver's license for verification.",
                    'license',
                    "/{$school->slug}/admin/enrollments"
                );
            }

            return redirect()->back()->with('success', 'License uploaded successfully! It will be reviewed by an admin.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to upload student license', [
                'error' => $e->getMessage(),
                'student_id' => $student->id,
            ]);
            return redirect()->back()->with('error', 'Failed to upload license. Please try again.');
        }
    }

    public function schedule(Request $request, School $school)
    {
        $student = Auth::guard('student')->user();
        
        // Get enrolled course IDs (approved enrollment requests)
        $enrolledCourseIds = \App\Models\EnrollmentRequest::where('learner_id', $student->id)
            ->where('school_id', $school->id)
            ->where('status', 'approved')
            ->pluck('course_id')
            ->toArray();

        // Get all bookings
        $allBookings = Booking::where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->with(['course', 'instructor', 'timeSlot'])
            ->orderBy('booking_date')
            ->get();

        // Separate bookings by status
        $confirmedBookings = $allBookings->whereIn('status', ['scheduled', 'confirmed', 'completed']);
        $cancelledBookings = $allBookings->where('status', 'cancelled');
        $queuedBookings = $allBookings->where('status', 'pending');
        
        // Group bookings by date
        $groupedBookings = $confirmedBookings->groupBy(function($booking) {
            return Carbon::parse($booking->booking_date)->format('Y-m-d');
        });
        
        $groupedCancelledBookings = $cancelledBookings->groupBy(function($booking) {
            return Carbon::parse($booking->booking_date)->format('Y-m-d');
        });

        $groupedQueuedBookings = $queuedBookings->groupBy(function($booking) {
            return Carbon::parse($booking->booking_date)->format('Y-m-d');
        });
        
        // Get enrollment requests
        $enrollmentRequests = \App\Models\EnrollmentRequest::where('learner_id', $student->id)
            ->where('school_id', $school->id)
            ->with(['course'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get available time slots (Only those with instructors assigned)
        $availableTimeSlots = \App\Models\TimeSlot::visibleToStudents()
            ->where('school_id', $school->id)
            ->with(['instructors', 'course', 'branch'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
        
        // Group available schedules by date
        $groupedAvailableSchedules = $availableTimeSlots->groupBy(function($timeSlot) {
            return Carbon::parse($timeSlot->date)->format('Y-m-d');
        });
        
        $todayDate = now()->toDateString();

        // Get booking queue settings
        $settings = $school->schoolSetting;
        $queueEnabled = $settings?->enable_booking_queue ?? true;
        $queueDays = $settings?->booking_queue_days ?? 3;

        // Pre-compute upcoming bookings for sidebar (avoids repeating logic in blade)
        $upcomingBookings = $confirmedBookings
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('booking_date', '>=', now()->toDateString())
            ->where('booking_date', '<=', now()->addDays(7)->toDateString())
            ->sortBy('booking_date');
        
        return view($school->resolveView('student.schedule'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'enrolledCourseIds' => $enrolledCourseIds,
            'allBookings' => $allBookings,
            'confirmedBookings' => $confirmedBookings,
            'cancelledBookings' => $cancelledBookings,
            'queuedBookings' => $queuedBookings,
            'groupedBookings' => $groupedBookings,
            'groupedCancelledBookings' => $groupedCancelledBookings,
            'groupedQueuedBookings' => $groupedQueuedBookings,
            'enrollmentRequests' => $enrollmentRequests,
            'availableTimeSlots' => $availableTimeSlots,
            'groupedAvailableSchedules' => $groupedAvailableSchedules,
            'todayDate' => $todayDate,
            'queueEnabled' => $queueEnabled,
            'queueDays' => $queueDays,
            'upcomingBookings' => $upcomingBookings,
        ]);
    }

    /**
     * Display the student's current active course (My Course page)
     */
    public function myCourse(Request $request, School $school)
    {
        $student = Auth::guard('student')->user();
        
        // Get active enrollment (student is locked to one course at a time)
        // Rule: Show if approved (active) OR completed within the last 24 hours.
        $activeEnrollment = EnrollmentRequest::where('learner_id', $student->id)
            ->where('school_id', $school->id)
            ->where(function ($query) {
                $query->where('status', 'approved')
                      ->orWhere(function ($q) {
                          $q->where('status', 'completed')
                            ->where('completed_at', '>=', Carbon::now()->subDay());
                      });
            })
            ->with(['course.modules.lessons', 'sessionCompletions', 'bookings.timeSlot', 'bookings.instructor'])
            ->first();
        
        $approvedRequest = $activeEnrollment;
        
        // Get pending enrollment requests
        $pendingRequests = EnrollmentRequest::where('learner_id', $student->id)
            ->where('school_id', $school->id)
            ->where('status', 'pending')
            ->with('course')
            ->get();
        
        // Get available courses for enrollment
        $availableCourses = Course::where('school_id', $school->id)
            ->where('status', 'active')
            ->get();
        
        // Calculate progress
        $hoursCompleted = 0;
        $hoursRequired = 0;
        $progressPercentage = 0;
        $course = null;
        $modules = collect();
        $sessionCompletions = collect();
        
        if ($activeEnrollment) {
            $course = $activeEnrollment->course;
            $completions = $activeEnrollment->sessionCompletions ?? collect();
            $hoursCompleted = $completions->where('status', 'completed')->sum('hours_completed');
            $hoursRequired = $course ? ($course->hours_required ?? $course->duration_hours ?? 0) : 0;
            $progressPercentage = $hoursRequired > 0 ? min(100, round(($hoursCompleted / $hoursRequired) * 100)) : 0;
            $modules = $course ? ($course->modules ?? collect()) : collect();
            $sessionCompletions = $completions;
        } elseif ($approvedRequest) {
            $course = $approvedRequest->course;
            $completions = $approvedRequest->sessionCompletions ?? collect();
            $hoursCompleted = $completions->where('status', 'completed')->sum('hours_completed');
            $hoursRequired = $course ? ($course->hours_required ?? $course->duration_hours ?? 0) : 0;
            $progressPercentage = $hoursRequired > 0 ? min(100, round(($hoursCompleted / $hoursRequired) * 100)) : 0;
            $modules = $course ? ($course->modules ?? collect()) : collect();
            $sessionCompletions = $completions;
        }
        
        return view($school->resolveView('student.my-course'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'student' => $student,
            'activeEnrollment' => $activeEnrollment,
            'approvedRequest' => $approvedRequest,
            'pendingRequests' => $pendingRequests,
            'availableCourses' => $availableCourses,
            'course' => $course,
            'modules' => $modules,
            'sessionCompletions' => $sessionCompletions,
            'hoursCompleted' => $hoursCompleted,
            'hoursRequired' => $hoursRequired,
            'progressPercentage' => $progressPercentage,
        ]);
    }

    /**
     * Display the student's enrollment progress overview (My Progress page)
     */
    public function myProgress(Request $request, School $school)
    {
        $student = Auth::guard('student')->user();

        // Get all enrollment requests for this student at this school
        $enrollmentHistory = EnrollmentRequest::where('learner_id', $student->id)
            ->where('school_id', $school->id)
            ->with(['course', 'sessionCompletions.instructor', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Current active enrollment
        $activeEnrollment = $enrollmentHistory->where('status', 'approved')->first();

        // Build progress data for active enrollment
        $progressData = null;
        if ($activeEnrollment) {
            $completions = $activeEnrollment->sessionCompletions ?? collect();
            $completedSessions = $completions->where('status', 'completed');

            $hoursCompleted = $completedSessions->sum('hours_completed');
            $hoursRequired = $activeEnrollment->course->hours_required ?? 0;

            $progressData = [
                'course' => $activeEnrollment->course,
                'hours_completed' => $hoursCompleted,
                'hours_required' => $hoursRequired,
                'progress_percentage' => $hoursRequired > 0
                    ? min(100, round(($hoursCompleted / $hoursRequired) * 100, 1))
                    : 0,
                'theoretical_passed' => $activeEnrollment->theoretical_passed,
                'theoretical_sessions' => $completedSessions->where('session_type', 'theoretical')->count(),
                'practical_sessions' => $completedSessions->where('session_type', 'practical')->count(),
                'recent_sessions' => $completedSessions->sortByDesc('session_date')->take(5),
            ];
        }

        // Get phase progressions for active enrollment
        $phaseProgressions = collect();
        if ($activeEnrollment) {
            $phaseProgressions = PhaseProgression::where('enrollment_id', $activeEnrollment->id)
                ->latest('requested_at')
                ->get();
        }

        // Completed enrollments history
        $completedEnrollments = $enrollmentHistory->where('status', 'completed');

        return view($school->resolveView('student.my-progress'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'student' => $student,
            'activeEnrollment' => $activeEnrollment,
            'progressData' => $progressData,
            'phaseProgressions' => $phaseProgressions,
            'completedEnrollments' => $completedEnrollments,
            'enrollmentHistory' => $enrollmentHistory,
        ]);
    }
}
