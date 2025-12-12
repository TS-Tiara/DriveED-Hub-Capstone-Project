<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Booking;
use App\Models\Progress;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function dashboard(School $school)
    {
        $student = Auth::guard('student')->user();
        
        // Get student model for enrollment checking
        $studentModel = Student::where('user_id', $student->id)->first();
        
        // Get active enrollments with course and session data
        $activeEnrollments = $studentModel ? Enrollment::where('student_id', $studentModel->id)
            ->with(['course', 'sessionCompletions'])
            ->where('status', 'active')
            ->get() : collect();
        
        // Calculate total hours from all enrollments
        $totalHours = $activeEnrollments->sum(function($enrollment) {
            return $enrollment->total_hours;
        });
        
        // Get all bookings for this student with optimized eager loading
        $allBookings = Booking::where('student_id', $student->id)
            ->with([
                'timeSlot:id,date,start_time,end_time',
                'instructor:id,name,email',
                'course:id,title,duration_hours'
            ])
            ->select('id', 'student_id', 'instructor_id', 'course_id', 'time_slot_id', 'status', 'scheduled_at')
            ->get();
        
        // Calculate stats
        $completedBookings = $allBookings->where('status', 'completed');
        $totalLessons = $completedBookings->count();
        
        // Calculate hours driven (use enrollment hours if available, fallback to bookings)
        $hoursDriven = $totalHours > 0 ? $totalHours : $totalLessons;
        
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
        
        // Calculate progress percentage based on enrollments
        $requiredHours = $activeEnrollments->sum(function($enrollment) {
            return $enrollment->course->hours_required ?? 0;
        });
        $requiredHours = $requiredHours > 0 ? $requiredHours : 40; // Fallback to 40
        $progressPercentage = $requiredHours > 0 ? min(100, round(($hoursDriven / $requiredHours) * 100)) : 0;
        
        // Test readiness (based on completion and hours)
        $testReadiness = min(100, round($progressPercentage * 0.8 + ($totalLessons >= 10 ? 20 : 0)));
        
        // Theoretical status
        $hasPassedTheoretical = $studentModel ? $studentModel->hasPassedTheoretical() : false;
        $canEnrollPractical = $studentModel ? $studentModel->canEnrollPractical() : false;
        
        return view($school->resolveView('student.dashboard'), [
            'school' => $school,
            'totalLessons' => $totalLessons,
            'hoursDriven' => $hoursDriven,
            'upcomingLessons' => $upcomingLessons,
            'nextLessons' => $nextLessons,
            'progressPercentage' => $progressPercentage,
            'requiredHours' => $requiredHours,
            'testReadiness' => $testReadiness,
            'activeEnrollments' => $activeEnrollments,
            'hasPassedTheoretical' => $hasPassedTheoretical,
            'canEnrollPractical' => $canEnrollPractical,
        ]);
    }

    public function profile(School $school)
    {
        return view($school->resolveView('student.profile'), [
            'school' => $school,
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
            'current_password' => 'nullable|string|min:6',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'email', 'contact', 'address']);

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
            'profile_picture' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        // Delete old profile picture if exists
        if ($student->profile_picture) {
            \Storage::disk('public')->delete($student->profile_picture);
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

    public function schedule(School $school)
    {
        $student = Auth::guard('student')->user();
        
        // Get confirmed/scheduled bookings (My Schedule)
        $confirmedBookings = Booking::where('student_id', $student->id)
            ->whereIn('status', ['scheduled', 'confirmed', 'completed'])
            ->with(['course:id,title,vehicle_type', 'instructor:id,name', 'timeSlot:id,date,start_time,end_time'])
            ->orderBy('booking_date')
            ->get();
        
        // Get pending bookings (Booking Queue)
        $queuedBookings = Booking::where('student_id', $student->id)
            ->where('status', 'pending')
            ->with(['course:id,title,vehicle_type', 'instructor:id,name', 'timeSlot:id,date,start_time,end_time'])
            ->orderBy('booking_date')
            ->get();
        
        // Group confirmed bookings by date
        $groupedBookings = $confirmedBookings->groupBy(function($booking) {
            return Carbon::parse($booking->booking_date)->format('Y-m-d');
        });
        
        // Group queued bookings by date
        $groupedQueuedBookings = $queuedBookings->groupBy(function($booking) {
            return Carbon::parse($booking->booking_date)->format('Y-m-d');
        });
        
        // Get enrollment requests
        $enrollmentRequests = \App\Models\EnrollmentRequest::where('learner_id', $student->id)
            ->with(['course:id,title'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get available time slots
        $availableTimeSlots = \App\Models\TimeSlot::where('school_id', $school->id)
            ->where('date', '>=', Carbon::now()->toDateString())
            ->where('status', 'open')
            ->with(['instructors:id,name', 'course:id,title,vehicle_type'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
        
        // Group available schedules by date
        $groupedAvailableSchedules = $availableTimeSlots->groupBy(function($timeSlot) {
            return Carbon::parse($timeSlot->date)->format('Y-m-d');
        });
        
        // Get booking queue settings
        $settings = $school->schoolSetting;
        $queueEnabled = $settings?->enable_booking_queue ?? true;
        $queueDays = $settings?->booking_queue_days ?? 3;
        
        return view($school->resolveView('student.schedule'), [
            'school' => $school,
            'allBookings' => $confirmedBookings->merge($queuedBookings),
            'confirmedBookings' => $confirmedBookings,
            'queuedBookings' => $queuedBookings,
            'groupedBookings' => $groupedBookings,
            'groupedQueuedBookings' => $groupedQueuedBookings,
            'enrollmentRequests' => $enrollmentRequests,
            'availableTimeSlots' => $availableTimeSlots,
            'groupedAvailableSchedules' => $groupedAvailableSchedules,
            'queueEnabled' => $queueEnabled,
            'queueDays' => $queueDays,
        ]);
    }
}
