@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Payments & Transactions')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .method-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.85rem;
        background: #e0e7ff;
        color: #3730a3;
    }
    
    .amount-cell {
        font-weight: 600;
        color: #059669;
    }
    
    .reference-cell {
        font-family: monospace;
        font-size: 0.85rem;
        color: #6b7280;
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Payments & Transactions</h1>
            <p class="page-subtitle">Track and manage all payments for {{ $schoolName }}</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₱{{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-label">Completed Payments</div>
            <div class="stat-value">{{ $payments->where('status', 'completed')->count() }}</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-label">Pending Payments</div>
            <div class="stat-value">{{ $payments->where('status', 'pending')->count() }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-group">
        <button class="filter-btn active" data-filter="all" onclick="filterPayments('all', this)">All Payments</button>
        <button class="filter-btn" data-filter="completed" onclick="filterPayments('completed', this)">Completed</button>
        <button class="filter-btn" data-filter="pending" onclick="filterPayments('pending', this)">Pending</button>
        <button class="filter-btn" data-filter="failed" onclick="filterPayments('failed', this)">Failed</button>
    </div>

    <!-- Payments Table -->
    <div class="content-card">
        <div style="overflow-x: auto;">
            <table class="admin-table" id="paymentsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr data-status="{{ $payment->status }}">
                        <td>{{ $payment->paid_on ? $payment->paid_on->format('M d, Y') : 'N/A' }}</td>
                        <td><strong>{{ $payment->booking->student->name ?? 'N/A' }}</strong></td>
                        <td>{{ $payment->booking->course->title ?? 'N/A' }}</td>
                        <td class="amount-cell">₱{{ number_format($payment->amount, 2) }}</td>
                        <td><span class="method-badge">{{ ucfirst($payment->method ?? 'N/A') }}</span></td>
                        <td class="reference-cell">{{ $payment->reference ?? '-' }}</td>
                        <td>
                            <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-title">No payments found</div>
                                <div class="empty-state-text">Payment records will appear here once transactions are made.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterPayments(status, btn) {
    const rows = document.querySelectorAll('#paymentsTable tbody tr[data-status]');
    const buttons = document.querySelectorAll('.filter-btn');
    
    buttons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    rows.forEach(row => {
        const rowStatus = row.dataset.status;
        if (status === 'all' || rowStatus === status) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endsection
