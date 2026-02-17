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
</style>

<div class="payments-container">
    <div class="page-header">
        <h1 class="page-title">My Payments</h1>
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
                    <td><strong>{{ $payment->booking?->course?->title ?? 'N/A' }}</strong></td>
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
                <span class="payment-card-amount">₱{{ number_format($payment->amount, 2) }}</span>
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
        <div class="payment-card" style="text-align: center; color: #9ca3af;">
            <p style="font-size: 1rem;">No payment records found</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
