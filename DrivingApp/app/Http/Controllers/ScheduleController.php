<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Instructor;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    // Step 1: Show form to pick date & time
    public function create()
    {
        return view('student.schedule_create');
    }

    // Step 2: Check availability (students pick instructor)
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time_start' => 'required|date_format:H:i',
        ]);

        $date = $request->date;
        $timeStart = $request->time_start;
        $timeEnd = date("H:i", strtotime($timeStart . ' +2 hours'));

        $availableInstructors = Instructor::whereDoesntHave('schedules', function ($query) use ($date, $timeStart, $timeEnd) {
            $query->where('date', $date)
                  ->where(function ($q) use ($timeStart, $timeEnd) {
                      $q->whereBetween('time_start', [$timeStart, $timeEnd])
                        ->orWhereBetween('time_end', [$timeStart, $timeEnd])
                        ->orWhere(function ($q2) use ($timeStart, $timeEnd) {
                            $q2->where('time_start', '<', $timeStart)
                               ->where('time_end', '>', $timeEnd);
                        });
                  });
        })->get();

        return view('student.select_instructor', compact('availableInstructors', 'date', 'timeStart', 'timeEnd'));
    }

    // Step 3: Final booking
    public function bookSchedule(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'instructor_id' => 'required|exists:instructors,id',
            'date' => 'required|date',
            'time_start' => 'required|date_format:H:i',
        ]);

        $timeEnd = date("H:i", strtotime($request->time_start . ' +2 hours'));

        Schedule::create([
            'student_id' => $request->student_id,
            'instructor_id' => $request->instructor_id,
            'date' => $request->date,
            'time_start' => $request->time_start,
            'time_end' => $timeEnd,
        ]);

        return redirect()->route('student.schedules')->with('success', 'Your 2-hour session has been booked!');
    }
}
