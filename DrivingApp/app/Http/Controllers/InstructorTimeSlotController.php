<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;

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

        $schedule = TimeSlot::with('instructors')
            ->where('school_id', $school->id)
            ->whereHas('instructors', function ($query) use ($instructor): void {
                $query->where('instructor_id', $instructor->id);
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view($school->resolveView('instructor.schedule'), [
            'school' => $school,
            'schedule' => $schedule,
        ]);
    }
}