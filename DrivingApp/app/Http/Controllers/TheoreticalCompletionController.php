<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentRequest;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TheoreticalCompletionController extends Controller
{
    /**
     * Display students pending theoretical completion
     */
    public function index()
    {
        // Check authentication guards
        if (Auth::guard('admin')->check()) {
            $school = Auth::guard('admin')->user()->school;
            $viewPath = 'school.admin.theoretical.index';
        } elseif (Auth::guard('instructor')->check()) {
            $school = Auth::guard('instructor')->user()->school;
            $viewPath = 'school.instructor.theoretical.index';
        } else {
            abort(403, 'Unauthorized');
        }
        
        // Get active theoretical enrollments where student hasn't passed yet
        $enrollments = EnrollmentRequest::with(['learner', 'course', 'sessionCompletions'])
            ->whereHas('course', function($query) use ($school) {
                $query->where('school_id', $school->id)
                      ->where('course_type', 'theoretical');
            })
            ->whereHas('student', function($query) {
                $query->where('has_passed_theoretical', false);
            })
            ->where('status', 'approved')
            ->paginate(20);
        
        // Calculate total hours for each enrollment
        $enrollments->getCollection()->transform(function($enrollment) {
            $enrollment->total_hours = $enrollment->sessionCompletions->sum('hours_completed');
            return $enrollment;
        });
        
        // Get passed students for the "Passed Students" tab
        $passedStudents = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->with(['enrollments' => function($query) {
                $query->whereHas('course', function($q) {
                    $q->where('course_type', 'theoretical');
                })->where('status', 'completed');
            }])
            ->latest('updated_at')
            ->paginate(20, ['*'], 'passed_page');
        
        // Calculate stats for passed students
        $totalPassed = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->count();
        
        $passedThisMonth = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        
        return view($viewPath, compact('school', 'enrollments', 'passedStudents', 'totalPassed', 'passedThisMonth'));
    }

    /**
     * Show the form for marking a student as passed
     */
    public function show(School $school, $enrollment)
    {
        // Check authentication guards
        if (!Auth::guard('admin')->check() && !Auth::guard('instructor')->check()) {
            abort(403, 'Unauthorized');
        }
        
        // Fetch enrollment manually since route binding isn't working with multiple parameters
        $enrollment = EnrollmentRequest::with(['learner', 'course', 'sessionCompletions'])
            ->findOrFail($enrollment);
        
        // Verify enrollment belongs to this school
        if ($enrollment->course->school_id !== $school->id) {
            abort(404);
        }
        
        // Validate if student can be marked as passed
        $validation = $this->validateCanPassTheoretical($enrollment);
        
        $viewPath = Auth::guard('admin')->check() 
            ? 'school.admin.theoretical.show' 
            : 'school.instructor.theoretical.show';
        
        return view($viewPath, compact('school', 'enrollment', 'validation'));
    }
    
    /**
     * Validate if a student can be marked as passed theoretical
     */
    private function validateCanPassTheoretical(EnrollmentRequest $enrollment): array
    {
        // Already passed
        if ($enrollment->student->has_passed_theoretical) {
            return [
                'allowed' => false,
                'message' => 'This student has already passed theoretical training.'
            ];
        }
        
        // Not a theoretical course
        if ($enrollment->course->course_type !== 'theoretical') {
            return [
                'allowed' => false,
                'message' => 'This is not a theoretical course enrollment.'
            ];
        }
        
        // Check if minimum hours requirement met
        $totalHours = $enrollment->sessionCompletions->sum('hours_completed');
        $requiredHours = $enrollment->course->theoretical_hours ?? 15;
        
        if ($totalHours < $requiredHours) {
            return [
                'allowed' => false,
                'message' => "Student needs at least {$requiredHours} hours. Currently has {$totalHours} hours completed."
            ];
        }
        
        // All validations passed
        return [
            'allowed' => true,
            'message' => 'Student meets all requirements and can be marked as passed.'
        ];
    }

    /**
     * Mark a student as passed theoretical
     */
    public function markAsPassed(Request $request)
    {
        // Get authenticated user
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $redirectRoute = 'schools.admin.theoretical.index';
        } elseif (Auth::guard('instructor')->check()) {
            $user = Auth::guard('instructor')->user();
            $redirectRoute = 'schools.instructor.theoretical.index';
        } else {
            abort(403, 'Unauthorized');
        }
        
        $request->validate([
            'enrollment_id' => 'required|exists:enrollment_requests,id',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $enrollment = EnrollmentRequest::with(['learner', 'course'])->findOrFail($request->enrollment_id);
        
        // Verify it's a theoretical course
        if ($enrollment->course->course_type !== 'theoretical') {
            return back()->with('error', 'This is not a theoretical course enrollment.');
        }
        
        // Verify student hasn't already passed
        if ($enrollment->student->has_passed_theoretical) {
            return back()->with('error', 'Student has already passed theoretical.');
        }
        
        DB::beginTransaction();
        try {
            // Update student record
            $enrollment->student->update([
                'has_passed_theoretical' => true,
            ]);
            
            // Complete the enrollment
            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            
            DB::commit();
            
            return redirect()
                ->route($redirectRoute, ['school' => $user->school->slug])
                ->with('success', 'Student marked as passed theoretical successfully! They can now enroll in practical courses.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Failed to mark as passed: ' . $e->getMessage());
        }
    }

    /**
     * View all students who have passed theoretical
     */
    public function passed()
    {
        // Check authentication guards
        if (Auth::guard('admin')->check()) {
            $school = Auth::guard('admin')->user()->school;
            $viewPath = 'school.admin.theoretical.passed';
        } elseif (Auth::guard('instructor')->check()) {
            $school = Auth::guard('instructor')->user()->school;
            $viewPath = 'school.instructor.theoretical.passed';
        } else {
            abort(403, 'Unauthorized');
        }
        
        $passedStudents = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->with(['enrollments' => function($query) {
                $query->whereHas('course', function($q) {
                    $q->where('course_type', 'theoretical');
                })->where('status', 'completed');
            }])
            ->latest('updated_at')
            ->paginate(20);
        
        return view($viewPath, compact('school', 'passedStudents'));
    }

    /**
     * Revoke theoretical passed status (admin only)
     */
    public function revoke(Enrollment $enrollment)
    {
        // Only admins can revoke
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Only administrators can revoke theoretical status.');
        }
        
        $user = Auth::guard('admin')->user();
        
        if (!$enrollment->student->has_passed_theoretical) {
            return back()->with('error', 'This student has not passed theoretical.');
        }
        
        // Check if student has active practical enrollments
        $hasPracticalEnrollments = $enrollment->student->enrollments()
            ->whereHas('course', function($query) {
                $query->where('course_type', 'practical');
            })
            ->where('status', 'active')
            ->exists();
        
        if ($hasPracticalEnrollments) {
            return back()->with('error', 'Cannot revoke theoretical status. Student has active practical enrollments.');
        }
        
        DB::beginTransaction();
        try {
            $enrollment->student->update([
                'has_passed_theoretical' => false,
            ]);
            
            $enrollment->update([
                'status' => 'active',
                'completed_at' => null,
            ]);
            
            DB::commit();
            
            return back()->with('success', 'Theoretical passed status revoked successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to revoke status: ' . $e->getMessage());
        }
    }

    /**
     * Get theoretical completion statistics
     */
    public function stats()
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }
        
        $school = Auth::guard('admin')->user()->school;
        
        $stats = [
            'total_passed' => Student::where('school_id', $school->id)
                ->where('has_passed_theoretical', true)
                ->count(),
            'pending_completion' => EnrollmentRequest::whereHas('course', function($query) use ($school) {
                    $query->where('school_id', $school->id)
                          ->where('course_type', 'theoretical');
                })
                ->whereHas('student', function($query) {
                    $query->where('has_passed_theoretical', false);
                })
                ->where('status', 'approved')
                ->count(),
            'passed_this_month' => Student::where('school_id', $school->id)
                ->where('has_passed_theoretical', true)
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->count(),
        ];
        
        return response()->json($stats);
    }
}
