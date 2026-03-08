@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Payments')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
@endphp

<style>
.payments-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 4px solid {{ $primaryColor }};
}

.page-title {
    font-size: 2rem;
    color: #111827;
    margin: 0;
    font-weight: 400;
}

.total-spent {
    @if($settings->use_gradient_header ?? true)
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
    @else
        background: {{ $primaryColor }};
    @endif
    color: white;
    border-radius: 12px;
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
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

/* Mobile card styles for payments */
.payment-card {
    display: none;
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    border-left: 4px solid {{ $primaryColor }};
}

.payment-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.payment-card-row:last-child {
    margin-bottom: 0;
}

.payment-card-label {
    color: #6b7280;
    font-size: 0.8rem;
    font-weight: 500;
}

.payment-card-value {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.9rem;
}

.payment-card-amount {
    font-size: 1.1rem;
    color: #10b981;
    font-weight: 700;
}

.total-spent-label {
    font-size: 1.2rem;
    opacity: 0.9;
}

.amount-emphasis {
    color: #10b981;
}

.payments-pagination {
    padding: 15px 20px;
    display: flex;
    justify-content: center;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .payments-container {
        padding: 15px;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .total-spent {
        padding: 20px;
    }
    
    .total-spent h2 {
        font-size: 2rem;
    }
    
    .total-spent p {
        font-size: 1rem !important;
    }
    
    /* Hide table, show cards on mobile */
    .payments-table table {
        display: none;
    }
    
    .payment-card {
        display: block;
    }
}

@media (max-width: 480px) {
    .payments-container {
        padding: 10px;
    }
    
    .page-title {
        font-size: 1.25rem;
    }
    
    .total-spent {
        padding: 15px;
        border-radius: 10px;
    }
    
    .total-spent h2 {
        font-size: 1.75rem;
    }
    
    .total-spent p {
        font-size: 0.9rem !important;
    }
    
    .payment-card {
        padding: 12px;
    }
    
    .badge {
        padding: 4px 10px;
        font-size: 0.7rem;
    }
}

/* Compact Pagination Styling */
nav[role="navigation"] {
    display: flex;
    justify-content: flex-end;
    align-items: center;
}

nav[role="navigation"] > div {
    display: flex;
    align-items: center;
    gap: 4px;
}

nav[role="navigation"] > div:first-child {
    display: none;
}

nav[role="navigation"] span[aria-current="page"] span,
nav[role="navigation"] a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.15s ease;
}

nav[role="navigation"] span[aria-current="page"] span {
    background: {{ $primaryColor }};
    color: white;
}

nav[role="navigation"] a {
    background: #f3f4f6;
    color: #374151;
}

nav[role="navigation"] a:hover {
    background: #e5e7eb;
    color: #1f2937;
}

nav[role="navigation"] svg {
    width: 14px !important;
    height: 14px !important;
}

nav[role="navigation"] span[aria-disabled="true"] {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    background: #f9fafb;
    color: #d1d5db;
    border-radius: 6px;
    cursor: not-allowed;
}

nav[role="navigation"] span[aria-disabled="true"] svg {
    width: 14px !important;
    height: 14px !important;
}

nav[role="navigation"] span:not([aria-current]):not([aria-disabled]) {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    font-size: 0.85rem;
    color: #6b7280;
}

@media (min-width: 640px) {
    nav[role="navigation"] > div:first-child {
        display: block;
        font-size: 0.82rem;
        color: #6b7280;
        margin-right: 16px;
    }
}
</style>

<div class="payments-container">
    <div class="page-header">
        <h1 class="page-title">My Payments</h1>
    </div>

    <div class="total-spent">
        <p class="total-spent-label">Total Amount Paid</p>
        <h2>&#8369;{{ number_format($totalPaid ?? $payments->where('status', 'completed')->sum('amount'), 2) }}</h2>
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
                    <td><strong>{{ $payment->booking?->course?->title ?? 'N/A' }}</strong></td>
                    <td><strong class="amount-emphasis">&#8369;{{ number_format($payment->amount, 2) }}</strong></td>
                    <td>{{ ucfirst($payment->method ?? 'N/A') }}</td>
                    <td><span class="badge badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <p class="empty-state-title">No payment records found</p>
                            <p class="empty-state-text">Payment history will appear here once payments are made</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Mobile card view --}}
        @forelse($payments as $payment)
        <div class="payment-card">
            <div class="payment-card-row">
                <span class="payment-card-label">Date</span>
                <span class="payment-card-value">{{ $payment->paid_on ? $payment->paid_on->format('M d, Y') : 'N/A' }}</span>
            </div>
            <div class="payment-card-row">
                <span class="payment-card-label">Course</span>
                <span class="payment-card-value">{{ $payment->booking?->course?->title ?? 'N/A' }}</span>
            </div>
            <div class="payment-card-row">
                <span class="payment-card-label">Amount</span>
                <span class="payment-card-amount">&#8369;{{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="payment-card-row">
                <span class="payment-card-label">Method</span>
                <span class="payment-card-value">{{ ucfirst($payment->method ?? 'N/A') }}</span>
            </div>
            <div class="payment-card-row">
                <span class="payment-card-label">Status</span>
                <span class="badge badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="empty-state-title">No payment records found</p>
            <p class="empty-state-text">Payment history will appear here once payments are made</p>
        </div>
        @endforelse

        @if($payments->hasPages())
        <div class="payments-pagination">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
