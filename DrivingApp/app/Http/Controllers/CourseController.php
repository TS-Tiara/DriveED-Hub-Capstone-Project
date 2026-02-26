<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\School;
use App\Models\Student;
use App\Support\EnrollmentValidator;
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
        $courses = Course::where('school_id', '=', $school->id)
            ->with('packages', 'modules')
            ->when(request('status'), function ($query, $status) {
            return $query->where('status', '=', $status);
        })
            ->when(request('course_type'), function ($query, $type) {
            return $query->where('course_type', '=', $type);
        })
            ->when(request('license_type'), function ($query, $license) {
            return $query->where('license_type', '=', $license);
        })
            ->orderBy('sort_order')
            ->orderBy('is_featured', 'desc')
            ->latest()
            ->paginate(10);

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'courses' => $courses
            ]);
        }

        // Determine which view to use based on user role (only admin and student have course views)
        $guard = Auth::guard('admin')->check() ? 'admin' : 'student';
        $view = $guard === 'admin' ? 'admin.courses' : 'student.courses';

        // Get instructors for admin view
        $instructors = [];
        if ($guard === 'admin') {
            $instructors = \App\Models\Instructor::where('school_id', '=', $school->id)->get(['*']);
        }

        // Check if this is an AJAX request
        $isAjax = request()->ajax();

        // For student view, pass enrollment status per course so cards can show badges
        $enrollmentStatuses = [];
        if ($guard === 'student') {
            $student = Auth::guard('student')->user();
            if ($student) {
                $requests = \App\Models\EnrollmentRequest::where('learner_id', '=', $student->id)
                    ->where('school_id', '=', $school->id)
                    ->whereIn('status', ['pending', 'approved', 'completed'])
                    ->get(['course_id', 'status']);
                foreach ($requests as $req) {
                    $enrollmentStatuses[$req->course_id] = $req->status;
                }
            }
        }

        return view($school->resolveView($view), compact('school', 'courses', 'instructors', 'isAjax', 'enrollmentStatuses'));
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
            'banner_image' => 'nullable|image|max:2048',
            'price' => 'nullable|numeric|min:0',
            'duration_hours' => 'nullable|numeric|min:0',
            'max_students' => 'nullable|integer|min:1',
            'type' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:100',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,archived',
            'is_featured' => 'nullable',
            'schedules' => 'nullable|array',
            'schedules.*.date' => 'required_with:schedules|date',
            'schedules.*.start_time' => 'required_with:schedules',
            'schedules.*.end_time' => 'required_with:schedules',
            'schedules.*.max_students' => 'nullable|integer|min:1',
            'schedules.*.instructor_id' => 'nullable|exists:instructors,id',
            'schedules.*.notes' => 'nullable|string',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('courses/banners', 'public');
        }

        // Handle is_featured checkbox
        $validated['is_featured'] = $request->has('is_featured');

        // Filter out empty features
        if (isset($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features'], fn($f) => !empty(trim($f))));
        }

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

        $course->load(['bookings.student', 'bookings.instructor', 'modules.lessons']);

        // Check if student can enroll (for student view)
        $canEnroll = null;
        $enrollmentValidation = null;

        if (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            if ($student) {
                $enrollmentValidation = EnrollmentValidator::canEnrollInCourse($student, $course);
                $canEnroll = $enrollmentValidation['allowed'];
            }
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'course' => $course,
                'can_enroll' => $canEnroll,
                'enrollment_message' => $enrollmentValidation['message'] ?? null,
            ]);
        }

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = $guard === 'admin' ? 'admin.course-show' : ($guard === 'student' ? 'student.course-show' : 'instructor.course-show');

        return view($school->resolveView($view), compact('school', 'course', 'canEnroll', 'enrollmentValidation'));
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
            'banner_image' => 'nullable|image|max:2048',
            'price' => 'nullable|numeric|min:0',
            'duration_hours' => 'nullable|numeric|min:0',
            'max_students' => 'nullable|integer|min:1',
            'type' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:100',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,archived',
            'is_featured' => 'nullable',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('courses/banners', 'public');
        }
        else {
            unset($validated['banner_image']);
        }

        // Handle is_featured checkbox
        $validated['is_featured'] = $request->has('is_featured');

        // Filter out empty features
        if (isset($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features'], fn($f) => !empty(trim($f))));
        }

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

        }
        catch (\Exception $e) {
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
