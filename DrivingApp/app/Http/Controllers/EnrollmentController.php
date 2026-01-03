<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\EnrollmentRequest;
use App\Models\School;
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
    public function index(School $school = null)
    {
        // Student view
        if (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            $enrollments = $student->enrollments()
                ->with(['course', 'sessionCompletions'])
                ->latest()
                ->get();
            
            return view('school.student.enrollments.index', compact('student', 'enrollments', 'school'));
        }
        
        // Instructor view
        if (Auth::guard('instructor')->check()) {
            $instructor = Auth::guard('instructor')->user();
            $school = $school ?? $instructor->school;
            
            // Show enrollments for students this instructor has taught
            $enrollments = Enrollment::with(['student', 'course', 'sessionCompletions'])
                ->whereHas('sessionCompletions', function($query) use ($instructor) {
                    $query->where('instructor_id', $instructor->id);
                })
                ->whereHas('course', function($query) use ($school) {
                    $query->where('school_id', $school->id);
                })
                ->latest()
                ->paginate(20);
            
            return view('school.instructor.enrollments.index', compact('school', 'enrollments'));
        }
        
        // Admin view
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $school = $school ?? $admin->school;
            
            // Show all enrollments for the school
            $enrollments = Enrollment::with(['student', 'course', 'sessionCompletions'])
                ->whereHas('course', function($query) use ($school) {
                    $query->where('school_id', $school->id);
                })
                ->latest()
                ->paginate(20);
            
            return view('school.admin.enrollments.index', compact('school', 'enrollments'));
        }
        
        abort(403);
    }

    /**
     * Display the specified enrollment
     */
    public function show(School $school, $enrollment)
    {
        // Fetch enrollment manually
        $enrollment = Enrollment::with(['student', 'course.modules.lessons', 'sessionCompletions.instructor'])
            ->findOrFail($enrollment);
        
        // Verify enrollment belongs to this school
        if ($enrollment->course->school_id !== $school->id) {
            abort(404);
        }
        
        // Student view - only their own enrollment
        if (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            if ($enrollment->student_id !== $student->id) {
                abort(403);
            }
            return view('school.student.enrollments.show', compact('school', 'enrollment'));
        }
        
        // Instructor view
        if (Auth::guard('instructor')->check()) {
            return view('school.instructor.enrollments.show', compact('school', 'enrollment'));
        }
        
        // Admin view
        if (Auth::guard('admin')->check()) {
            return view('school.admin.enrollments.show', compact('school', 'enrollment'));
        }
        
        abort(403);
    }

    /**
     * Create enrollment from approved enrollment request
     * (Called by admin when approving an enrollment request)
     */
    public function createFromRequest(School $school, EnrollmentRequest $enrollmentRequest)
    {
        // Only admins can create enrollments
        if (!Auth::guard('admin')->check()) {
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
                ->route('schools.admin.enrollments.show', ['school' => $school->slug, 'enrollment' => $enrollment->id])
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
    public function complete(School $school, $enrollment)
    {
        // Fetch enrollment
        $enrollment = Enrollment::findOrFail($enrollment);
        
        // Verify belongs to school
        if ($enrollment->course->school_id !== $school->id) {
            abort(404);
        }
        
        // Only instructors and admins can complete enrollments
        if (!Auth::guard('instructor')->check() && !Auth::guard('admin')->check()) {
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
    public function cancel(School $school, $enrollment)
    {
        // Fetch enrollment
        $enrollment = Enrollment::findOrFail($enrollment);
        
        // Verify belongs to school
        if ($enrollment->course->school_id !== $school->id) {
            abort(404);
        }
        
        // Students can drop their own enrollments
        if (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            if ($enrollment->student_id !== $student->id) {
                abort(403);
            }
        } 
        // Admins can cancel any
        elseif (!Auth::guard('admin')->check()) {
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
    public function stats(School $school)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }
        
        $stats = [
            'total' => Enrollment::whereHas('course', function($query) use ($school) {
                $query->where('school_id', $school->id);
            })->count(),
            'active' => Enrollment::whereHas('course', function($query) use ($school) {
                $query->where('school_id', $school->id);
            })->where('status', 'active')->count(),
            'completed' => Enrollment::whereHas('course', function($query) use ($school) {
                $query->where('school_id', $school->id);
            })->where('status', 'completed')->count(),
            'dropped' => Enrollment::whereHas('course', function($query) use ($school) {
                $query->where('school_id', $school->id);
            })->where('status', 'dropped')->count(),
        ];
        
        return response()->json($stats);
    }
}
