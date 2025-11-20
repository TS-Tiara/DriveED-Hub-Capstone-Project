<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Instructor;
use App\Models\School;
use App\Models\TimeSlot;

class AdminTimeSlotController extends Controller
{
    // Display all time slots
    public function index(School $school)
    {
        $timeSlots = TimeSlot::with('instructors')
            ->where('school_id', $school->id)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $instructors = Instructor::where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view($school->resolveView('admin.timeslots'), [
            'school' => $school,
            'timeSlots' => $timeSlots,
            'instructors' => $instructors,
        ]);
    }

    // Store new time slot
    public function store(Request $request, School $school)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_instructors' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
            'instructors' => 'nullable|array',
            'instructors.*' => 'exists:instructors,id',
        ]);

        $timeSlot = TimeSlot::create([
            'school_id' => $school->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'max_instructors' => $request->max_instructors,
            'notes' => $request->notes,
            'status' => 'open',
        ]);

        if ($request->has('instructors') && is_array($request->instructors)) {
            $instructorIds = Instructor::where('school_id', $school->id)
                ->whereIn('id', $request->instructors)
                ->pluck('id');

            foreach ($instructorIds as $instructorId) {
                $timeSlot->instructors()->attach($instructorId, [
                    'school_id' => $school->id,
                    'assignment_type' => 'admin_assigned',
                ]);
            }
        }

        return redirect()->route('schools.admin.timeslots.index', $school)
            ->with('success', 'Time slot created successfully!');
    }

    // Assign/unassign instructors to a time slot
    public function assignInstructors(Request $request, School $school, $id)
    {
        $timeSlot = TimeSlot::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'instructors' => 'nullable|array',
            'instructors.*' => 'exists:instructors,id',
        ]);

        $requestedInstructorIds = $request->instructors ?? [];

        $newInstructors = Instructor::where('school_id', $school->id)
            ->whereIn('id', $requestedInstructorIds)
            ->pluck('id')
            ->toArray();

        $currentInstructors = $timeSlot->instructors()->pluck('instructor_id')->toArray();

        if (count($newInstructors) > $timeSlot->max_instructors) {
            return redirect()->back()
                ->with('error', "Cannot assign more than {$timeSlot->max_instructors} instructors to this slot.");
        }

        $toAdd = array_diff($newInstructors, $currentInstructors);
        $toRemove = array_diff($currentInstructors, $newInstructors);

        foreach ($toAdd as $instructorId) {
            $timeSlot->instructors()->attach($instructorId, [
                'school_id' => $school->id,
                'assignment_type' => 'admin_assigned',
            ]);
        }

        foreach ($toRemove as $instructorId) {
            $pivot = $timeSlot->instructors()
                ->wherePivot('instructor_id', $instructorId)
                ->wherePivot('school_id', $school->id)
                ->first();

            if ($pivot && $pivot->pivot->assignment_type === 'admin_assigned') {
                $timeSlot->instructors()->detach($instructorId);
            }
        }

        return redirect()->route('schools.admin.timeslots.index', $school)
            ->with('success', 'Instructors assigned successfully!');
    }

    // Delete time slot
    public function destroy(School $school, $id)
    {
        $timeSlot = TimeSlot::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $timeSlot->delete();

        return redirect()->route('schools.admin.timeslots.index', $school)
            ->with('success', 'Time slot deleted successfully!');
    }

    // Toggle time slot status (open/closed)
    public function toggleStatus(School $school, $id)
    {
        $timeSlot = TimeSlot::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $timeSlot->status = $timeSlot->status === 'open' ? 'closed' : 'open';
        $timeSlot->save();

        return redirect()->route('schools.admin.timeslots.index', $school)
            ->with('success', 'Time slot status updated!');
    }
}