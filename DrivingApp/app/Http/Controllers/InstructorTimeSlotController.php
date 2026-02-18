<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\TimeSlot;
use App\Models\Booking;
use App\Models\InstructorRemovalRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Rules\StrongPassword;

class InstructorTimeSlotController extends Controller
{
    // Display available time slots and instructor's selected slots
    public function index(School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        abort_unless($instructor && $instructor->school_id === $school->id, 403);

        // Get instructor's course specializations
        $instructorCourses = $instructor->course_specializations ?? [];

        $availableSlots = TimeSlot::with(['instructors', 'course', 'branch'])
            ->where('school_id', $school->id)
            ->where('status', 'open')
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $mySlots = TimeSlot::with(['instructors', 'course', 'branch'])
            ->where('school_id', $school->id)
            ->whereHas('instructors', function ($query) use ($instructor): void {
                $query->where('instructor_id', $instructor->id);
            })
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view($school->resolveView('instructor.timeslots'), [
            'school' => $school,
            'availableSlots' => $availableSlots,
            'mySlots' => $mySlots,
            'instructorCourses' => $instructorCourses,
        ]);
    }

    // Toggle instructor's participation in a time slot (select/leave)
    public function toggle(School $school, $id)
    {
        $instructor = Auth::guard('instructor')->user();

        abort_unless($instructor && $instructor->school_id === $school->id, 403);

        $timeSlot = TimeSlot::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $isAjax = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';

        if ($timeSlot->status !== 'open') {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'This time slot is closed and cannot be selected.'], 400);
            }
            return redirect()->back()->with('error', 'This time slot is closed and cannot be selected.');
        }

        if ($timeSlot->date->isPast()) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Cannot select past time slots.'], 400);
            }
            return redirect()->back()->with('error', 'Cannot select past time slots.');
        }

        if ($timeSlot->hasInstructor($instructor->id)) {
            $pivot = $timeSlot->instructors()
                ->wherePivot('instructor_id', $instructor->id)
                ->wherePivot('school_id', $school->id)
                ->first();

            if ($pivot && $pivot->pivot->assignment_type === 'admin_assigned') {
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => 'You cannot leave this slot as it was assigned by an admin.'], 400);
                }
                return redirect()->back()->with('error', 'You cannot leave this slot as it was assigned by an admin.');
            }

            $timeSlot->instructors()->detach($instructor->id);

            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'You have left this time slot.', 'action' => 'left']);
            }
            return redirect()->back()->with('success', 'You have left this time slot.');
        }

        if ($timeSlot->isFull()) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'This time slot is full.'], 400);
            }
            return redirect()->back()->with('error', 'This time slot is full.');
        }

        // Check if instructor is qualified for this course
        $instructorCourses = $instructor->course_specializations ?? [];
        $isQualified = empty($instructorCourses) || in_array($timeSlot->course_id, $instructorCourses);

        if (!$isQualified) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'You are not qualified for this course. Please contact your admin for course assignment approval.'], 400);
            }
            return redirect()->back()->with('error', 'You are not qualified for this course. Please contact your admin for course assignment approval.');
        }

        $hasConflict = TimeSlot::where('school_id', $school->id)
            ->where('id', '!=', $timeSlot->id)
            ->where('date', $timeSlot->date)
            ->whereHas('instructors', function ($query) use ($instructor): void {
                $query->where('instructor_id', $instructor->id);
            })
            ->where(function ($query) use ($timeSlot): void {
                $query->where(function ($q) use ($timeSlot): void {
                    $q->where('start_time', '<=', $timeSlot->start_time)
                        ->where('end_time', '>', $timeSlot->start_time);
                })
                ->orWhere(function ($q) use ($timeSlot): void {
                    $q->where('start_time', '<', $timeSlot->end_time)
                        ->where('end_time', '>=', $timeSlot->end_time);
                })
                ->orWhere(function ($q) use ($timeSlot): void {
                    $q->where('start_time', '>=', $timeSlot->start_time)
                        ->where('end_time', '<=', $timeSlot->end_time);
                });
            })
            ->exists();

        if ($hasConflict) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'You already have a time slot that conflicts with this one.'], 400);
            }
            return redirect()->back()->with('error', 'You already have a time slot that conflicts with this one.');
        }

        $timeSlot->instructors()->attach($instructor->id, [
            'school_id' => $school->id,
            'assignment_type' => 'self_selected',
        ]);

        if ($isAjax) {
            return response()->json(['success' => true, 'message' => 'You have successfully selected this time slot!', 'action' => 'selected']);
        }
        return redirect()->back()->with('success', 'You have successfully selected this time slot!');
    }

    // View instructor's schedule/calendar
    public function mySchedule(School $school)
    {
        $instructor = Auth::guard('instructor')->user();
        abort_unless($instructor && $instructor->school_id === $school->id, 403);

        $instructorId = $instructor->id;
        $todayDate = now()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();
        $minimumNoticeDays = $school->instructor_removal_notice_days ?? 7;
        
        // Get instructor's qualified courses
        $qualifiedCourseIds = $instructor->course_specializations ?? [];
        
        // Get pending removal requests
        $pendingRemovalRequests = InstructorRemovalRequest::where('instructor_id', $instructorId)
            ->where('school_id', $school->id)
            ->where('status', 'pending')
            ->pluck('time_slot_id')
            ->toArray();
        
        // My slots (instructor's selected and admin-assigned slots)
        $mySlots = TimeSlot::with(['instructors', 'course', 'branch', 'bookings.student', 'bookings.course'])
            ->where('school_id', $school->id)
            ->whereHas('instructors', function ($query) use ($instructorId) {
                $query->where('instructor_id', $instructorId);
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
        
        // Group my slots by date
        $groupedMySlots = $mySlots->groupBy(function ($slot) {
            return $slot->date->format('Y-m-d');
        });
        
        // Today's slots
        $todaySlots = $mySlots->filter(function ($slot) use ($todayDate) {
            return $slot->date->format('Y-m-d') === $todayDate;
        });
        
        // Upcoming slots this week (excluding today)
        $upcomingSlots = $mySlots->filter(function ($slot) use ($todayDate, $endOfWeek) {
            $slotDate = $slot->date->format('Y-m-d');
            return $slotDate > $todayDate && $slotDate <= $endOfWeek;
        })->take(5);
        
        // Available slots (not taken by this instructor)
        $availableSlots = TimeSlot::with(['instructors', 'course', 'branch'])
            ->where('school_id', $school->id)
            ->where('status', 'open')
            ->whereDoesntHave('instructors', function ($query) use ($instructorId) {
                $query->where('instructor_id', $instructorId);
            })
            ->whereRaw('(SELECT COUNT(*) FROM schedule_instructors WHERE schedule_instructors.time_slot_id = time_slots.id) < COALESCE(time_slots.max_instructors, 1)')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
        
        // Group available slots by date
        $groupedAvailableSlots = $availableSlots->groupBy(function ($slot) {
            return $slot->date->format('Y-m-d');
        });
        
        // Instructor's schedule for conflict checking (slot IDs by date and time)
        $instructorSchedule = [];
        foreach ($mySlots as $slot) {
            $dateKey = $slot->date->format('Y-m-d');
            if (!isset($instructorSchedule[$dateKey])) {
                $instructorSchedule[$dateKey] = [];
            }
            $instructorSchedule[$dateKey][] = [
                'id' => $slot->id,
                'start' => $slot->start_time->format('H:i'),
                'end' => $slot->end_time->format('H:i'),
            ];
        }

        return view('school.instructor.schedule-new', [
            'school' => $school,
            'instructorId' => $instructorId,
            'todayDate' => $todayDate,
            'minimumNoticeDays' => $minimumNoticeDays,
            'qualifiedCourseIds' => $qualifiedCourseIds,
            'pendingRemovalRequests' => $pendingRemovalRequests,
            'mySlots' => $mySlots,
            'groupedMySlots' => $groupedMySlots,
            'todaySlots' => $todaySlots,
            'upcomingSlots' => $upcomingSlots,
            'availableSlots' => $availableSlots,
            'groupedAvailableSlots' => $groupedAvailableSlots,
            'instructorSchedule' => $instructorSchedule,
        ]);
    }

    public function profile(School $school)
    {
        $instructor = Auth::guard('instructor')->user();
        $instructor->load('branch');

        return view($school->resolveView('instructor.profile'), [
            'school' => $school,
            'instructor' => $instructor,
        ]);
    }

    public function updateProfile(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('instructors', 'email')
                    ->where('school_id', $school->id)
                    ->ignore($instructor->id),
                'regex:/@(gmail\.com|yahoo\.com)$/i',
            ],
            'contact' => ['nullable', 'string', 'max:20', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'license_number' => 'nullable|string|max:50',
            'current_password' => 'nullable|string|min:6',
            'new_password' => ['nullable', 'confirmed', new StrongPassword()],
        ]);

        $data = $request->only(['name', 'email', 'contact', 'license_number']);

        // Check current password if user wants to change password
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $instructor->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $data['password'] = Hash::make($request->new_password);
        }
        //False positive
        $instructor->update($data);

        return redirect()
            ->route('schools.instructor.profile', $school)
            ->with('success', 'Profile updated successfully!');
    }

    public function updateProfilePicture(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        $request->validate([
            'profile_picture' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        // Delete old profile picture if exists
        if ($instructor->profile_picture) {
            Storage::disk('public')->delete($instructor->profile_picture);
        }

        // Store new profile picture
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        $instructor->update([
            'profile_picture' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully!',
            'path' => $path,
        ]);
    }

    // Request removal from an admin-assigned time slot
    public function requestRemoval(Request $request, School $school, $id)
    {
        $instructor = Auth::guard('instructor')->user();

        abort_unless($instructor && $instructor->school_id === $school->id, 403);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $timeSlot = TimeSlot::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        // Check minimum notice period
        $minimumNoticeDays = $school->instructor_removal_notice_days ?? 7;
        $daysUntilSlot = now()->startOfDay()->diffInDays($timeSlot->date->startOfDay(), false);
        
        if ($daysUntilSlot < $minimumNoticeDays) {
            return redirect()->back()
                ->with('error', "You must request removal at least {$minimumNoticeDays} days before the scheduled time slot. This slot is in {$daysUntilSlot} day(s).");
        }

        // Check if instructor is assigned to this slot
        $pivot = DB::table('schedule_instructors')
            ->where('time_slot_id', $timeSlot->id)
            ->where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->first();

        if (!$pivot) {
            return redirect()->back()
                ->with('error', 'You are not assigned to this time slot.');
        }

        // Only allow removal requests for admin-assigned slots
        if ($pivot->assignment_type !== 'admin_assigned') {
            return redirect()->back()
                ->with('error', 'You can only request removal from admin-assigned slots. Self-selected slots can be left directly.');
        }

        // Check if there's already a pending request
        if ($pivot->has_pending_removal_request) {
            return redirect()->back()
                ->with('error', 'You already have a pending removal request for this time slot.');
        }

        // Create the removal request
        InstructorRemovalRequest::create([
            'school_id' => $school->id,
            'time_slot_id' => $timeSlot->id,
            'instructor_id' => $instructor->id,
            'schedule_instructor_id' => $pivot->id,
            'status' => 'pending',
            'reason' => $request->reason,
        ]);

        // Update the pivot to mark it has a pending request
        DB::table('schedule_instructors')
            ->where('id', $pivot->id)
            ->update(['has_pending_removal_request' => true]);

        return redirect()->back()
            ->with('success', 'Your removal request has been submitted to the admin for review.');
    }

    public function updateAttendance(School $school, Booking $booking, Request $request)
    {
        $instructor = Auth::guard('instructor')->user();
        abort_unless($instructor && $instructor->school_id === $school->id, 403);
        abort_unless($booking->school_id === $school->id, 403);

        $request->validate([
            'attendance_status' => 'nullable|in:attended,late,absent'
        ]);

        $booking->update([
            'attendance_status' => $request->attendance_status,
            'attendance_marked_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully'
        ]);
    }

    public function updateFeedback(School $school, Booking $booking, Request $request)
    {
        $instructor = Auth::guard('instructor')->user();
        abort_unless($instructor && $instructor->school_id === $school->id, 403);
        abort_unless($booking->school_id === $school->id, 403);

        $request->validate([
            'instructor_feedback' => 'nullable|string|max:1000'
        ]);

        $booking->update([
            'instructor_feedback' => $request->instructor_feedback
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback updated successfully'
        ]);
    }

    public function getLessonDetails(School $school, Booking $booking)
    {
        $instructor = Auth::guard('instructor')->user();
        abort_unless($instructor && $instructor->school_id === $school->id, 403);
        abort_unless($booking->school_id === $school->id, 403);
        abort_unless($booking->instructor_id === $instructor->id, 403, 'This lesson is not assigned to you');

        $booking->load(['student', 'course', 'timeSlot']);

        return response()->json([
            'success' => true,
            'booking' => $booking
        ]);
    }

    public function updateLessonDetails(School $school, Booking $booking, Request $request)
    {
        $instructor = Auth::guard('instructor')->user();
        abort_unless($instructor && $instructor->school_id === $school->id, 403);
        abort_unless($booking->school_id === $school->id, 403);
        abort_unless($booking->instructor_id === $instructor->id, 403, 'This lesson is not assigned to you');

        $validated = $request->validate([
            'attendance_status' => 'required|in:attended,late,absent',
            'session_status' => 'required|in:completed,cancelled,rescheduled,no-show',
            'session_grade' => 'nullable|numeric|min:0|max:100',
            'instructor_feedback' => 'nullable|string|max:1000',
            'student_feedback' => 'nullable|string|max:1000',
            'skills_practiced' => 'nullable|array',
            'cancellation_reason' => 'nullable|string|max:500'
        ]);

        $updateData = [
            'attendance_status' => $validated['attendance_status'],
            'session_status' => $validated['session_status'],
            'session_grade' => $validated['session_grade'],
            'instructor_feedback' => $validated['instructor_feedback'],
            'student_feedback' => $validated['student_feedback'],
            'skills_practiced' => $validated['skills_practiced'] ?? [],
            'attendance_marked_at' => now()
        ];

        // If session is being marked as cancelled, update the main booking status too
        if ($validated['session_status'] === 'cancelled' && $booking->status !== 'cancelled') {
            $updateData['status'] = 'cancelled';
            $updateData['cancelled_by'] = 'instructor';
            $updateData['cancelled_at'] = now();
            $updateData['cancellation_reason'] = $validated['cancellation_reason'] ?? 'Session cancelled by instructor';
        }

        $booking->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Lesson details updated successfully'
        ]);
    }
}


