<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     */
    public function index(School $school)
    {
        $courses = Course::where('school_id', $school->id)
            ->with('packages')
            ->when(request('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('sort_order')
            ->orderBy('is_featured', 'desc')
            ->latest()
            ->get();

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'courses' => $courses
            ]);
        }

        // Determine which view to use based on user role
        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = $guard === 'admin' ? 'admin.courses' : ($guard === 'student' ? 'student.courses' : 'instructor.courses');

        // Get instructors for admin view
        $instructors = [];
        if ($guard === 'admin') {
            $instructors = \App\Models\Instructor::where('school_id', $school->id)->get();
        }

        // Check if this is an AJAX request
        $isAjax = request()->ajax();

        return view($school->resolveView($view), compact('school', 'courses', 'instructors', 'isAjax'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create(School $school)
    {
        return view($school->resolveView('admin.course-create'), compact('school'));
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|numeric|min:0',
            'max_students' => 'nullable|integer|min:1',
            'type' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive,archived',
            'schedules' => 'nullable|array',
            'schedules.*.date' => 'required_with:schedules|date',
            'schedules.*.start_time' => 'required_with:schedules',
            'schedules.*.end_time' => 'required_with:schedules',
            'schedules.*.max_students' => 'nullable|integer|min:1',
            'schedules.*.instructor_id' => 'nullable|exists:instructors,id',
            'schedules.*.notes' => 'nullable|string',
        ]);

        $validated['school_id'] = $school->id;
        $course = Course::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Course created successfully',
                'course' => $course
            ], 201);
        }

        return redirect()->route('courses.index', $school->slug)
            ->with('success', 'Course created successfully');
    }

    /**
     * Display the specified course.
     */
    public function show(School $school, Course $course)
    {
        // Authorization check can be added here if needed
        
        $course->load(['bookings.student', 'bookings.instructor']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'course' => $course
            ]);
        }

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = $guard === 'admin' ? 'admin.course-show' : ($guard === 'student' ? 'student.course-show' : 'instructor.course-show');
        
        return view($school->resolveView($view), compact('school', 'course'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(School $school, Course $course)
    {
        // Authorization check can be added here if needed
        return view($school->resolveView('admin.course-edit'), compact('school', 'course'));
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, School $school, Course $course)
    {
        // Authorization check can be added here if needed
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|numeric|min:0',
            'max_students' => 'nullable|integer|min:1',
            'type' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive,archived',
        ]);

        $course->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Course updated successfully',
                'course' => $course
            ]);
        }

        return redirect()->route('courses.show', [$school->slug, $course->id])
            ->with('success', 'Course updated successfully');
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Request $request, School $school, Course $course)
    {
        // Authorization check can be added here if needed
        
        try {
            // Check if course has bookings
            $bookingsCount = $course->bookings()->count();
            
            if ($bookingsCount > 0) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot delete course. It has {$bookingsCount} booking(s). Please delete or reassign bookings first."
                    ], 400);
                }
                
                return redirect()->back()
                    ->with('error', "Cannot delete course. It has {$bookingsCount} booking(s).");
            }
            
            // Delete related records first
            $course->progresses()->delete();
            
            // Now delete the course
            $course->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Course deleted successfully'
                ]);
            }

            return redirect()->route('courses.index', $school->slug)
                ->with('success', 'Course deleted successfully');
                
        } catch (\Exception $e) {
            Log::error('Course deletion error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting course: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting course. Please try again.');
        }
    }
}
