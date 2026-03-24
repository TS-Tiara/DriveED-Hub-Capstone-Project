<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentRequest;
use App\Models\School;
use App\Models\Student;
use App\Models\SessionCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LogFacade;

class TheoreticalCompletionController extends Controller
{
    /**
     * Get the authenticated school and guard info
     */
    private function getSchoolAndGuard(): array
    {
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            return [
                'school' => $admin->school,
                'guard' => 'admin',
                'viewPrefix' => 'school.admin.theoretical',
                'routePrefix' => 'schools.admin.theoretical',
            ];
        }

        $instructor = Auth::guard('instructor')->user();
        if ($instructor) {
            return [
                'school' => $instructor->school,
                'guard' => 'instructor',
                'viewPrefix' => 'school.instructor.theoretical',
                'routePrefix' => 'schools.instructor.theoretical',
            ];
        }

        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Unauthorized');
    }

    /**
     * Unified theoretical training page with tabs: In Training, Mark Completion, Passed
     */
    public function index()
    {
        $ctx = $this->getSchoolAndGuard();
        $school = $ctx['school'];

        $activeQuery = EnrollmentRequest::with(['learner', 'course', 'sessionCompletions'])
            ->whereHas('course', function ($query) use ($school) {
            $query->where('school_id', $school->id)
                ->where('course_type', 'theoretical');
        })
            ->whereHas('student', function ($query) {
            $query->where('has_passed_theoretical', false);
        })
            ->where('status', 'approved');

        // Calculate absolute totals for stats before pagination
        $totalInTraining = (clone $activeQuery)->count();
        $readyToPass = (clone $activeQuery)->get()->filter(function ($enrollment) {
            $enrollment->total_hours = $enrollment->sessionCompletions->sum('hours_completed');
            $enrollment->required_hours = $enrollment->course->theoretical_hours ?? $enrollment->course->hours_required ?? 15;
            return $enrollment->required_hours > 0 && ($enrollment->total_hours / $enrollment->required_hours) >= 1;
        })->count();

        $activeEnrollments = $activeQuery->paginate(10, ['*'], 'active_page');

        // Calculate hours & progress for the current page
        $activeEnrollments->getCollection()->transform(function ($enrollment) {
            $enrollment->total_hours = $enrollment->sessionCompletions->sum('hours_completed');
            $enrollment->required_hours = $enrollment->course->theoretical_hours ?? $enrollment->course->hours_required ?? 15;
            $enrollment->progress = $enrollment->required_hours > 0
                ? min(100, round(($enrollment->total_hours / $enrollment->required_hours) * 100))
                : 0;
            $enrollment->session_count = $enrollment->sessionCompletions->count();
            $enrollment->last_session = $enrollment->sessionCompletions->sortByDesc('session_date')->first();
            return $enrollment;
        });

        // Split for Mark Completion tab (representing ONLY current page)
        $readyEnrollments = $activeEnrollments->getCollection()->where('progress', '>=', 100)->values();
        $notReadyEnrollments = $activeEnrollments->getCollection()->where('progress', '<', 100)->values();

        // ── Passed Students tab ──
        $passedStudents = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->latest('updated_at')
            ->paginate(10, ['*'], 'passed_page');

        $totalPassed = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->count();

        return view($ctx['viewPrefix'] . '.index', compact(
            'school', 'activeEnrollments', 'totalInTraining', 'readyToPass',
            'readyEnrollments', 'notReadyEnrollments',
            'passedStudents', 'totalPassed'
        ));
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
        $ctx = $this->getSchoolAndGuard();
        $user = Auth::guard($ctx['guard'])->user();

        if (!$user) {
            abort(403);
        }

        $redirectRoute = $ctx['routePrefix'] . '.index';

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
            // AUD-004 Fix: Sync theoretical completion flags using model helper
            $enrollment->markTheoreticalPassed($user->id, $request->notes);

            // Complete the enrollment
            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route($redirectRoute, ['school' => $user->school->slug])
                ->with('success', 'Student marked as passed theoretical successfully! They can now enroll in practical courses.');

        }
        catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to mark student as passed theoretical: ' . $e->getMessage(), [
                'enrollment_id' => $request->enrollment_id,
                'exception' => $e
            ]);
            return back()
                ->with('error', 'Unable to mark student as passed at this time. Please try again later.');
        }
    }

    /**
     * Revoke theoretical passed status (admin only)
     */
    public function revoke(School $school, $enrollment)
    {
        // Manually resolve enrollment to avoid scopeBindings() conflict
        $enrollment = EnrollmentRequest::findOrFail($enrollment);

        // Only admins can revoke
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Only administrators can revoke theoretical status.');
        }

        // Verify enrollment belongs to this school
        if ($enrollment->school_id !== $school->id) {
            abort(404);
        }

        $user = Auth::guard('admin')->user();

        if (!$enrollment->student->has_passed_theoretical) {
            return back()->with('error', 'This student has not passed theoretical.');
        }

        // Check if student has active practical enrollments
        $hasPracticalEnrollments = $enrollment->student->enrollments()
            ->whereHas('course', function ($query) {
            $query->where('course_type', 'practical');
        })
            ->where('status', 'approved')
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
                'status' => 'approved',
                'completed_at' => null,
            ]);

            DB::commit();

            return back()->with('success', 'Theoretical passed status revoked successfully.');

        }
        catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to revoke theoretical status: ' . $e->getMessage(), [
                'enrollment_id' => $enrollment->id,
                'school_id' => $school->id,
                'exception' => $e
            ]);
            return back()->with('error', 'Unable to revoke status at this time. Please try again later.');
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

        $school = Auth::guard('admin')->user()?->school;

        $passed = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->orderBy('updated_at', 'desc')
            ->paginate(15, ['*'], 'passed_page');

        $pending = EnrollmentRequest::whereHas('course', function ($query) use ($school) {
            $query->where('school_id', $school->id)
                ->where('course_type', 'theoretical');
        })
            ->whereHas('student', function ($query) {
            $query->where('has_passed_theoretical', false);
        })
            ->where('status', 'approved')
            ->count();

        $passedThisMonth = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $stats = [
            'total_passed' => $passed,
            'pending_completion' => $pending,
            'passed_this_month' => $passedThisMonth,
        ];

        return response()->json($stats);
    }
}
