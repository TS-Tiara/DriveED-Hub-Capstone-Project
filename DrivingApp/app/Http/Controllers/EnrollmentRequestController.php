<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnrollmentRequest;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnrollmentRequestController extends Controller
{
    /**
     * Display all enrollment requests (Admin)
     */
    public function index(School $school)
    {
        $requests = EnrollmentRequest::where('school_id', $school->id)
            ->with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('school.admin.enrollment-requests.index', compact('school', 'requests'));
    }

    /**
     * Approve an enrollment request - changes guest to student and creates enrollment
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

        // Security Check 4: Verify the student still exists and is a guest
        if (!$enrollmentRequest->student) {
            return redirect()
                ->back()
                ->with('error', 'Student not found.');
        }

        if ($enrollmentRequest->student->role !== 'guest') {
            return redirect()
                ->back()
                ->with('error', 'This user is already a student.');
        }

        DB::beginTransaction();
        try {
            // Update enrollment status and set enrolled_at
            $enrollmentRequest->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'enrolled_at' => now(),
            ]);

            // Update student role from guest to student
            $enrollmentRequest->student->update(['role' => 'student']);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Student account activated! Role changed from Guest to Student.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to approve enrollment: ' . $e->getMessage());
        }
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

    /**
     * Complete an enrollment (mark as finished)
     */
    public function complete(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        // Security checks
        if ($enrollmentRequest->school_id !== $school->id) {
            abort(404);
        }

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($enrollmentRequest->status !== 'approved') {
            return redirect()
                ->back()
                ->with('error', 'Only active enrollments can be marked as completed.');
        }

        $enrollmentRequest->complete($admin->id);

        return redirect()
            ->back()
            ->with('success', 'Enrollment marked as completed successfully.');
    }

    /**
     * Cancel an enrollment
     */
    public function cancel(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        // Security checks
        if ($enrollmentRequest->school_id !== $school->id) {
            abort(404);
        }

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($enrollmentRequest->status, ['pending', 'approved'])) {
            return redirect()
                ->back()
                ->with('error', 'Only pending or active enrollments can be cancelled.');
        }

        $enrollmentRequest->cancel($validated['remarks'] ?? null);

        return redirect()
            ->back()
            ->with('success', 'Enrollment cancelled successfully.');
    }

    /**
     * Mark theoretical portion as passed
     */
    public function markTheoreticalPassed(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Security checks
        if ($enrollmentRequest->school_id !== $school->id) {
            abort(404);
        }

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($enrollmentRequest->status !== 'approved') {
            return redirect()
                ->back()
                ->with('error', 'Only active enrollments can have theoretical status updated.');
        }

        $enrollmentRequest->markTheoreticalPassed($admin->id, $validated['notes'] ?? null);

        return redirect()
            ->back()
            ->with('success', 'Theoretical portion marked as passed.');
    }
}
