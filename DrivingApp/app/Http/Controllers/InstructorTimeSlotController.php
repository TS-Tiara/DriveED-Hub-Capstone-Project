<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\TimeSlot;
use App\Models\InstructorRemovalRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Monolog\Handler\ElasticaHandler;
use PhpParser\Node\Stmt\Else_;

class InstructorTimeSlotController extends Controller
{
    // Display available time slots and instructor's selected slots
    public function index(School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        abort_unless($instructor && $instructor->school_id === $school->id, 403);

        $availableSlots = TimeSlot::with('instructors')
            ->where('school_id', $school->id)
            ->where('status', 'open')
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $mySlots = TimeSlot::with('instructors')
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

        if ($timeSlot->status !== 'open') {
            return redirect()->back()
                ->with('error', 'This time slot is closed and cannot be selected.');
        }

        if ($timeSlot->date->isPast()) {
            return redirect()->back()
                ->with('error', 'Cannot select past time slots.');
        }

        if ($timeSlot->hasInstructor($instructor->id)) {
            $pivot = $timeSlot->instructors()
                ->wherePivot('instructor_id', $instructor->id)
                ->wherePivot('school_id', $school->id)
                ->first();

            if ($pivot && $pivot->pivot->assignment_type === 'admin_assigned') {
                return redirect()->back()
                    ->with('error', 'You cannot leave this slot as it was assigned by an admin.');
            }

            $timeSlot->instructors()->detach($instructor->id);

            return redirect()->back()
                ->with('success', 'You have left this time slot.');
        }

        if ($timeSlot->isFull()) {
            return redirect()->back()
                ->with('error', 'This time slot is full.');
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
            return redirect()->back()
                ->with('error', 'You already have a time slot that conflicts with this one.');
        }

        $timeSlot->instructors()->attach($instructor->id, [
            'school_id' => $school->id,
            'assignment_type' => 'self_selected',
        ]);

        return redirect()->back()
            ->with('success', 'You have successfully selected this time slot!');
    }

    // View instructor's schedule/calendar
    public function mySchedule(School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        abort_unless($instructor && $instructor->school_id === $school->id, 403);

        $schedule = TimeSlot::with(['instructors' => function ($query) use ($instructor): void {
                $query->where('instructor_id', $instructor->id);
            }])
            ->where('school_id', $school->id)
            ->whereHas('instructors', function ($query) use ($instructor): void {
                $query->where('instructor_id', $instructor->id);
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Get pending removal requests for this instructor
        $pendingRequests = InstructorRemovalRequest::where('instructor_id', $instructor->id)
            ->where('school_id', $school->id)
            ->where('status', 'pending')
            ->pluck('time_slot_id')
            ->toArray();

        return view($school->resolveView('instructor.schedule'), [
            'school' => $school,
            'schedule' => $schedule,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    public function profile(School $school)
    {
        return view($school->resolveView('instructor.profile'), [
            'school' => $school,
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
            'new_password' => 'nullable|string|min:6|confirmed',
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
}