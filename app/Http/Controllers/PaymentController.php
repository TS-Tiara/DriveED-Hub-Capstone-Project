<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\GCashSetting;
use App\Models\Payment;
use App\Models\School;
use App\Services\PaymentSubmissionService;
use App\Services\ReceiptStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of payments.
     */
    public function index(Request $request, School $school, ReceiptStorageService $storageService)
    {
        // Require authentication
        if (!Auth::guard('admin')->check() && !Auth::guard('student')->check()) {
            abort(403, 'Unauthorized access.');
        }

        $query = Payment::where('school_id', '=', $school->id)
            ->with(['payer', 'booking.course', 'enrollmentRequest.course']);

        // Filter by role - students can only see their own payments
        if (Auth::guard('student')->check()) {
            $studentId = Auth::guard('student')->id();
            $query->where('payer_user_id', $studentId);
        }
        elseif (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            if ($admin instanceof Admin && $admin->isBranchSecretary() && $admin->branch_id) {
                // Use the new branch_id field for reliable scoping across all payment types
                $query->where('branch_id', '=', $admin->branch_id);
            }
        }

        if (request('enrollment_id')) {
            $query->where('enrollment_request_id', '=', request('enrollment_id'));
        }

        if (request('status')) {
            $query->where('status', '=', request('status'));
        }

        if (request('method')) {
            $query->where('method', '=', request('method'));
        }

        $payments = $query->latest('paid_on')->paginate(10);

        // Append temporary URLs for GCash receipts
        $payments->getCollection()->transform(function ($payment) use ($storageService) {
            if ($payment->method === 'gcash' && $payment->proof_of_payment_path) {
                $payment->proof_url = $storageService->getUrl($payment->proof_of_payment_path);
            }
            return $payment;
        });

        // Pre-compute statistics for display
        $stats = [
            'total_revenue' => (clone $query)->where('status', 'approved')->sum('amount'),
            'approved_count' => (clone $query)->where('status', 'approved')->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
        ];

        $pendingEnrollments = collect();
        $pendingBookings = collect();
        $gcashPaymentImageUrl = null;

        if (Auth::guard('student')->check()) {
            $studentId = Auth::guard('student')->id();
            $student = Auth::guard('student')->user();

            $activeGcashSetting = GCashSetting::getActiveSetting($school->id, $student?->branch_id);
            if ($activeGcashSetting && $activeGcashSetting->is_active && !empty($activeGcashSetting->qr_path)) {
                $gcashPaymentImageUrl = route('schools.guest.storage.gcash-qr', [
                    'school' => $school,
                    'gcashSetting' => $activeGcashSetting,
                ]);
            }

            $pendingEnrollments = \App\Models\EnrollmentRequest::where('learner_id', $studentId)
                ->whereIn('payment_status', ['pending', 'partial', 'on_hold'])
                ->with('course')
                ->get();

            $pendingBookings = \App\Models\Booking::where('student_id', $studentId)
                ->whereIn('payment_status', ['pending', 'partial', 'on_hold'])
                ->with('course')
                ->get();
        }

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'payments' => $payments
            ]);
        }

        // Only admin and student have payment views
        $guard = Auth::guard('admin')->check() ? 'admin' : 'student';
        $view = "school.{$guard}.payments";
        return view($view, array_merge(
            compact('school', 'payments', 'stats', 'pendingEnrollments', 'pendingBookings', 'gcashPaymentImageUrl'),
            ['isAjax' => $request->ajax()]
        ));
    }

    /**
     * Store a newly created payment (GCash or On-site).
     */
    public function store(Request $request, School $school, PaymentSubmissionService $submissionService, ReceiptStorageService $storageService)
    {
        $isAdmin = Auth::guard('admin')->check();
        $student = Auth::guard('student')->user();
        $studentId = $student?->id;

        $validated = $request->validate([
            'method' => 'required|in:gcash,on_site',
            'amount' => 'required|numeric|min:1',
            'booking_id' => 'nullable|exists:bookings,id',
            'enrollment_request_id' => 'nullable|exists:enrollment_requests,id',
            
            // GCash fields
            'reference' => 'required_if:method,gcash|nullable|string|max:120',
            'proof_of_payment' => 'required_if:method,gcash|nullable|image|max:5120',
            
            // On-site fields
            'or_number' => 'required_if:method,on_site|nullable|string|max:120',
        ]);

        // Forensic XOR Linkage check
        if (empty($validated['booking_id']) && empty($validated['enrollment_request_id'])) {
            return response()->json(['success' => false, 'message' => 'Payment must be linked to a booking or enrollment.'], 422);
        }

        $data = $validated;
        $data['school_id'] = $school->id;
        $data['payer_user_id'] = $studentId;

        // Determine branch and check ownership
        if (!empty($validated['booking_id'])) {
            $booking = \App\Models\Booking::findOrFail($validated['booking_id']);
            if (!$isAdmin && (int)$booking->student_id !== (int)$studentId) abort(403);
            $data['branch_id'] = $booking->branch_id;
        } else {
            $enrollment = \App\Models\EnrollmentRequest::findOrFail($validated['enrollment_request_id']);
            if (!$isAdmin && (int)$enrollment->learner_id !== (int)$studentId) abort(403);
            $data['branch_id'] = $enrollment->branch_id;
        }

        if ($validated['method'] === 'gcash') {
            // Store receipt securely
            $data['proof_of_payment_path'] = $storageService->store($request->file('proof_of_payment'), $school->id);
            $payment = $submissionService->submitGcash($data);
        } else {
            // On-site usually recorded by Admin
            if (!$isAdmin) abort(403, 'Students cannot record on-site payments.');
            $data['received_by_admin_id'] = Auth::guard('admin')->id();
            $data['received_at'] = now();
            $payment = $submissionService->submitOnsite($data);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Payment submitted for verification.', 'payment' => $payment], 201);
        }

        return redirect()->back()->with('success', 'Payment submitted successfully.');
    }

    /**
     * Display the specified payment.
     */
    public function show(School $school, Payment $payment)
    {
        abort_if($payment->school_id !== $school->id, 404);

        // Security check
        if (Auth::guard('student')->check()) {
            $studentId = Auth::guard('student')->id();
            $payment->load(['booking', 'enrollmentRequest']);
            
            $isOwner = false;
            if ($payment->booking && (int)$payment->booking->student_id === (int)$studentId) {
                $isOwner = true;
            } elseif ($payment->enrollmentRequest && (int)$payment->enrollmentRequest->learner_id === (int)$studentId) {
                $isOwner = true;
            }

            if (!$isOwner) {
                abort(403, 'You do not have access to this payment record.');
            }
        }
        elseif (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            if ($admin instanceof Admin && $admin->isBranchSecretary() && $admin->branch_id) {
                // Securely check branch access using payment-level branch_id
                if ($payment->branch_id && (int)$payment->branch_id !== (int)$admin->branch_id) {
                    abort(403, 'You do not have access to payments from this branch.');
                }
            }
        }
        else {
            abort(403);
        }

        $payment->load(['booking.student', 'booking.course', 'booking.instructor']);

        // Always return JSON - payment details shown in modals/lists
        return response()->json([
            'success' => true,
            'payment' => $payment
        ]);
    }

    // update() method removed — Payment module is read-only.
    // All acceptance/rejection is handled exclusively by the Enrollment module.

    /**
     * Remove the specified payment.
     */
    public function destroy(Request $request, School $school, Payment $payment)
    {
        abort_if($payment->school_id !== $school->id, 404);

        // Security: Branch secretary restriction
        $admin = Auth::guard('admin')->user();
        if (!$admin instanceof Admin) {
            abort(403, 'Unauthorized admin action.');
        }
        if ($admin->isBranchSecretary() && $admin->branch_id) {
            // Securely check branch access using payment-level branch_id
            if ($payment->branch_id && (int)$payment->branch_id !== (int)$admin->branch_id) {
                abort(403, 'You do not have access to payments from this branch.');
            }
        }

        // Warning: Deleting a completed payment affects financial reports
        if ($payment->status === 'approved' && !$admin->isSchoolAdmin()) {
            abort(403, 'Only school administrators can delete completed payments.');
        }

        $payment->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully'
            ]);
        }

        return redirect()->route('payments.index', $school->slug)
            ->with('success', 'Payment deleted successfully');
    }

    /**
     * Get payment statistics.
     */
    public function statistics(School $school)
    {
        $admin = Auth::guard('admin')->user();
        // Only admins can view general statistics
        if (!$admin instanceof Admin) {
            abort(403);
        }

        $stats = [
            'total_revenue' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->where('status', '=', 'approved')
                ->sum('amount') - $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->where('status', '=', 'refunded')
                ->sum('refunded_amount'),
            'pending_payments' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->where('status', '=', 'pending')
                ->count(),
            'total_payments' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->count(),
            'by_method' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->where('status', 'approved')
                ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('method')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }
}
