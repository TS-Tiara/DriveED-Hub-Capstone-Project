<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Course;
use App\Models\Progress;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    /**
     * Display a listing of progress records.
     */
    public function index(School $school)
    {
        $query = Progress::where('school_id', $school->id)
            ->with(['student', 'course']);

        // Filter by role
        if (Auth::guard('student')->check()) {
            $query->where('student_id', Auth::guard('student')->id());
        } elseif (Auth::guard('instructor')->check()) {
            // Instructors see progress for students they have bookings with
            $instructorId = Auth::guard('instructor')->id();
            $studentIds = \App\Models\Booking::where('school_id', $school->id)
                ->where('instructor_id', $instructorId)
                ->distinct()
                ->pluck('student_id');
            $query->whereIn('student_id', $studentIds);
        }

        if (request('student_id')) {
            $query->where('student_id', request('student_id'));
        }

        if (request('course_id')) {
            $query->where('course_id', request('course_id'));
        }

        $progresses = $query->latest('last_updated')->get();

        // Pre-load booking data for each progress record (avoids N+1 in views)
        $allStudentIds = $progresses->pluck('student_id')->unique()->toArray();
        $allCourseIds = $progresses->pluck('course_id')->unique()->toArray();

        $allBookings = Booking::where('school_id', $school->id)
            ->whereIn('student_id', $allStudentIds)
            ->whereIn('course_id', $allCourseIds)
            ->with(['instructor', 'course'])
            ->orderBy('scheduled_at', 'desc')
            ->get();

        // Attach computed booking data to each progress record
        foreach ($progresses as $progress) {
            $bookings = $allBookings->where('student_id', $progress->student_id)
                ->where('course_id', $progress->course_id);

            $progress->completedSessions = $bookings->where('status', 'confirmed')->count();
            $progress->totalSessions = ceil($progress->course->duration_hours ?? 10);
            $progress->currentBooking = $bookings->where('status', 'confirmed')->sortByDesc('scheduled_at')->first() ?? $bookings->first();
            $progress->nextBooking = $bookings->where('status', 'pending')->filter(fn($b) => $b->scheduled_at > now())->sortBy('scheduled_at')->first();
            $progress->bookingsList = $bookings;
        }

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'progresses' => $progresses
            ]);
        }

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = "{$guard}.progress";
        return view($school->resolveView($view), compact('school', 'progresses'));
    }

    /**
     * Show the form for creating/updating progress.
     */
    public function create(School $school, Request $request)
    {
        $studentId = $request->query('student_id');
        $courseId = $request->query('course_id');

        $students = Student::where('school_id', $school->id)->where('status', 'active')->get();
        $courses = Course::where('school_id', $school->id)->where('status', 'active')->get();

        $guard = Auth::guard('admin')->check() ? 'admin' : 'instructor';
        $view = "{$guard}.progress-create";
        return view($school->resolveView($view), compact('school', 'students', 'courses', 'studentId', 'courseId'));
    }

    /**
     * Store or update progress.
     */
    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'completion_percent' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['school_id'] = $school->id;
        $validated['last_updated'] = now();

        // Update existing progress or create new
        $progress = Progress::updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $validated['student_id'],
                'course_id' => $validated['course_id'],
            ],
            $validated
        );

        $progress->load(['student', 'course']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'progress' => $progress
            ], 201);
        }

        return redirect()->route('progress.show', [$school->slug, $progress->id])
            ->with('success', 'Progress updated successfully');
    }

    /**
     * Display the specified progress.
     */
    public function show(School $school, Progress $progress)
    {
        abort_if($progress->school_id !== $school->id, 404);

        $progress->load(['student', 'course']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'progress' => $progress
            ]);
        }

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = "{$guard}.progress-show";
        return view($school->resolveView($view), compact('school', 'progress'));
    }

    /**
     * Show the form for editing the specified progress.
     */
    public function edit(School $school, Progress $progress)
    {
        abort_if($progress->school_id !== $school->id, 404);

        $students = Student::where('school_id', $school->id)->where('status', 'active')->get();
        $courses = Course::where('school_id', $school->id)->where('status', 'active')->get();

        $guard = Auth::guard('admin')->check() ? 'admin' : 'instructor';
        $view = "{$guard}.progress-edit";
        return view($school->resolveView($view), compact('school', 'progress', 'students', 'courses'));
    }

    /**
     * Update the specified progress.
     */
    public function update(Request $request, School $school, Progress $progress)
    {
        abort_if($progress->school_id !== $school->id, 404);

        $validated = $request->validate([
            'completion_percent' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['last_updated'] = now();
        $progress->update($validated);
        $progress->load(['student', 'course']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'progress' => $progress
            ]);
        }

        return redirect()->route('progress.show', [$school->slug, $progress->id])
            ->with('success', 'Progress updated successfully');
    }

    /**
     * Remove the specified progress.
     */
    public function destroy(Request $request, School $school, Progress $progress)
    {
        abort_if($progress->school_id !== $school->id, 404);

        $progress->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Progress deleted successfully'
            ]);
        }

        return redirect()->route('progress.index', $school->slug)
            ->with('success', 'Progress deleted successfully');
    }

    /**
     * Get student progress summary.
     */
    public function studentSummary(School $school, Student $student)
    {
        $progresses = Progress::where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->with('course')
            ->get();

        $summary = [
            'total_courses' => $progresses->count(),
            'completed_courses' => $progresses->where('completion_percent', '>=', 100)->count(),
            'in_progress_courses' => $progresses->where('completion_percent', '>', 0)
                ->where('completion_percent', '<', 100)->count(),
            'average_completion' => $progresses->avg('completion_percent'),
            'progresses' => $progresses,
        ];

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);
        }

        return view($school->resolveView('student.progress-summary'), compact('school', 'student', 'summary'));
    }
}
