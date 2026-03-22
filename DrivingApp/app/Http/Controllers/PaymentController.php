<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(School $school)
    {
        // Require authentication
        if (!Auth::guard('admin')->check() && !Auth::guard('student')->check()) {
            abort(403, 'Unauthorized access.');
        }

        $query = Payment::where('school_id', '=', $school->id)
            ->with(['booking.student', 'booking.course']);

        // Filter by role - students can only see their own payments
        if (Auth::guard('student')->check()) {
            $studentId = Auth::guard('student')->id();
            $query->where(function ($q) use ($studentId) {
                $q->whereHas('booking', function ($inner) use ($studentId) {
                    $inner->where('student_id', '=', $studentId);
                })->orWhereHas('enrollmentRequest', function ($inner) use ($studentId) {
                    $inner->where('learner_id', '=', $studentId);
                });
            });
        }
        elseif (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            if ($admin->isBranchSecretary() && $admin->branch_id) {
                // Use the new branch_id field for reliable scoping across all payment types
                $query->where('branch_id', '=', $admin->branch_id);
            }
        }

        if (request('status')) {
            $query->where('status', '=', request('status'));
        }

        if (request('method')) {
            $query->where('method', '=', request('method'));
        }

        // Pre-compute statistics for display (before pagination)
        $stats = [
            'total_revenue' => (clone $query)->where('status', 'completed')->sum('amount'),
            'completed_count' => (clone $query)->where('status', 'completed')->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
        ];

        $payments = $query->latest('paid_on')->paginate(10);

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'payments' => $payments
            ]);
        }

        // Only admin and student have payment views
        $guard = Auth::guard('admin')->check() ? 'admin' : 'student';
        $view = "{$guard}.payments";
        return view($school->resolveView($view), compact('school', 'payments', 'stats'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request, School $school)
    {
        // Only admins can record payments, or students recording their own
        $isAdmin = Auth::guard('admin')->check();
        $studentId = Auth::guard('student')->id();

        if (!$isAdmin && !$studentId) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',
            'enrollment_request_id' => 'nullable|exists:enrollment_requests,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'nullable|in:cash,card,bank_transfer,online',
            'reference' => 'nullable|string|max:120',
            'paid_on' => 'nullable|date',
            'status' => 'nullable|in:pending,completed,failed,refunded',
        ]);

        // Security: Ensure at least one linkage is provided
        if (empty($validated['booking_id']) && empty($validated['enrollment_request_id'])) {
            return response()->json(['success' => false, 'message' => 'Payment must be linked to a booking or enrollment request.'], 422);
        }

        // Determine branch attribution
        $branchId = null;
        if (!empty($validated['booking_id'])) {
            $booking = \App\Models\Booking::findOrFail($validated['booking_id']);
            if (!$isAdmin && $booking->student_id !== $studentId) {
                abort(403, 'You can only record payments for your own bookings.');
            }
            $branchId = $booking->branch_id;
        } elseif (!empty($validated['enrollment_request_id'])) {
            $enrollment = \App\Models\EnrollmentRequest::findOrFail($validated['enrollment_request_id']);
            if (!$isAdmin && $enrollment->learner_id !== $studentId) {
                abort(403, 'You can only record payments for your own enrollments.');
            }
            $branchId = $enrollment->branch_id;
        }

        // Security: Only admins can mark payments as completed/failed immediately
        $status = $validated['status'] ?? 'pending';
        if (!$isAdmin) {
            $status = 'pending';
        }

        $payment = new Payment([
            'school_id' => $school->id,
            'branch_id' => $branchId,
            'booking_id' => $validated['booking_id'] ?? null,
            'enrollment_request_id' => $validated['enrollment_request_id'] ?? null,
            'amount' => $validated['amount'],
            'method' => $validated['method'] ?? 'cash',
            'reference' => $validated['reference'] ?? null,
            'paid_on' => $validated['paid_on'] ?? now(),
        ]);
        $payment->status = $status;
        $payment->save();

        $payment->load(['booking.student', 'booking.course']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'payment' => $payment
            ], 201);
        }

        return redirect()->route('payments.show', [$school->slug, $payment->id])
            ->with('success', 'Payment recorded successfully');
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
            if ($admin->isBranchSecretary() && $admin->branch_id) {
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

    /**
     * Update the specified payment.
     */
    public function update(Request $request, School $school, Payment $payment)
    {
        abort_if($payment->school_id !== $school->id, 404);

        // Only admins can update payments
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Only administrators can update payment records.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'method' => 'nullable|in:cash,card,bank_transfer,online',
            'reference' => 'nullable|string|max:120',
            'paid_on' => 'nullable|date',
            'status' => 'nullable|in:pending,completed,failed,refunded',
        ]);

        $payment->fill($validated);
        if (isset($validated['status'])) {
            $payment->status = $validated['status'];
        }
        $payment->save();

        $payment->load(['booking.student', 'booking.course']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully',
                'payment' => $payment
            ]);
        }

        return redirect()->route('payments.show', [$school->slug, $payment->id])
            ->with('success', 'Payment updated successfully');
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Request $request, School $school, Payment $payment)
    {
        abort_if($payment->school_id !== $school->id, 404);

        // Security: Branch secretary restriction
        $admin = Auth::guard('admin')->user();
        if ($admin->isBranchSecretary() && $admin->branch_id) {
            // Securely check branch access using payment-level branch_id
            if ($payment->branch_id && (int)$payment->branch_id !== (int)$admin->branch_id) {
                abort(403, 'You do not have access to payments from this branch.');
            }
        }

        // Warning: Deleting a completed payment affects financial reports
        if ($payment->status === 'completed' && !$admin->isSchoolAdmin()) {
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
        if (!$admin) {
            abort(403);
        }

        $stats = [
            'total_revenue' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_payments' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->where('status', 'pending')
                ->count(),
            'total_payments' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->count(),
            'by_method' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->where('status', 'completed')
                ->selectRaw('method, SUM(amount) as total')
                ->groupBy('method')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }
}
