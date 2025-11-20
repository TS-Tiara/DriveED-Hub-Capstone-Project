<?php

namespace App\Http\Controllers;

use App\Models\Booking;
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
        $query = Payment::where('school_id', $school->id)
            ->with(['booking.student', 'booking.course']);

        // Filter by role
        if (Auth::guard('student')->check()) {
            $studentId = Auth::guard('student')->id();
            $query->whereHas('booking', function($q) use ($studentId) {
                $q->where('student_id', $studentId);
            });
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('method')) {
            $query->where('method', request('method'));
        }

        $payments = $query->latest('paid_on')->get();

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'payments' => $payments
            ]);
        }

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = "{$guard}.payments";
        return view($school->resolveView($view), compact('school', 'payments'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(School $school, Request $request)
    {
        $bookingId = $request->query('booking_id');
        $booking = null;

        if ($bookingId) {
            $booking = Booking::where('school_id', $school->id)
                ->where('id', $bookingId)
                ->with(['student', 'course'])
                ->first();
        }

        $bookings = Booking::where('school_id', $school->id)
            ->whereDoesntHave('payment')
            ->with(['student', 'course'])
            ->get();

        return view($school->resolveView('admin.payment-create'), compact('school', 'bookings', 'booking'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'nullable|in:cash,card,bank_transfer,online',
            'reference' => 'nullable|string|max:120',
            'paid_on' => 'nullable|date',
            'status' => 'nullable|in:pending,completed,failed,refunded',
        ]);

        $validated['school_id'] = $school->id;
        $validated['paid_on'] = $validated['paid_on'] ?? now();
        $validated['status'] = $validated['status'] ?? 'completed';

        $payment = Payment::create($validated);
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
        $payment->load(['booking.student', 'booking.course', 'booking.instructor']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'payment' => $payment
            ]);
        }

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = "{$guard}.payment-show";
        return view($school->resolveView($view), compact('school', 'payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(School $school, Payment $payment)
    {
        return view($school->resolveView('admin.payment-edit'), compact('school', 'payment'));
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, School $school, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'method' => 'nullable|in:cash,card,bank_transfer,online',
            'reference' => 'nullable|string|max:120',
            'paid_on' => 'nullable|date',
            'status' => 'nullable|in:pending,completed,failed,refunded',
        ]);

        $payment->update($validated);
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
        $stats = [
            'total_revenue' => Payment::where('school_id', $school->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_payments' => Payment::where('school_id', $school->id)
                ->where('status', 'pending')
                ->count(),
            'total_payments' => Payment::where('school_id', $school->id)
                ->count(),
            'by_method' => Payment::where('school_id', $school->id)
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
