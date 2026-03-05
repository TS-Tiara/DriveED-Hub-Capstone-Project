<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\School;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    /**
     * List all admins and branch secretaries for this school.
     * Only accessible by school_admin role.
     */
    public function index(School $school)
    {
        $admin = Auth::guard('admin')->user();

        $admins = Admin::where('school_id', $school->id)
            ->whereIn('role', [Admin::ROLE_SCHOOL_ADMIN, Admin::ROLE_BRANCH_SECRETARY])
            ->with('branch')
            ->orderByRaw("CASE role WHEN 'school_admin' THEN 0 WHEN 'branch_secretary' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get();

        $branches = Branch::where('school_id', $school->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Calculate which branches already have a secretary
        $branchesWithSecretary = Admin::where('school_id', $school->id)
            ->where('role', Admin::ROLE_BRANCH_SECRETARY)
            ->where('is_active', true)
            ->pluck('branch_id')
            ->filter()
            ->toArray();

        return view('school.admin.admin-management.index', compact(
            'school', 'admin', 'admins', 'branches', 'branchesWithSecretary'
        ));
    }

    /**
     * Store a new admin or branch secretary.
     */
    public function store(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->canManageAdmins()) {
            return redirect()->back()->with('error', 'You do not have permission to manage administrators.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'contact' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:school_admin,branch_secretary'],
            'branch_id' => ['required_if:role,branch_secretary', 'nullable', 'exists:branches,id'],
        ]);

        // If creating secretary, verify branch belongs to this school
        if ($validated['role'] === Admin::ROLE_BRANCH_SECRETARY && $validated['branch_id']) {
            $branch = Branch::where('id', $validated['branch_id'])
                ->where('school_id', $school->id)
                ->first();

            if (!$branch) {
                return redirect()->back()->with('error', 'Selected branch does not belong to this school.');
            }
        }

        $newAdmin = Admin::create([
            'school_id' => $school->id,
            'branch_id' => $validated['role'] === Admin::ROLE_BRANCH_SECRETARY ? $validated['branch_id'] : null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Will be hashed by model cast
            'must_reset_password' => true, // Force reset on first login
            'role' => $validated['role'],
            'contact' => $validated['contact'] ?? null,
            'is_active' => true,
        ]);

        $roleLabel = $validated['role'] === Admin::ROLE_BRANCH_SECRETARY ? 'Branch Secretary' : 'School Admin';

        SystemLog::logInfo(
            "New {$roleLabel} '{$newAdmin->name}' created by {$admin->name}",
            'admin',
        [
            'new_admin_id' => $newAdmin->id,
            'role' => $newAdmin->role,
            'branch_id' => $newAdmin->branch_id,
            'created_by' => $admin->id,
        ],
            $school->id,
            'admin_created'
        );

        return redirect()->back()->with('success', "{$roleLabel} '{$newAdmin->name}' has been created successfully.");
    }

    /**
     * Update an existing admin/secretary.
     */
    public function update(Request $request, School $school, Admin $targetAdmin)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->canManageAdmins()) {
            return redirect()->back()->with('error', 'You do not have permission to manage administrators.');
        }

        // Cannot edit admins from other schools
        abort_if($targetAdmin->school_id !== $school->id, 404);

        // Cannot edit yourself through this controller (use profile page)
        if ($targetAdmin->id === $admin->id) {
            return redirect()->back()->with('error', 'Use the profile page to edit your own account.');
        }

        // Cannot edit system admins
        if ($targetAdmin->isSystemAdmin()) {
            return redirect()->back()->with('error', 'System administrators cannot be edited here.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($targetAdmin->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'contact' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:school_admin,branch_secretary'],
            'branch_id' => ['required_if:role,branch_secretary', 'nullable', 'exists:branches,id'],
        ]);

        // If updating to secretary, verify branch belongs to this school
        if ($validated['role'] === Admin::ROLE_BRANCH_SECRETARY && $validated['branch_id']) {
            $branch = Branch::where('id', $validated['branch_id'])
                ->where('school_id', $school->id)
                ->first();

            if (!$branch) {
                return redirect()->back()->with('error', 'Selected branch does not belong to this school.');
            }
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'contact' => $validated['contact'] ?? null,
            'role' => $validated['role'],
            'branch_id' => $validated['role'] === Admin::ROLE_BRANCH_SECRETARY ? $validated['branch_id'] : null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password']; // Hashed by model
        }

        $targetAdmin->update($updateData);

        SystemLog::logInfo(
            "Admin '{$targetAdmin->name}' updated by {$admin->name}",
            'admin',
        [
            'admin_id' => $targetAdmin->id,
            'new_role' => $targetAdmin->role,
            'branch_id' => $targetAdmin->branch_id,
            'updated_by' => $admin->id,
        ],
            $school->id,
            'admin_updated'
        );

        return redirect()->back()->with('success', "Administrator '{$targetAdmin->name}' has been updated.");
    }

    /**
     * Toggle an admin's active status (activate/deactivate).
     */
    public function toggleStatus(School $school, Admin $targetAdmin)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->canManageAdmins()) {
            return redirect()->back()->with('error', 'You do not have permission to manage administrators.');
        }

        abort_if($targetAdmin->school_id !== $school->id, 404);

        // Cannot deactivate yourself
        if ($targetAdmin->id === $admin->id) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        // Cannot modify system admins
        if ($targetAdmin->isSystemAdmin()) {
            return redirect()->back()->with('error', 'System administrators cannot be modified here.');
        }

        $targetAdmin->update([
            'is_active' => !$targetAdmin->is_active,
        ]);

        $statusLabel = $targetAdmin->is_active ? 'activated' : 'deactivated';

        SystemLog::logInfo(
            "Admin '{$targetAdmin->name}' {$statusLabel} by {$admin->name}",
            'admin',
        [
            'admin_id' => $targetAdmin->id,
            'new_status' => $targetAdmin->is_active,
            'toggled_by' => $admin->id,
        ],
            $school->id,
            'admin_status_toggled'
        );

        return redirect()->back()->with('success', "Administrator '{$targetAdmin->name}' has been {$statusLabel}.");
    }

    /**
     * Delete an admin/secretary.
     */
    public function destroy(School $school, Admin $targetAdmin)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->canManageAdmins()) {
            return redirect()->back()->with('error', 'You do not have permission to manage administrators.');
        }

        abort_if($targetAdmin->school_id !== $school->id, 404);

        // Cannot delete yourself
        if ($targetAdmin->id === $admin->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Cannot delete system admins
        if ($targetAdmin->isSystemAdmin()) {
            return redirect()->back()->with('error', 'System administrators cannot be deleted here.');
        }

        $name = $targetAdmin->name;
        $role = $targetAdmin->role;
        $targetAdmin->delete();

        SystemLog::logInfo(
            "Admin '{$name}' (role: {$role}) deleted by {$admin->name}",
            'admin',
        [
            'deleted_admin_name' => $name,
            'deleted_role' => $role,
            'deleted_by' => $admin->id,
        ],
            $school->id,
            'admin_deleted'
        );

        return redirect()->back()->with('success', "Administrator '{$name}' has been removed.");
    }
}
