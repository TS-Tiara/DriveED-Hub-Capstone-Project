<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Instructor;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // ==========================
    // DASHBOARD
    // ==========================
    public function dashboard(School $school)
    {
        return view($school->resolveView('admin.dashboard'), [
            'school' => $school,
        ]);
    }

    // ==========================
    // CREATE ACCOUNT
    // ==========================
    public function createAccount(School $school)
    {
        return view($school->resolveView('admin.create-account'), [
            'school' => $school,
        ]);
    }

    public function storeAccount(Request $request, School $school)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('students', 'email')->where('school_id', $school->id),
                Rule::unique('instructors', 'email')->where('school_id', $school->id),
                'regex:/@(gmail\.com|yahoo\.com)$/i',
            ],
            'password' => 'required|string|min:6',
            'contact' => ['nullable', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'role' => 'required|in:student,instructor',
        ]);

        $data = [
            'school_id' => $school->id,
            'name' => trim($request->name),
            'email' => trim($request->email),
            'contact' => trim((string) $request->contact),
            'password' => Hash::make($request->password),
        ];

        if ($request->role === 'student') {
            Student::create(array_merge($data, [
                'address' => null,
                'status' => 'active',
            ]));
        } else {
            Instructor::create(array_merge($data, [
                'license_number' => null,
                'status' => 'active',
                'availability' => 'available',
            ]));
        }

        return redirect()
            ->route('schools.admin.createAccount', $school)
            ->with('success', 'Account created successfully!');
    }

    // ==========================
    // STUDENTS MANAGEMENT
    // ==========================
    public function students(School $school)
    {
        $students = Student::where('school_id', $school->id)->orderBy('name')->get();

        return view($school->resolveView('admin.students'), [
            'school' => $school,
            'students' => $students,
        ]);
    }

    public function editStudent(School $school, $id)
    {
        $student = Student::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        return view($school->resolveView('admin.edit-student'), [
            'school' => $school,
            'student' => $student,
        ]);
    }

    public function updateStudent(Request $request, School $school, $id)
    {
        $student = Student::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

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
            'password' => 'nullable|string|min:6',
        ]);

        $data = $request->only('name', 'email', 'contact', 'address');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $student->update($data);

        return redirect()->route('schools.admin.students', $school)
            ->with('success', 'Student updated successfully!');
    }

    public function toggleStudentStatus(School $school, $id)
    {
        $student = Student::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $student->status = $student->status === 'active' ? 'inactive' : 'active';
        $student->save();

        return redirect()->route('schools.admin.students', $school)
            ->with('success', 'Student status updated successfully!');
    }

    // ==========================
    // INSTRUCTORS MANAGEMENT
    // ==========================
    public function instructors(School $school)
    {
        $instructors = Instructor::where('school_id', $school->id)
            ->orderBy('name')
            ->get();

        return view($school->resolveView('admin.instructors'), [
            'school' => $school,
            'instructors' => $instructors,
        ]);
    }

    public function editInstructor(School $school, $id)
    {
        $instructor = Instructor::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        return view($school->resolveView('admin.edit-instructor'), [
            'school' => $school,
            'instructor' => $instructor,
        ]);
    }

    public function updateInstructor(Request $request, School $school, $id)
    {
        $instructor = Instructor::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

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
            'password' => 'nullable|string|min:6',
        ]);

        $data = $request->only('name', 'email', 'contact', 'license_number');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $instructor->update($data);

        return redirect()->route('schools.admin.instructors', $school)
            ->with('success', 'Instructor updated successfully!');
    }

    public function toggleInstructorStatus(School $school, $id)
    {
        $instructor = Instructor::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $instructor->status = $instructor->status === 'active' ? 'inactive' : 'active';
        $instructor->save();

        return redirect()->route('schools.admin.instructors', $school)
            ->with('success', 'Instructor status updated successfully!');
    }

    public function toggleAvailability(School $school, $id)
    {
        $instructor = Instructor::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $instructor->availability = $instructor->availability === 'available' ? 'unavailable' : 'available';
        $instructor->save();

        return redirect()->route('schools.admin.instructors', $school)
            ->with('success', 'Instructor availability updated successfully!');
    }

    // ==========================
    // SCHEDULES MANAGEMENT
    // ==========================
    public function schedules(School $school)
    {
        $schedules = Schedule::with('instructor')
            ->where('school_id', $school->id)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $manualEntries = $schedules->map(function (Schedule $schedule): array {
            return [
                'key' => 'manual-' . $schedule->id,
                'type' => 'manual',
                'id' => $schedule->id,
                'date' => Carbon::parse($schedule->date)->format('Y-m-d'),
                'start_time' => Carbon::parse($schedule->start_time)->format('H:i:s'),
                'end_time' => Carbon::parse($schedule->end_time)->format('H:i:s'),
                'status' => $schedule->status,
                'instructor_id' => $schedule->instructor_id,
                'instructor_name' => optional($schedule->instructor)->name ?? 'N/A',
                'assignment_type' => null,
                'notes' => null,
                'max_instructors' => null,
                'can_edit' => true,
                'can_delete' => true,
            ];
        });

        $timeSlotAssignments = TimeSlot::with(['instructors' => function ($query) use ($school): void {
                $query
                    ->where('instructors.school_id', $school->id)
                    ->where('schedule_instructors.school_id', $school->id);
            }])
            ->where('school_id', $school->id)
            ->whereHas('instructors', function ($query) use ($school): void {
                $query
                    ->where('instructors.school_id', $school->id)
                    ->where('schedule_instructors.school_id', $school->id);
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $timeSlotEntries = $timeSlotAssignments->flatMap(function (TimeSlot $slot) {
            return $slot->instructors->map(function (Instructor $instructor) use ($slot): array {
                return [
                    'key' => 'timeslot-' . $slot->id . '-' . $instructor->id,
                    'type' => 'timeslot',
                    'id' => $slot->id,
                    'date' => $slot->date?->format('Y-m-d') ?? Carbon::parse($slot->date)->format('Y-m-d'),
                    'start_time' => $slot->start_time?->format('H:i:s') ?? Carbon::parse($slot->start_time)->format('H:i:s'),
                    'end_time' => $slot->end_time?->format('H:i:s') ?? Carbon::parse($slot->end_time)->format('H:i:s'),
                    'status' => $slot->status,
                    'instructor_id' => $instructor->id,
                    'instructor_name' => $instructor->name,
                    'assignment_type' => $instructor->pivot->assignment_type,
                    'notes' => $slot->notes,
                    'max_instructors' => $slot->max_instructors,
                    'can_edit' => false,
                    'can_delete' => false,
                ];
            });
        });

        $scheduleEntries = $manualEntries
            ->merge($timeSlotEntries)
            ->sortBy(function (array $entry): string {
                return implode('|', [
                    $entry['date'],
                    $entry['start_time'],
                    $entry['instructor_name'],
                ]);
            })
            ->values();

        $instructors = Instructor::where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view($school->resolveView('admin.schedules'), [
            'school' => $school,
            'scheduleEntries' => $scheduleEntries,
            'instructors' => $instructors,
        ]);
    }

    public function createSchedule(School $school)
    {
        $instructors = Instructor::where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view($school->resolveView('admin.create-schedule'), [
            'school' => $school,
            'instructors' => $instructors,
        ]);
    }

    public function storeSchedule(Request $request, School $school)
    {
        $request->validate([
            'instructor_id' => 'required|exists:instructors,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $instructor = Instructor::where('school_id', $school->id)
            ->where('id', $request->instructor_id)
            ->firstOrFail();

        Schedule::create([
            'school_id' => $school->id,
            'instructor_id' => $instructor->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'available',
            'created_by' => optional(Auth::guard('admin')->user())->id,
        ]);

        return redirect()->route('schools.admin.schedules', $school)
            ->with('success', 'Schedule created successfully!');
    }

    public function editSchedule(School $school, $id)
    {
        $schedule = Schedule::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $instructors = Instructor::where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view($school->resolveView('admin.edit-schedule'), [
            'school' => $school,
            'schedule' => $schedule,
            'instructors' => $instructors,
        ]);
    }

    public function updateSchedule(Request $request, School $school, $id)
    {
        $schedule = Schedule::where('school_id', $school->id)
            ->where('id', $id)
            ->first();

        if (!$schedule) {
            return redirect()->route('schools.admin.schedules', $school)
                ->with('error', 'Schedule not found.');
        }

        $request->validate([
            'instructor_id' => 'required|exists:instructors,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:available,removed,booked',
        ]);

        $instructor = Instructor::where('school_id', $school->id)
            ->where('id', $request->instructor_id)
            ->firstOrFail();

        $schedule->fill([
            'instructor_id' => $instructor->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status,
        ])->save();

        return redirect()->route('schools.admin.schedules', $school)
            ->with('success', 'Schedule updated successfully!');
    }

    public function deleteSchedule(School $school, $id)
    {
        $schedule = Schedule::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $schedule->delete();

        return redirect()->route('schools.admin.schedules', $school)
            ->with('success', 'Schedule deleted successfully!');
    }

    // ==========================
    // REPORTS & PROFILE
    // ==========================
    public function studentReports(School $school)
    {
        return view($school->resolveView('admin.reports.students'), [
            'school' => $school,
        ]);
    }

    public function instructorReports(School $school)
    {
        return view($school->resolveView('admin.reports.instructors'), [
            'school' => $school,
        ]);
    }

    public function logs(School $school)
    {
        return view($school->resolveView('admin.reports.logs'), [
            'school' => $school,
        ]);
    }

    public function profile(School $school)
    {
        return view($school->resolveView('admin.profile'), [
            'school' => $school,
        ]);
    }
}
