<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Http\Requests\MarkTheoreticalPassedRequest;
use App\Support\EnrollmentValidator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TheoreticalCompletionController extends Controller
{
    /**
     * Display students pending theoretical completion
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['instructor', 'admin', 'superadmin'])) {
            abort(403);
        }
        
        // Get active theoretical enrollments that haven't been marked as passed
        $enrollments = Enrollment::with(['student.user', 'course'])
            ->whereHas('course', function($query) {
                $query->where('course_type', 'theoretical');
            })
            ->where('status', 'active')
            ->where('theoretical_passed', false)
            ->latest()
            ->paginate(20);
        
        $viewPath = $user->role === 'instructor' 
            ? 'instructor.theoretical.index' 
            : 'admin.theoretical.index';
        
        return view($viewPath, compact('enrollments'));
    }

    /**
     * Show the form for marking a student as passed
     */
    public function show(Enrollment $enrollment)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['instructor', 'admin', 'superadmin'])) {
            abort(403);
        }
        
        $enrollment->load([
            'student.user', 
            'course',
            'sessionCompletions.instructor.user'
        ]);
        
        // Check if can be marked as passed
        $validation = EnrollmentValidator::canMarkTheoreticalPassed($enrollment);
        
        $viewPath = $user->role === 'instructor' 
            ? 'instructor.theoretical.show' 
            : 'admin.theoretical.show';
        
        return view($viewPath, compact('enrollment', 'validation'));
    }

    /**
     * Mark a student as passed theoretical
     */
    public function markAsPassed(MarkTheoreticalPassedRequest $request)
    {
        $user = Auth::user();
        
        $enrollment = Enrollment::findOrFail($request->enrollment_id);
        
        DB::beginTransaction();
        try {
            // Mark enrollment as theoretical passed
            $enrollment->update([
                'theoretical_passed' => true,
                'theoretical_passed_at' => now(),
                'theoretical_passed_by' => $user->id,
                'notes' => $request->notes,
            ]);
            
            // Also update the student record
            $enrollment->student->markTheoreticalPassed();
            
            DB::commit();
            
            return redirect()
                ->route(
                    $user->role === 'instructor' ? 'instructor.theoretical.index' : 'admin.theoretical.index'
                )
                ->with('success', 'Student marked as passed theoretical successfully. They can now enroll in practical courses.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to mark as passed: ' . $e->getMessage());
        }
    }

    /**
     * View all students who have passed theoretical
     */
    public function passed()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['instructor', 'admin', 'superadmin'])) {
            abort(403);
        }
        
        $students = Student::with(['user', 'enrollments' => function($query) {
                $query->where('theoretical_passed', true)
                      ->with(['course', 'theoreticalPassedBy']);
            }])
            ->where('has_passed_theoretical', true)
            ->latest('theoretical_passed_at')
            ->paginate(20);
        
        $viewPath = $user->role === 'instructor' 
            ? 'instructor.theoretical.passed' 
            : 'admin.theoretical.passed';
        
        return view($viewPath, compact('students'));
    }

    /**
     * Revoke theoretical passed status (admin only)
     */
    public function revoke(Enrollment $enrollment)
    {
        $user = Auth::user();
        
        // Only admins can revoke
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        if (!$enrollment->theoretical_passed) {
            return back()->with('error', 'This enrollment is not marked as passed.');
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
            $enrollment->update([
                'theoretical_passed' => false,
                'theoretical_passed_at' => null,
                'theoretical_passed_by' => null,
            ]);
            
            // Check if student has any other passed theoretical enrollments
            $hasOtherPassed = $enrollment->student->enrollments()
                ->where('id', '!=', $enrollment->id)
                ->where('theoretical_passed', true)
                ->exists();
            
            if (!$hasOtherPassed) {
                $enrollment->student->update([
                    'has_passed_theoretical' => false,
                    'theoretical_passed_at' => null,
                ]);
            }
            
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
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }
        
        $stats = [
            'total_passed' => Student::where('has_passed_theoretical', true)->count(),
            'pending_completion' => Enrollment::whereHas('course', function($query) {
                    $query->where('course_type', 'theoretical');
                })
                ->where('status', 'active')
                ->where('theoretical_passed', false)
                ->count(),
            'passed_this_month' => Student::where('has_passed_theoretical', true)
                ->whereMonth('theoretical_passed_at', now()->month)
                ->whereYear('theoretical_passed_at', now()->year)
                ->count(),
            'average_hours_to_pass' => Enrollment::where('theoretical_passed', true)
                ->whereHas('course', function($query) {
                    $query->where('course_type', 'theoretical');
                })
                ->avg(DB::raw('(SELECT SUM(hours_completed) FROM session_completions WHERE enrollment_id = enrollments.id)')),
        ];
        
        return response()->json($stats);
    }
}
