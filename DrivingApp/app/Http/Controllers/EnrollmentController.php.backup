<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\EnrollmentRequest;
use App\Models\Student;
use App\Models\Course;
use App\Support\EnrollmentValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
            $enrollments = $student ? $student->enrollments()->with('course')->latest()->get() : collect();
            
            return view('student.enrollments.index', compact('enrollments', 'student'));
        }
        
        if ($user->role === 'instructor') {
            // Show enrollments for students assigned to this instructor
            $enrollments = Enrollment::with(['student.user', 'course'])
                ->whereHas('sessionCompletions', function($query) use ($user) {
                    $instructor = \App\Models\Instructor::where('user_id', $user->id)->first();
                    if ($instructor) {
                        $query->where('instructor_id', $instructor->id);
                    }
                })
                ->latest()
                ->paginate(20);
            
            return view('instructor.enrollments.index', compact('enrollments'));
        }
        
        if (in_array($user->role, ['admin', 'superadmin'])) {
            // Show all enrollments
            $enrollments = Enrollment::with(['student.user', 'course'])
                ->latest()
                ->paginate(20);
            
            return view('admin.enrollments.index', compact('enrollments'));
        }
        
        abort(403);
    }

    /**
     * Display the specified enrollment
     */
    public function show(Enrollment $enrollment)
    {
        $user = Auth::user();
        
        // Check authorization
        if ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
            if (!$student || $enrollment->student_id !== $student->id) {
                abort(403);
            }
        }
        
        $enrollment->load(['student.user', 'course.modules.lessons', 'sessionCompletions.instructor.user']);
        
        $viewPath = match($user->role) {
            'student' => 'student.enrollments.show',
            'instructor' => 'instructor.enrollments.show',
            'admin', 'superadmin' => 'admin.enrollments.show',
            default => abort(403)
        };
        
        return view($viewPath, compact('enrollment'));
    }

    /**
     * Create enrollment from approved enrollment request
     * (Called by admin when approving an enrollment request)
     */
    public function createFromRequest(EnrollmentRequest $enrollmentRequest)
    {
        $user = Auth::user();
        
        // Only admins can create enrollments
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        // Check if enrollment request is approved
        if ($enrollmentRequest->status !== 'approved') {
            return back()->with('error', 'Only approved enrollment requests can be converted to enrollments.');
        }
        
        // Check if enrollment already exists
        if ($enrollmentRequest->enrollment) {
            return back()->with('error', 'An enrollment already exists for this request.');
        }
        
        DB::beginTransaction();
        try {
            // Create the enrollment
            $enrollment = Enrollment::create([
                'student_id' => $enrollmentRequest->student_id,
                'course_id' => $enrollmentRequest->course_id,
                'enrollment_request_id' => $enrollmentRequest->id,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
            
            DB::commit();
            
            return redirect()
                ->route('admin.enrollments.show', $enrollment)
                ->with('success', 'Enrollment created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Validate if a student can enroll in a course
     * (AJAX endpoint for real-time validation)
     */
    public function validateEnrollment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
        ]);
        
        $student = Student::find($request->student_id);
        $course = Course::find($request->course_id);
        
        $validation = EnrollmentValidator::canEnrollInCourse($student, $course);
        
        return response()->json($validation);
    }

    /**
     * Mark enrollment as complete
     */
    public function complete(Enrollment $enrollment)
    {
        $user = Auth::user();
        
        // Only instructors and admins can complete enrollments
        if (!in_array($user->role, ['instructor', 'admin', 'superadmin'])) {
            abort(403);
        }
        
        // Validate enrollment can be completed
        $validation = EnrollmentValidator::canCompleteEnrollment($enrollment);
        
        if (!$validation['allowed']) {
            return back()->with('error', $validation['message']);
        }
        
        $enrollment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        
        return back()->with('success', 'Enrollment marked as complete.');
    }

    /**
     * Cancel/drop enrollment
     */
    public function cancel(Enrollment $enrollment)
    {
        $user = Auth::user();
        
        // Students can drop their own enrollments, admins can cancel any
        if ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
            if (!$student || $enrollment->student_id !== $student->id) {
                abort(403);
            }
        } elseif (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        // Cannot cancel already completed enrollments
        if ($enrollment->status === 'completed') {
            return back()->with('error', 'Cannot cancel completed enrollments.');
        }
        
        $enrollment->update([
            'status' => 'dropped',
        ]);
        
        return back()->with('success', 'Enrollment cancelled successfully.');
    }

    /**
     * Get enrollment statistics
     */
    public function stats()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        $stats = [
            'total' => Enrollment::count(),
            'active' => Enrollment::where('status', 'active')->count(),
            'completed' => Enrollment::where('status', 'completed')->count(),
            'dropped' => Enrollment::where('status', 'dropped')->count(),
            'theoretical_passed' => Enrollment::where('theoretical_passed', true)->count(),
            'pending_theoretical' => Enrollment::whereHas('course', function($query) {
                    $query->where('course_type', 'theoretical');
                })
                ->where('theoretical_passed', false)
                ->where('status', 'active')
                ->count(),
        ];
        
        return response()->json($stats);
    }
}
