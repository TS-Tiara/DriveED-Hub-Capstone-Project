<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Invitation;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\SystemLog;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding form for the given token.
     */
    public function show(School $school, $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('school_id', $school->id)
            ->first();

        if (!$invitation) {
            return redirect()->route('schools.login', $school)
                ->with('error', 'Invalid or expired invitation link.');
        }

        if ($invitation->isUsed()) {
            return redirect()->route('schools.login', $school)
                ->with('error', 'This invitation has already been used.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('schools.login', $school)
                ->with('error', 'This invitation has expired. Please request a new one.');
        }

        return view($school->resolveView('auth.onboarding'), [
            'school' => $school,
            'invitation' => $invitation,
            'role' => $invitation->role
        ]);
    }

    /**
     * Submit the onboarding form and create the account.
     */
    public function submit(Request $request, School $school, $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('school_id', $school->id)
            ->firstOrFail();

        if ($invitation->isUsed() || $invitation->isExpired()) {
            return redirect()->route('schools.login', $school)->with('error', 'Invalid invitation.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'password' => ['required', 'confirmed', new StrongPassword()],
        ];

        // Specific fields based on role
        if ($invitation->role === 'student' || $invitation->role === 'instructor') {
            $rules['contact'] = ['required', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9})$/'];
            $rules['address'] = 'required|string|max:500';
        }

        $validated = $request->validate($rules);

        if ($invitation->role === 'branch_secretary') {
            if (!$invitation->branch_id) {
                return back()->with('error', 'Branch assignment is required for branch manager invitations.');
            }

            $capacity = $this->buildBranchSecretaryCapacitySummary(
                $school,
                (int) $invitation->branch_id,
                $invitation->id
            );

            if (($capacity['used'] + 1) > $capacity['limit']) {
                return back()->with(
                    'error',
                    "This invitation can no longer be accepted because the selected branch is at capacity ({$capacity['used']}/{$capacity['limit']})."
                );
            }
        }

        try {
            DB::beginTransaction();

            $commonData = [
                'school_id' => $school->id,
                'branch_id' => $invitation->branch_id,
                'name' => $validated['name'],
                'email' => $invitation->email,
                'password' => Hash::make($validated['password']),
                'must_reset_password' => true, // Enforce reset on first login via normal flow
            ];

            $user = null;
            $guard = 'admin';

            switch ($invitation->role) {
                case 'school_admin':
                    $user = Admin::create(array_merge($commonData, [
                        'role' => $invitation->role,
                        'status' => 'active',
                    ]));
                    $guard = 'admin';
                    break;

                case 'branch_secretary':
                    if (!$invitation->branch_id) {
                        throw new \Exception('Branch assignment required for branch manager invitation.');
                    }

                    $user = Admin::create(array_merge($commonData, [
                        'role' => $invitation->role,
                        'status' => 'active',
                    ]));
                    $guard = 'admin';
                    break;

                case 'instructor':
                    $payload = $invitation->payload ?? [];
                    $user = Instructor::create(array_merge($commonData, [
                        'license_number' => $payload['license_number'] ?? null,
                        'contact' => $validated['contact'],
                        'address' => $validated['address'],
                        'status' => 'active',
                        'availability' => 'available',
                    ]));
                    $guard = 'instructor';
                    break;

                case 'student':
                    $user = Student::create(array_merge($commonData, [
                        'contact' => $validated['contact'],
                        'address' => $validated['address'],
                        'status' => 'active',
                    ]));
                    $guard = 'student';
                    break;

                default:
                    throw new \Exception("Unsupported role: {$invitation->role}");
            }

            // Mark invitation as used
            $invitation->update(['used_at' => now()]);

            SystemLog::logInfo(
                "User activated account via invitation: {$user->email}",
                'authentication',
                ['role' => $invitation->role, 'user_id' => $user->id],
                $school->id
            );

            DB::commit();

            // Log the user in
            Auth::guard($guard)->login($user);

            // Redirect based on role (AuthController should intercept the must_reset_password but just in case)
            $routes = [
                'admin' => 'schools.admin.dashboard',
                'instructor' => 'schools.instructor.dashboard',
                'student' => 'schools.student.dashboard'
            ];

            return redirect()->route($routes[$guard], $school)
                ->with('success', 'Welcome! Your account has been successfully activated.');

        } catch (\Exception $e) {
            DB::rollBack();
            SystemLog::logError("Onboarding failure: " . $e->getMessage(), 'database', $e, ['email' => $invitation->email], $school->id);
            return back()->with('error', 'Failed to activate account. Please try again or contact support.');
        }
    }

    private function getBranchSecretaryLimit(School $school): int
    {
        return max(1, (int) ($school->schoolSetting?->branch_secretary_limit_per_branch ?? 1));
    }

    private function buildBranchSecretaryCapacitySummary(
        School $school,
        int $branchId,
        ?int $excludeInvitationId = null
    ): array {
        $limit = $this->getBranchSecretaryLimit($school);

        $activeCount = Admin::query()
            ->where('school_id', $school->id)
            ->where('branch_id', $branchId)
            ->where('role', Admin::ROLE_BRANCH_SECRETARY)
            ->where('is_active', true)
            ->count();

        $pendingInvitationQuery = Invitation::query()
            ->where('school_id', $school->id)
            ->where('branch_id', $branchId)
            ->where('role', Admin::ROLE_BRANCH_SECRETARY)
            ->whereNull('used_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });

        if ($excludeInvitationId !== null) {
            $pendingInvitationQuery->where('id', '!=', $excludeInvitationId);
        }

        $pendingCount = $pendingInvitationQuery->count();
        $used = $activeCount + $pendingCount;

        return [
            'limit' => $limit,
            'active' => $activeCount,
            'pending' => $pendingCount,
            'used' => $used,
            'remaining' => max(0, $limit - $used),
            'at_capacity' => $used >= $limit,
        ];
    }
}
