<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnrollmentRequest;
use App\Models\PhaseProgression;
use App\Models\School;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Mail\EnrollmentApproved;
use App\Mail\EnrollmentRejected;
use App\Models\Branch;
use App\Models\Notification;
use App\Models\SystemLog;

class EnrollmentRequestController extends Controller
{
    /**
     * Display all enrollment requests (Admin)
     */
    public function index(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();

        $baseQuery = EnrollmentRequest::where('school_id', $school->id)
            ->with(['learner', 'course', 'approvedBy', 'branchRelation'])
            ->orderBy('created_at', 'desc');

        $admin->scopeToBranch($baseQuery);

        $allRequests = (clone $baseQuery)->paginate(20)->withQueryString();

        $allRequestsCount = (clone $baseQuery)->count();
        $pendingRequestsCount = (clone $baseQuery)->where('status', 'pending')->count();
        $approvedRequestsCount = (clone $baseQuery)->where('status', 'approved')->count();
        $completedRequestsCount = (clone $baseQuery)->where('status', 'completed')->count();
        $cancelledRequestsCount = (clone $baseQuery)->where('status', 'cancelled')->count();
        $rejectedRequestsCount = (clone $baseQuery)->where('status', 'rejected')->count();

        $stats = [
            'total' => $allRequests->count(),
            'pending' => $pendingRequests->count(),
            'approved' => $approvedRequests->count(),
            'completed' => $completedRequests->count(),
            'cancelled' => $cancelledRequests->count(),
            'rejected' => $rejectedRequests->count(),
        ];

        $branches = Branch::where('school_id', $school->id)->where('is_active', true)->orderBy('name')->get();

        $isAjax = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';

        return view('school.admin.enrollment-requests.index', compact(
<<<<<<< HEAD
            'school', 'allRequests', 'allRequestsCount',
            'pendingRequestsCount', 'approvedRequestsCount',
            'completedRequestsCount', 'cancelledRequestsCount', 'rejectedRequestsCount',
            'admin', 'branches'
=======
            'school', 'allRequests', 'pendingRequests', 'approvedRequests',
            'completedRequests', 'cancelledRequests', 'rejectedRequests',
            'admin', 'branches', 'isAjax', 'stats'
>>>>>>> deploy-testing
        ));
    }

    /**
     * Display detailed enrollment information (Admin)
     */
    public function show(School $school, EnrollmentRequest $enrollmentRequest)
    {
        // Security: verify belongs to this school
        abort_if($enrollmentRequest->school_id !== $school->id, 404);

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Branch secretary access check
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($enrollmentRequest->branch_id)) {
            abort(403, 'You do not have access to this enrollment request.');
        }

        $enrollmentRequest->load(['learner', 'course', 'approvedBy', 'sessionCompletions.instructor', 'payments', 'bookings']);

        // Calculate session hours summary
        $sessionSummary = [
            'total_sessions' => $enrollmentRequest->sessionCompletions->count(),
            'total_hours' => $enrollmentRequest->sessionCompletions->where('status', 'completed')->sum('hours_completed'),
            'theoretical_sessions' => $enrollmentRequest->sessionCompletions->where('session_type', 'theoretical')->count(),
            'practical_sessions' => $enrollmentRequest->sessionCompletions->where('session_type', 'practical')->count(),
            'hours_required' => $enrollmentRequest->course->hours_required ?? 0,
        ];

        $sessionSummary['progress_percentage'] = $sessionSummary['hours_required'] > 0
            ? min(100, round(($sessionSummary['total_hours'] / $sessionSummary['hours_required']) * 100, 1))
            : 0;

        // Get phase progressions for this enrollment
        $phaseProgressions = PhaseProgression::where('enrollment_id', $enrollmentRequest->id)
            ->with('reviewedBy')
            ->latest('requested_at')
            ->get();

        return view('school.admin.enrollment-requests.show', compact(
            'school', 'enrollmentRequest', 'sessionSummary', 'phaseProgressions'
        ));
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

        // Security Check: Branch secretary permission and access
        if (!$admin->canApproveEnrollments()) {
            abort(403, 'You do not have permission to approve enrollments.');
        }
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($enrollmentRequest->branch_id)) {
            abort(403, 'You do not have access to this enrollment request.');
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

        // Security Check 5: Verify student is not locked to another active course
        if ($enrollmentRequest->student->is_course_locked) {
            return redirect()
                ->back()
                ->with('error', 'This student is already enrolled in an active course. They must complete or cancel their current enrollment first.');
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

            // Lock student to this course (prevents concurrent enrollments)
            $enrollmentRequest->student->update(['is_course_locked' => true]);

            // Send approval email notification
            try {
                Mail::to($enrollmentRequest->learner->email)
                    ->send(new EnrollmentApproved($enrollmentRequest, $school));
            } catch (\Exception $e) {
                Log::warning('Failed to send enrollment approval email: ' . $e->getMessage());
            }

            // Create in-app notification for the student
            Notification::send(
                $enrollmentRequest->student,
                'enrollment_approved',
                'Enrollment Approved!',
                "Your enrollment for {$enrollmentRequest->course->title} has been approved. Welcome aboard!",
                'success',
                "/{$school->slug}/student"
            );

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Student account activated! Role changed from Guest to Student. Notification email sent.');
                
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

        // Security Check: Branch secretary permission and access
        if (!$admin->canApproveEnrollments()) {
            abort(403, 'You do not have permission to reject enrollments.');
        }
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($enrollmentRequest->branch_id)) {
            abort(403, 'You do not have access to this enrollment request.');
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

        // Send rejection email
        try {
            Mail::to($enrollmentRequest->learner->email)
                ->send(new EnrollmentRejected($enrollmentRequest, $school));
        } catch (\Exception $e) {
            Log::warning('Failed to send enrollment rejection email: ' . $e->getMessage());
        }

        // Create in-app notification for the guest
        Notification::send(
            $enrollmentRequest->student,
            'enrollment_rejected',
            'Enrollment Request Update',
            "Your enrollment request for {$enrollmentRequest->course->title} was not approved. Check your email for details.",
            'warning',
            "/{$school->slug}/guest/enrollment-requests"
        );

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
            'payment_notes' => ['nullable', 'string', 'max:1000'],
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

        // Security Check: Payment confirmation permission and branch access
        if (!$admin->canConfirmPayments()) {
            abort(403, 'You do not have permission to update payment status.');
        }
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($enrollmentRequest->branch_id)) {
            abort(403, 'You do not have access to this enrollment request.');
        }

        // Security Check 3: Only allow payment updates for approved enrollments
        if ($enrollmentRequest->status !== 'approved') {
            return redirect()
                ->back()
                ->with('error', 'Payment status can only be updated for approved enrollments.');
        }

        $oldPaymentStatus = $enrollmentRequest->payment_status;

        $updateData = [
            'payment_status' => $validated['payment_status'],
        ];

        // Set payment confirmation fields when marking as paid
        if ($validated['payment_status'] === 'paid') {
            $updateData['payment_confirmed_by'] = $admin->id;
            $updateData['payment_confirmed_at'] = now();
            $updateData['payment_confirmation_notes'] = $request->input('payment_notes');
        }

        $enrollmentRequest->update($updateData);

        // Log the payment status update
        SystemLog::logInfo(
            "Payment status updated to '{$validated['payment_status']}' for enrollment #{$enrollmentRequest->id}",
            'payment',
            [
                'enrollment_id' => $enrollmentRequest->id,
                'branch_id' => $enrollmentRequest->branch_id,
                'student_name' => $enrollmentRequest->learner?->name,
                'old_status' => $oldPaymentStatus,
                'new_status' => $validated['payment_status'],
                'confirmed_by_name' => $admin->name,
                'confirmed_by_role' => $admin->role,
                'notes' => $request->input('payment_notes'),
            ],
            $school->id,
            'payment_status_updated'
        );

        // Notify student about payment status change
        if ($enrollmentRequest->student) {
            try {
                Notification::send(
                    $enrollmentRequest->student,
                    'payment_status_updated',
                    'Payment Status Updated',
                    "Your payment status for {$enrollmentRequest->course->title} has been updated to: {$validated['payment_status']}.",
                    'payment',
                    "/{$school->slug}/student"
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send payment status notification: ' . $e->getMessage());
            }
        }

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

        // Unlock student from course so they can enroll in a new one
        if ($enrollmentRequest->student) {
            $enrollmentRequest->student->update(['is_course_locked' => false]);

            // Notify student about enrollment completion
            try {
                Notification::send(
                    $enrollmentRequest->student,
                    'enrollment_completed',
                    'Course Completed!',
                    "Congratulations! You have successfully completed {$enrollmentRequest->course->title}.",
                    'success',
                    "/{$school->slug}/student/my-progress"
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send enrollment completion notification: ' . $e->getMessage());
            }
        }

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

        $wasApproved = $enrollmentRequest->status === 'approved';
        $enrollmentRequest->cancel($validated['remarks'] ?? null);

        // Unlock student from course if they had an active enrollment
        if ($wasApproved && $enrollmentRequest->student) {
            $enrollmentRequest->student->update(['is_course_locked' => false]);
        }

        // Notify student about cancellation
        if ($enrollmentRequest->student) {
            try {
                $reason = $validated['remarks'] ? " Reason: {$validated['remarks']}" : '';
                Notification::send(
                    $enrollmentRequest->student,
                    'enrollment_cancelled',
                    'Enrollment Cancelled',
                    "Your enrollment for {$enrollmentRequest->course->title} has been cancelled.{$reason}",
                    'warning',
                    "/{$school->slug}/student"
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send cancellation notification: ' . $e->getMessage());
            }
        }

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

        // Notify student about theoretical completion
        if ($enrollmentRequest->student) {
            try {
                Notification::send(
                    $enrollmentRequest->student,
                    'theoretical_passed',
                    'Theoretical Exam Passed!',
                    "You have passed the theoretical portion for {$enrollmentRequest->course->title}. You may now proceed to practical training.",
                    'success',
                    "/{$school->slug}/student/my-course"
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send theoretical passed notification: ' . $e->getMessage());
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Theoretical portion marked as passed.');
    }

    /**
     * Bulk approve enrollment requests
     */
    public function bulkApprove(Request $request, School $school)
    {
        $request->validate([
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollment_requests,id',
        ]);

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        $approved = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->enrollment_ids as $id) {
                $enrollment = EnrollmentRequest::find($id);

                // Skip if not belonging to this school
                if ($enrollment->school_id !== $school->id) {
                    continue;
                }

                // Skip if already approved
                if ($enrollment->status === 'approved') {
                    continue;
                }

                // Skip if student is already active
                if ($enrollment->student->role !== 'guest') {
                    continue;
                }

                // Skip if student is locked to another course
                if ($enrollment->student->is_course_locked) {
                    continue;
                }

                $enrollment->update([
                    'status' => 'approved',
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                    'enrolled_at' => now(),
                ]);

                $enrollment->student->update(['role' => 'student']);

                // Lock student to this course
                $enrollment->student->update(['is_course_locked' => true]);

                // Send email notification
                try {
                    Mail::to($enrollment->learner->email)
                        ->send(new EnrollmentApproved($enrollment, $school));
                } catch (\Exception $e) {
                    Log::warning('Failed to send bulk approval email: ' . $e->getMessage());
                }

                // Create in-app notification
                Notification::send(
                    $enrollment->student,
                    'enrollment_approved',
                    'Enrollment Approved!',
                    "Your enrollment for {$enrollment->course->title} has been approved. Welcome aboard!",
                    'success',
                    "/{$school->slug}/student"
                );

                $approved++;
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', "Successfully approved {$approved} enrollment request(s).");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Bulk approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk reject enrollment requests
     */
    public function bulkReject(Request $request, School $school)
    {
        $request->validate([
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollment_requests,id',
            'remarks' => 'required|string|max:1000',
        ]);

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        $rejected = 0;

        DB::beginTransaction();
        try {
            foreach ($request->enrollment_ids as $id) {
                $enrollment = EnrollmentRequest::find($id);

                // Skip if not belonging to this school
                if ($enrollment->school_id !== $school->id) {
                    continue;
                }

                // Skip if already rejected or approved
                if (in_array($enrollment->status, ['rejected', 'approved'])) {
                    continue;
                }

                $enrollment->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'remarks' => $request->remarks,
                ]);

                // Send rejection email
                try {
                    Mail::to($enrollment->learner->email)
                        ->send(new EnrollmentRejected($enrollment, $school));
                } catch (\Exception $e) {
                    Log::warning('Failed to send bulk rejection email: ' . $e->getMessage());
                }

                // Create in-app notification
                Notification::send(
                    $enrollment->student,
                    'enrollment_rejected',
                    'Enrollment Request Update',
                    "Your enrollment request for {$enrollment->course->title} was not approved.",
                    'warning',
                    "/{$school->slug}/guest/enrollment-requests"
                );

                $rejected++;
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', "Successfully rejected {$rejected} enrollment request(s).");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Bulk rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify a student's driver license
     */
    public function verifyLicense(Request $request, School $school, Student $student)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Ensure the student belongs to this school
        if ($student->school_id !== $school->id) {
            abort(403, 'Student does not belong to this school.');
        }

        if ($student->student_license_status !== 'pending') {
            return redirect()->back()->with('warning', 'This license is not pending verification.');
        }

        $student->update([
            'student_license_status' => 'verified',
            'student_license_verified_at' => now(),
            'student_license_verified_by' => $admin->id,
            'student_license_rejection_reason' => null,
        ]);

        // Notify the student
        Notification::send(
            $student,
            'license_verified',
            'License Verified!',
            "Your student driver's license has been verified. You can now enroll in Practical Driving Courses (PDC).",
            'success',
            "/{$school->slug}/guest/courses"
        );

        return redirect()->back()->with('success', "Student driver's license for {$student->name} has been verified.");
    }

    /**
     * Reject a student's driver license
     */
    public function rejectLicense(Request $request, School $school, Student $student)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($student->school_id !== $school->id) {
            abort(403, 'Student does not belong to this school.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($student->student_license_status !== 'pending') {
            return redirect()->back()->with('warning', 'This license is not pending verification.');
        }

        $student->update([
            'student_license_status' => 'rejected',
            'student_license_rejection_reason' => $request->rejection_reason,
            'student_license_verified_at' => null,
            'student_license_verified_by' => null,
        ]);

        // Notify the student
        Notification::send(
            $student,
            'license_rejected',
            'License Not Approved',
            "Your student driver's license was not approved: {$request->rejection_reason}",
            'warning',
            "/{$school->slug}/guest/dashboard"
        );

        return redirect()->back()->with('success', "Student driver's license for {$student->name} has been rejected.");
    }

    /**
     * View a student's uploaded driver license file
     */
    public function viewLicense(Request $request, School $school, Student $student)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($student->school_id !== $school->id) {
            abort(403, 'Student does not belong to this school.');
        }

        if (!empty($student->student_license_data)) {
            $decodedData = base64_decode($student->student_license_data, true);

            if ($decodedData !== false) {
                $licenseFilename = $student->student_license_filename ?: "student-license-{$student->id}";
                $safeFilename = str_replace(["\r", "\n", '"'], '', basename($licenseFilename));

                return response($decodedData, 200, [
                    'Content-Type' => $student->student_license_mime_type ?: 'application/octet-stream',
                    'Content-Disposition' => 'inline; filename="' . $safeFilename . '"',
                ]);
            }

            Log::warning('Invalid base64 student license data', [
                'school_id' => $school->id,
                'student_id' => $student->id,
            ]);
        }

        $storedPath = trim((string) $student->student_license_path);

        if ($storedPath === '') {
            abort(404, 'License file not found.');
        }

        $pathFromUrl = parse_url($storedPath, PHP_URL_PATH);
        $normalizedPath = ltrim($pathFromUrl ?: $storedPath, '/');
        $fileName = basename($normalizedPath);

        $candidates = array_values(array_filter(array_unique([
            $storedPath,
            $normalizedPath,
            Str::after($normalizedPath, 'storage/'),
            Str::after($normalizedPath, 'public/'),
            $fileName ? 'student-licenses/' . $fileName : null,
        ])));

        foreach (['public', 'local'] as $disk) {
            foreach ($candidates as $candidatePath) {
                if (Storage::disk($disk)->exists($candidatePath)) {
                    return Storage::disk($disk)->response($candidatePath);
                }
            }
        }

        Log::warning('Student license file not found during admin view', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'stored_path' => $storedPath,
            'candidate_paths' => $candidates,
        ]);

        abort(404, 'License file not found.');
    }
}
