<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnrollmentRequest;
use App\Models\School;
use Illuminate\Support\Facades\Auth;

class EnrollmentRequestController extends Controller
{
    /**
     * Display all enrollment requests (Admin)
     */
    public function index(School $school)
    {
        $requests = EnrollmentRequest::where('school_id', $school->id)
            ->with(['learner', 'course'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('school.admin.enrollment-requests.index', compact('school', 'requests'));
    }

    /**
     * Approve an enrollment request - changes guest to student
     */
    public function approve(School $school, EnrollmentRequest $enrollmentRequest)
    {
        // Security Check 1: Verify the request belongs to this school
        if ($enrollmentRequest->school_id !== $school->id) {
            abort(404);
        }

        // Security Check 2: Verify admin is authenticated and belongs to this school
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Security Check 3: Prevent duplicate approvals
        if ($enrollmentRequest->status === 'approved') {
            return redirect()
                ->back()
                ->with('error', 'This enrollment request has already been approved.');
        }

        // Security Check 4: Verify the learner still exists and is a guest
        if (!$enrollmentRequest->learner) {
            return redirect()
                ->back()
                ->with('error', 'Student not found.');
        }

        if ($enrollmentRequest->learner->role !== 'guest') {
            return redirect()
                ->back()
                ->with('error', 'This user is already a student.');
        }

        // Update enrollment status
        $enrollmentRequest->update([
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Update student role from guest to student (just like activating/deactivating)
        $enrollmentRequest->learner->update(['role' => 'student']);

        return redirect()
            ->back()
            ->with('success', 'Student account activated! Role changed from Guest to Student.');
    }

    /**
     * Reject an enrollment request - keeps user as guest
     */
    public function reject(Request $req, School $school, EnrollmentRequest $enrollmentRequest)
    {
        $validated = $req->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        // Security Check 1: Verify the request belongs to this school
        if ($enrollmentRequest->school_id !== $school->id) {
            abort(404);
        }

        // Security Check 2: Verify admin is authenticated and belongs to this school
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Security Check 3: Prevent duplicate rejections
        if ($enrollmentRequest->status === 'rejected') {
            return redirect()
                ->back()
                ->with('error', 'This enrollment request has already been rejected.');
        }

        // Security Check 4: Don't allow rejecting already approved requests
        if ($enrollmentRequest->status === 'approved') {
            return redirect()
                ->back()
                ->with('error', 'Cannot reject an already approved enrollment request.');
        }

        // Simply update status and add remarks
        $enrollmentRequest->update([
            'status' => 'rejected',
            'remarks' => $validated['remarks'],
        ]);

        return redirect()
            ->back()
            ->with('success', 'Enrollment request rejected. User remains as Guest.');
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        $validated = $request->validate([
            'payment_status' => ['required', 'in:pending,on_hold,paid'],
        ]);

        // Security Check 1: Verify the request belongs to this school
        if ($enrollmentRequest->school_id !== $school->id) {
            abort(404);
        }

        // Security Check 2: Verify admin is authenticated and belongs to this school
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Security Check 3: Only allow payment updates for approved enrollments
        if ($enrollmentRequest->status !== 'approved') {
            return redirect()
                ->back()
                ->with('error', 'Payment status can only be updated for approved enrollments.');
        }

        $enrollmentRequest->update([
            'payment_status' => $validated['payment_status'],
        ]);

        return redirect()
            ->back()
            ->with('success', 'Payment status updated successfully.');
    }
}
