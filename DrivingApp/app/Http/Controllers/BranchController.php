<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    /**
     * Display branch management page (or return JSON list for AJAX).
     */
    public function index(Request $request, School $school)
    {
        $branches = Branch::where('school_id', $school->id)
            ->withCount(['students', 'instructors'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'branches' => $branches,
            ]);
        }

        $settings = $school->schoolSetting;

        return view('school.admin.branches', compact('school', 'branches', 'settings'));
    }

    /**
     * Store a new branch.
     */
    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $validated['school_id'] = $school->id;

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $branch = Branch::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Branch created successfully!',
                'branch' => $branch,
            ], 201);
        }

        return redirect()->back()->with('success', 'Branch created successfully!');
    }

    /**
     * Update an existing branch.
     */
    public function update(Request $request, School $school, $id)
    {
        $branch = Branch::where('school_id', $school->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $branch->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Branch updated successfully!',
                'branch' => $branch->fresh(),
            ]);
        }

        return redirect()->back()->with('success', 'Branch updated successfully!');
    }

    /**
     * Toggle branch active status.
     */
    public function toggleActive(Request $request, School $school, $id)
    {
        $branch = Branch::where('school_id', $school->id)->findOrFail($id);

        $branch->update(['is_active' => !$branch->is_active]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $branch->is_active ? 'Branch activated.' : 'Branch deactivated.',
                'branch' => $branch->fresh(),
            ]);
        }

        return redirect()->back()->with('success', 'Branch status updated.');
    }

    /**
     * Delete a branch (only if no users are assigned).
     */
    public function destroy(Request $request, School $school, $id)
    {
        $branch = Branch::where('school_id', $school->id)->findOrFail($id);

        // Prevent deletion if students or instructors are assigned
        $studentCount = $branch->students()->count();
        $instructorCount = $branch->instructors()->count();

        if ($studentCount > 0 || $instructorCount > 0) {
            $msg = 'Cannot delete branch: ';
            $parts = [];
            if ($studentCount > 0) {
                $parts[] = "{$studentCount} student(s)";
            }
            if ($instructorCount > 0) {
                $parts[] = "{$instructorCount} instructor(s)";
            }
            $msg .= implode(' and ', $parts) . ' still assigned.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $branch->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Branch deleted successfully!',
            ]);
        }

        return redirect()->back()->with('success', 'Branch deleted successfully!');
    }
}
