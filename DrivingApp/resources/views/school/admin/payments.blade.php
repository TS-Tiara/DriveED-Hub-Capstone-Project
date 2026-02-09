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
    
    /* Export Buttons */
    .export-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .btn-export {
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    
    .btn-export-pdf {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .btn-export-pdf:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        color: white;
    }
    
    .btn-export-excel {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .btn-export-excel:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        color: white;
    }
    
    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .admin-container {
            padding: 15px;
            margin: 10px auto;
        }
        
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .page-title {
            font-size: 1.4rem;
        }
        
        .page-subtitle {
            font-size: 0.85rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .stat-card {
            padding: 18px;
        }
        
        .stat-value {
            font-size: 1.6rem;
        }
        
        .filter-group {
            flex-direction: column;
        }
        
        .filter-btn {
            width: 100%;
            text-align: center;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 10px 8px;
            font-size: 0.85rem;
        }
        
        /* Hide less important columns on mobile */
        .admin-table th:nth-child(5),
        .admin-table td:nth-child(5),
        .admin-table th:nth-child(6),
        .admin-table td:nth-child(6) {
            display: none;
        }
        
        .method-badge {
            padding: 3px 6px;
            font-size: 0.75rem;
        }
        
        .amount-cell {
            font-size: 0.9rem;
        }
        
        .export-buttons {
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .admin-container {
            padding: 10px;
            margin: 5px auto;
        }
        
        .page-title {
            font-size: 1.2rem;
        }
        
        .stat-card {
            padding: 14px;
        }
        
        .stat-value {
            font-size: 1.4rem;
        }
        
        .stat-label {
            font-size: 0.75rem;
        }
        
        .filter-btn {
            padding: 8px 12px;
            font-size: 0.85rem;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 8px 6px;
            font-size: 0.8rem;
        }
        
        /* Hide more columns on very small screens */
        .admin-table th:nth-child(3),
        .admin-table td:nth-child(3) {
            display: none;
        }
        
        .btn-export {
            padding: 8px 12px;
            font-size: 0.8rem;
        }
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Payments & Transactions</h1>
            <p class="page-subtitle">Track and manage all payments for {{ $schoolName }}</p>
        </div>
        <div class="export-buttons">
            <a href="{{ $schoolRoute('admin.exports.payments.pdf') }}" class="btn-export btn-export-pdf">
                Export PDF
            </a>
            <a href="{{ $schoolRoute('admin.exports.payments.excel') }}" class="btn-export btn-export-excel">
                Export Excel
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">₱{{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card growth">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Completed Payments</div>
                        <div class="stat-value">{{ $payments->where('status', 'completed')->count() }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card inactive">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending Payments</div>
                        <div class="stat-value">{{ $payments->where('status', 'pending')->count() }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
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
