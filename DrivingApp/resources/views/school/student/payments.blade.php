@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Payments')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
@endphp

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: white;
    padding: 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px;
}

.page-header h1 {
    font-size: 2rem;
    color: #1f2937;
    margin-bottom: 30px;
}

.total-spent {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    text-align: center;
}

.total-spent h2 {
    font-size: 3rem;
    margin: 10px 0;
}

.payments-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #f9fafb;
}

th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #374151;
}

td {
    padding: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-completed { background: #d1fae5; color: #065f46; }
.badge-pending { background: #fef3c7; color: #92400e; }
</style>

<div class="container">
    <div class="page-header">
        <h1>💰 My Payments - {{ $schoolName }}</h1>
    </div>

    <div class="total-spent">
        <p style="font-size: 1.2rem; opacity: 0.9;">Total Amount Paid</p>
        <h2>₱{{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}</h2>
    </div>

    <div class="payments-table">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Course</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->paid_on ? $payment->paid_on->format('M d, Y') : 'N/A' }}</td>
                    <td><strong>{{ $payment->booking->course->title }}</strong></td>
                    <td><strong style="color: #10b981;">₱{{ number_format($payment->amount, 2) }}</strong></td>
                    <td>{{ ucfirst($payment->method ?? 'N/A') }}</td>
                    <td><span class="badge badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 60px 20px; color: #9ca3af;">
                        <p style="font-size: 1.2rem;">No payment records found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
