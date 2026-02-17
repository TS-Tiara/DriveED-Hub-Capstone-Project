<?php

namespace App\Http\Controllers;

use App\Models\PhaseProgression;
use App\Models\School;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PhaseProgressionController extends Controller
{
    /**
     * Display all phase progression requests (Admin view)
     */
    public function index(School $school, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        $progressions = PhaseProgression::with(['enrollment.student', 'enrollment.course', 'reviewedBy'])
            ->forSchool($school->id)
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->from_phase, function ($query, $phase) {
                $query->where('from_phase', $phase);
            })
            ->latest('requested_at')
            ->paginate(20);

        $pendingCount = PhaseProgression::forSchool($school->id)->pending()->count();

        return view('school.admin.phase-progressions.index', compact('school', 'progressions', 'pendingCount'));
    }

    /**
     * Approve a phase progression request
     */
    public function approve(School $school, PhaseProgression $phaseProgression, Request $request)
    {
        // Security: verify belongs to this school
        abort_if($phaseProgression->school_id !== $school->id, 404);

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Cannot approve if not pending
        if (!$phaseProgression->isPending()) {
            return redirect()
                ->back()
                ->with('error', 'This progression request has already been reviewed.');
        }

        DB::beginTransaction();
        try {
            // Approve the progression request
            $phaseProgression->approve($admin->id, $request->input('admin_notes'));

            // Update the enrollment if needed (e.g., mark theoretical → practical transition)
            $enrollment = $phaseProgression->enrollment;
            if ($enrollment && $phaseProgression->to_phase === 'practical') {
                // Student is moving to practical phase
                $enrollment->update(['theoretical_passed' => true, 'theoretical_passed_at' => now()]);
            }

            // If completing final phase, mark enrollment as completed
            if ($enrollment && $phaseProgression->to_phase === 'completed') {
                $enrollment->markAsCompleted();

                // Unlock student from course
                $student = $enrollment->student;
                if ($student) {
                    $student->unlockFromCourse();
                }
            }

            // Send notification to student
            if ($enrollment && $enrollment->student) {
                try {
                    Notification::send(
                        $enrollment->student,
                        'phase_progression_approved',
                        'Phase Progression Approved',
                        "Your progression from {$phaseProgression->from_phase} to {$phaseProgression->to_phase} has been approved.",
                        'success',
                        "/{$school->slug}/student"
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send phase progression notification: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', "Phase progression approved: {$phaseProgression->getTransitionLabel()}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Phase progression approval failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Failed to approve phase progression. Please try again.');
        }
    }

    /**
     * Reject a phase progression request
     */
    public function reject(School $school, PhaseProgression $phaseProgression, Request $request)
    {
        // Security: verify belongs to this school
        abort_if($phaseProgression->school_id !== $school->id, 404);

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Cannot reject if not pending
        if (!$phaseProgression->isPending()) {
            return redirect()
                ->back()
                ->with('error', 'This progression request has already been reviewed.');
        }

        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $phaseProgression->reject($admin->id, $request->input('admin_notes'));

            // Send notification to student
            $enrollment = $phaseProgression->enrollment;
            if ($enrollment && $enrollment->student) {
                try {
                    Notification::send(
                        $enrollment->student,
                        'phase_progression_rejected',
                        'Phase Progression Not Approved',
                        "Your progression from {$phaseProgression->from_phase} to {$phaseProgression->to_phase} was not approved. Reason: {$request->input('admin_notes')}",
                        'warning',
                        "/{$school->slug}/student"
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send phase progression rejection notification: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Phase progression request rejected.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Phase progression rejection failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Failed to reject phase progression. Please try again.');
        }
    }
}
