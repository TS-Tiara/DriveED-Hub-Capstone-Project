<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\ProfileUnlockRequest;
use App\Models\School;
use App\Models\Student;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileUnlockRequestController extends Controller
{
    public function storeStudent(Request $request, School $school)
    {
        $student = Auth::guard('student')->user();

        if (!$student || (int) $student->school_id !== (int) $school->id) {
            abort(403, 'Unauthorized.');
        }

        if ((int) ($student->profile_edit_count ?? 0) < 1) {
            return back()->with('error', 'Your profile is not locked yet.');
        }

        $pendingRequest = ProfileUnlockRequest::query()
            ->where('school_id', $school->id)
            ->where('user_type', Student::class)
            ->where('user_id', $student->id)
            ->where('status', ProfileUnlockRequest::STATUS_PENDING)
            ->exists();

        if ($pendingRequest) {
            return back()->with('error', 'You already have a pending correction request.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        ProfileUnlockRequest::create([
            'school_id' => $school->id,
            'user_type' => Student::class,
            'user_id' => $student->id,
            'reason' => $validated['reason'] ?? null,
            'status' => ProfileUnlockRequest::STATUS_PENDING,
        ]);

        SystemLog::logInfo(
            'Student submitted a profile correction request.',
            'user_management',
            [
                'school_id' => $school->id,
                'user_type' => 'student',
                'user_id' => $student->id,
            ],
            $school->id,
            'profile_unlock_request_submit'
        );

        return back()->with('success', 'Correction request submitted. Please wait for admin review.');
    }

    public function storeInstructor(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        if (!$instructor || (int) $instructor->school_id !== (int) $school->id) {
            abort(403, 'Unauthorized.');
        }

        if ((int) ($instructor->profile_edit_count ?? 0) < 1) {
            return back()->with('error', 'Your profile is not locked yet.');
        }

        $pendingRequest = ProfileUnlockRequest::query()
            ->where('school_id', $school->id)
            ->where('user_type', Instructor::class)
            ->where('user_id', $instructor->id)
            ->where('status', ProfileUnlockRequest::STATUS_PENDING)
            ->exists();

        if ($pendingRequest) {
            return back()->with('error', 'You already have a pending correction request.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        ProfileUnlockRequest::create([
            'school_id' => $school->id,
            'user_type' => Instructor::class,
            'user_id' => $instructor->id,
            'reason' => $validated['reason'] ?? null,
            'status' => ProfileUnlockRequest::STATUS_PENDING,
        ]);

        SystemLog::logInfo(
            'Instructor submitted a profile correction request.',
            'user_management',
            [
                'school_id' => $school->id,
                'user_type' => 'instructor',
                'user_id' => $instructor->id,
            ],
            $school->id,
            'profile_unlock_request_submit'
        );

        return back()->with('success', 'Correction request submitted. Please wait for admin review.');
    }

    public function approve(School $school, ProfileUnlockRequest $profileUnlockRequest)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin || !$admin->isSchoolAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if ((int) $profileUnlockRequest->school_id !== (int) $school->id) {
            abort(404);
        }

        if (!$profileUnlockRequest->isPending()) {
            return back()->with('error', 'This correction request has already been processed.');
        }

        DB::transaction(function () use ($admin, $school, $profileUnlockRequest): void {
            $requestRecord = ProfileUnlockRequest::query()
                ->lockForUpdate()
                ->findOrFail($profileUnlockRequest->id);

            if (!$requestRecord->isPending()) {
                return;
            }

            $requestUser = $requestRecord->user;

            if ($requestUser && (int) $requestUser->school_id === (int) $school->id) {
                $requestUser->update([
                    'profile_edit_count' => 0,
                    'profile_locked_at' => null,
                ]);
            }

            $requestRecord->approve($admin);
        });

        SystemLog::logInfo(
            'Profile correction request approved by school admin.',
            'user_management',
            [
                'school_id' => $school->id,
                'request_id' => $profileUnlockRequest->id,
                'handled_by' => $admin->id,
            ],
            $school->id,
            'profile_unlock_request_approve'
        );

        return back()->with('success', 'Profile correction request approved. User can edit core details once again.');
    }

    public function deny(School $school, ProfileUnlockRequest $profileUnlockRequest)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin || !$admin->isSchoolAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if ((int) $profileUnlockRequest->school_id !== (int) $school->id) {
            abort(404);
        }

        if (!$profileUnlockRequest->isPending()) {
            return back()->with('error', 'This correction request has already been processed.');
        }

        DB::transaction(function () use ($admin, $profileUnlockRequest): void {
            $requestRecord = ProfileUnlockRequest::query()
                ->lockForUpdate()
                ->findOrFail($profileUnlockRequest->id);

            if (!$requestRecord->isPending()) {
                return;
            }

            $requestRecord->deny($admin);
        });

        SystemLog::logInfo(
            'Profile correction request denied by school admin.',
            'user_management',
            [
                'school_id' => $school->id,
                'request_id' => $profileUnlockRequest->id,
                'handled_by' => $admin->id,
            ],
            $school->id,
            'profile_unlock_request_deny'
        );

        return back()->with('success', 'Profile correction request denied.');
    }
}
