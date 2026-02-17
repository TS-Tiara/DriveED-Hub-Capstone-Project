@extends('layouts.system-admin')
@section('title', 'Payments')
@section('page-title', 'All Payments')
@section('content')
<div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 24px;">
    <div class="stat-card">
        <h3>Total Paid</h3>
        <div class="value" style="color: #059669;">₱{{ number_format($totalPaid, 2) }}</div>
    </div>
    <div class="stat-card">
        <h3>Pending Payments</h3>
        <div class="value" style="color: #f59e0b;">₱{{ number_format($totalPending, 2) }}</div>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3>Payments ({{ $payments->total() }})</h3></div>
    <div class="card-body">
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Method</th>
                    <th>School</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ $payment->booking->student->name ?? 'N/A' }}</td>
                    <td><strong>₱{{ number_format($payment->amount, 2) }}</strong></td>
                    <td>
                        <span class="badge {{ $payment->status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                    <td>{{ $payment->school->name }}</td>
                    <td>{{ $payment->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        {{ $payments->links() }}
    </div>
</div>
@endsection
