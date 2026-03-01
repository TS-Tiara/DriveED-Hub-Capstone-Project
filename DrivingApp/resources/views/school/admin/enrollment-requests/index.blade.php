@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Manage Enrollments')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $settings->primary_color ?? '#667eea';
    use Illuminate\Support\Facades\Storage;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .enrollment-requests-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1600px;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid {{ $primaryColor }};
    }
    
    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    
    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .icon-14 {
        width: 14px;
        height: 14px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    /* Active selection state for stat cards */
    .stat-card.selected {
        border-left-color: {{ $primaryColor }};
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        transform: translateY(-3px);
    }
    
    /* Override stat card cursor for clickable cards */
    .stat-card[onclick] {
        cursor: pointer;
    }
    
    .requests-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
        margin-top: 20px;
    }
    
    .requests-table thead th {
        background: #f9fafb;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .requests-table tbody tr {
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .requests-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .requests-table tbody td {
        padding: 18px 15px;
        vertical-align: middle;
    }
    
    .requests-table tbody tr td:first-child {
        border-radius: 8px 0 0 8px;
    }
    
    .requests-table tbody tr td:last-child {
        border-radius: 0 8px 8px 0;
    }
    
    .learner-info {
        display: flex;
        flex-direction: column;
    }
    
    .learner-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 3px;
    }
    
    .learner-email {
        font-size: 0.85rem;
        color: #9ca3af;
    }

    .license-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 4px;
    }

    .license-none {
        background: #f3f4f6;
        color: #6b7280;
    }

    .license-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .license-verified {
        background: #d1fae5;
        color: #065f46;
    }

    .license-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .license-actions {
        display: flex;
        gap: 6px;
        margin-top: 4px;
    }

    .btn-license-verify {
        padding: 2px 8px;
        font-size: 0.7rem;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-license-reject {
        padding: 2px 8px;
        font-size: 0.7rem;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-license-view {
        padding: 2px 8px;
        font-size: 0.7rem;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
    }
    
    .course-info {
        display: flex;
        flex-direction: column;
    }
    
    .course-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 3px;
    }
    
    .course-type {
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-completed {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-cancelled {
        background: #f3f4f6;
        color: #374151;
    }
    
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .payment-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .payment-pending {
        background: #fef3c7;
        color: #78350f;
    }
    
    .payment-on_hold {
        background: #dbeafe;
        color: #1e3a8a;
    }
    
    .payment-paid {
        background: #d1fae5;
        color: #065f46;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, opacity 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }
    
    .btn-approve {
        background: #10b981;
        color: white;
    }
    
    .btn-reject {
        background: #ef4444;
        color: white;
    }
    
    .btn-view {
        background: #3b82f6;
        color: white;
    }
    
    .no-requests {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    
    .no-requests-icon {
        font-size: 4rem;
        margin-bottom: 15px;
    }
    
    .no-requests-text {
        font-size: 1.2rem;
    }
    
    .date-text {
        font-size: 0.9rem;
        color: #6b7280;
    }

    .inline-form {
        display: inline;
    }

    .contents-form {
        display: contents;
    }

    .status-muted {
        color: #9ca3af;
        font-size: 0.9rem;
    }

    .mobile-learner-name {
        font-size: 1rem;
    }

    .mobile-learner-email {
        color: #6b7280;
        font-size: 0.8rem;
    }

    .license-badge-inline {
        margin-top: 4px;
        display: inline-block;
    }

    .branch-scope-banner {
        padding: 12px 18px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .branch-scope-icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    .branch-scope-text {
        color: #1e40af;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .branch-filter-wrap {
        margin-bottom: 16px;
    }

    .branch-filter-select {
        max-width: 300px;
        display: inline-block;
        padding: 8px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .action-bar-wrapper {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        gap: 10px;
    }

    .bulk-actions-bar {
        display: none;
        gap: 10px;
        align-items: center;
    }

    .bulk-actions-count {
        font-weight: 600;
        color: #374151;
    }

    .bulk-action-btn {
        padding: 8px 16px;
    }

    .btn-icon-sm {
        width: 16px;
        height: 16px;
        display: inline;
    }

    .export-actions {
        display: flex;
        gap: 10px;
        margin-left: auto;
    }

    .export-menu-wrap {
        position: relative;
    }

    .export-btn {
        padding: 8px 16px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .export-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 5px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        min-width: 180px;
        z-index: 10;
    }

    .export-menu-link {
        display: block;
        padding: 10px 15px;
        text-decoration: none;
        color: #374151;
        transition: background 0.2s;
    }

    .export-menu-link:hover {
        background: #f3f4f6;
    }

    .export-menu-link.with-border {
        border-top: 1px solid #e5e7eb;
    }

    .checkbox-col {
        width: 40px;
    }

    .row-checkbox {
        cursor: pointer;
        width: 18px;
        height: 18px;
    }

    .empty-state-icon {
        width: 48px;
        height: 48px;
        color: #9ca3af;
    }

    .action-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .action-modal-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
    }

    .action-modal-title {
        margin: 0 0 20px 0;
        color: #333;
    }

    .action-modal-title-tight {
        margin: 0 0 8px 0;
        color: #333;
    }

    .action-modal-subtitle {
        margin: 0 0 20px 0;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .action-modal-field {
        margin-bottom: 20px;
    }

    .action-modal-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .action-modal-input {
        width: 100%;
        padding: 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
    }

    .action-modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .action-modal-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    }

    .action-modal-btn-secondary {
        background: #e5e7eb;
        color: #333;
    }

    .action-modal-btn-danger {
        background: #ef4444;
        color: white;
    }

    .action-modal-btn-neutral {
        background: #6b7280;
        color: white;
    }
    
    /* Mobile card layout */
    .mobile-card {
        display: none;
        background: white;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid {{ $primaryColor }};
    }
    
    .mobile-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    
    .mobile-card-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .mobile-card-row:last-child {
        border-bottom: none;
    }
    
    .mobile-card-label {
        color: #6b7280;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .mobile-card-value {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.85rem;
        text-align: right;
    }
    
    .mobile-card-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        flex-wrap: wrap;
    }
    
    .mobile-card-actions .btn {
        flex: 1;
        min-width: 100px;
        padding: 10px 12px;
        font-size: 0.85rem;
        text-align: center;
        min-height: 44px;
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .enrollment-requests-container {
            padding: 12px;
            margin: 10px auto;
        }
        
        .page-header {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }
        
        .page-title {
            font-size: 1.3rem;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .stat-value {
            font-size: 1.3rem;
        }
        
        .stat-label {
            font-size: 0.7rem;
        }
        
        /* Hide table, show cards */
        .table-container {
            display: none;
        }
        
        .mobile-card {
            display: block;
        }
        
        /* Action bar responsive */
        .action-bar-wrapper {
            flex-direction: column;
            gap: 10px;
        }
        
        .btn {
            min-height: 44px;
            padding: 10px 14px;
        }
    }
    
    @media (max-width: 480px) {
        .enrollment-requests-container {
            padding: 8px;
        }
        
        .page-title {
            font-size: 1.1rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        
        .mobile-card {
            padding: 12px;
        }
        
        .mobile-card-actions {
            flex-direction: column;
        }
        
        .mobile-card-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="enrollment-requests-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Manage Enrollments</h1>
            <p class="page-subtitle">View and manage all enrollment requests and active student enrollments</p>
        </div>
    </div>

    @if($admin->isBranchSecretary() && $admin->branch)
    <div class="branch-scope-banner">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#2563eb" class="branch-scope-icon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" /></svg>
        <span class="branch-scope-text">Showing enrollments for your branch: <strong>{{ $admin->branch->name }}</strong></span>
    </div>
    @endif

    @if($branches->count() > 0)
    <div class="mb-3 branch-filter-wrap">
        <select id="branchFilter" class="form-select branch-filter-select">
            <option value="">All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->name }}">{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="flash-message success">
        <div class="flash-icon">✓</div>
        <div class="flash-content">
            <div class="flash-title">Success!</div>
            <div class="flash-text">{{ session('success') }}</div>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif

    @if(session('error'))
    <div class="flash-message error">
        <div class="flash-icon">✕</div>
        <div class="flash-content">
            <div class="flash-title">Error!</div>
            <div class="flash-text">{{ session('error') }}</div>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif
    
    <div class="stats-grid">
        <div class="stat-card info" onclick="filterRequests('all', this)" data-status="all">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">All Enrollments</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card pending" onclick="filterRequests('pending', this)" data-status="pending">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending Approval</div>
                        <div class="stat-value">{{ $stats['pending'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card growth" onclick="filterRequests('approved', this)" data-status="approved">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active</div>
                        <div class="stat-value">{{ $stats['approved'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card active" onclick="filterRequests('completed', this)" data-status="completed">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Completed</div>
                        <div class="stat-value">{{ $stats['completed'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card inactive" onclick="filterRequests('cancelled', this)" data-status="cancelled">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Cancelled</div>
                        <div class="stat-value">{{ $stats['cancelled'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card danger" onclick="filterRequests('rejected', this)" data-status="rejected">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Rejected</div>
                        <div class="stat-value">{{ $stats['rejected'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Bar with Export and Bulk Operations -->
    <div class="action-bar-wrapper">
        <!-- Bulk Operations (Left Side) -->
        <div id="bulkActionsBar" class="bulk-actions-bar">
            <span id="selectedCount" class="bulk-actions-count">0 selected</span>
            <button type="button" class="btn btn-approve bulk-action-btn" onclick="bulkApprove()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="btn-icon-sm">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Approve Selected
            </button>
            <button type="button" class="btn btn-reject bulk-action-btn" onclick="bulkReject()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="btn-icon-sm">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reject Selected
            </button>
        </div>
        
        <!-- Export Buttons (Right Side) -->
        <div class="export-actions">
            <div class="export-menu-wrap">
                <button type="button" class="btn btn-primary export-btn" onclick="toggleExportMenu()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="btn-icon-sm">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    Export PDF
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="exportMenu" class="export-menu">
                    <a href="{{ route('schools.admin.exports.enrollments.pdf', ['school' => $school->slug]) }}" class="export-menu-link">
                        All Enrollments
                    </a>
                    <a href="{{ route('schools.admin.exports.enrollments.pdf', ['school' => $school->slug, 'status' => 'pending']) }}" class="export-menu-link with-border">
                        Pending Only
                    </a>
                    <a href="{{ route('schools.admin.exports.enrollments.pdf', ['school' => $school->slug, 'status' => 'approved']) }}" class="export-menu-link with-border">
                        Active Only
                    </a>
                    <a href="{{ route('schools.admin.exports.enrollments.pdf', ['school' => $school->slug, 'status' => 'completed']) }}" class="export-menu-link with-border">
                        Completed Only
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    @if($allRequests->count() > 0)
        <div class="table-container">
        <table class="requests-table">
            <thead>
                <tr>
                    <th class="checkbox-col">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="row-checkbox">
                    </th>
                    <th>Learner</th>
                    <th>Course</th>
                    <th>Branch</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allRequests as $request)
                    <tr data-status="{{ $request->status }}" data-request-id="{{ $request->id }}" data-branch="{{ $request->branchRelation?->name ?? '' }}">
                        <td>
                            @if($request->status === 'pending')
                                <input type="checkbox" class="request-checkbox row-checkbox" value="{{ $request->id }}" onchange="updateBulkActions()">
                            @endif
                        </td>
                        <td>
                            <div class="learner-info">
                                <div class="learner-name">{{ $request->learner->name }}</div>
                                <div class="learner-email">{{ $request->learner->email }}</div>
                                @php $licenseStatus = $request->learner->student_license_status ?? 'none'; @endphp
                                <div>
                                    <span class="license-badge license-{{ $licenseStatus }}">
                                        🪪 {{ $licenseStatus === 'none' ? 'No License' : ucfirst($licenseStatus) }}
                                    </span>
                                </div>
                                @if($licenseStatus === 'pending')
                                    <div class="license-actions">
                                        <a href="{{ Storage::url($request->learner->student_license_path) }}" target="_blank" class="btn-license-view">View</a>
                                        <form method="POST" action="{{ route('schools.admin.enrollments.verifyLicense', ['school' => $school, 'student' => $request->learner->id]) }}" class="inline-form">
                                            @csrf
                                            <button type="button" class="btn-license-verify" onclick="showConfirm({title:'Verify License',message:'Verify this student\'s license?',type:'success',onConfirm:()=>this.closest('form').submit()})">✓ Verify</button>
                                        </form>
                                        <button type="button" class="btn-license-reject" onclick="showLicenseRejectModal({{ $request->learner->id }}, '{{ addslashes($request->learner->name) }}')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> Reject</button>
                                    </div>
                                @elseif($licenseStatus === 'verified')
                                    @if($request->learner->student_license_path)
                                        <div class="license-actions">
                                            <a href="{{ Storage::url($request->learner->student_license_path) }}" target="_blank" class="btn-license-view">View License</a>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="course-info">
                                <div class="course-name">{{ $request->course->title ?? 'N/A' }}</div>
                                <div class="course-type">{{ ucfirst($request->course->type ?? 'standard') }}</div>
                            </div>
                        </td>
                        <td>{{ $request->branchRelation?->name ?? '—' }}</td>
                        <td>
                            <strong>₱{{ number_format($request->course->price ?? 0, 2) }}</strong>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $request->status }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="payment-badge payment-{{ $request->payment_status }}">
                                {{ ucfirst(str_replace('_', ' ', $request->payment_status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="date-text">
                                {{ $request->created_at->format('M d, Y') }}<br>
                                <small>{{ $request->created_at->format('h:i A') }}</small>
                            </div>
                        </td>
                        <td>
                            @if($request->status === 'pending')
                                <div class="action-buttons">
                                    <form method="POST" action="{{ route('schools.admin.enrollments.approve', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" class="inline-form" id="approveForm{{ $request->id }}">
                                        @csrf
                                        <button type="button" class="btn btn-approve" onclick="approveRequest({{ $request->id }})">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-reject" onclick="showRejectModal({{ $request->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> Reject
                                    </button>
                                </div>
                            @elseif($request->status === 'approved')
                                <div class="action-buttons">
                                    <form method="POST" action="{{ route('schools.admin.enrollments.complete', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" class="inline-form" id="completeForm{{ $request->id }}">
                                        @csrf
                                        <button type="button" class="btn btn-approve" onclick="completeEnrollment({{ $request->id }})">
                                            ✓ Complete
                                        </button>
                                    </form>
                                    <button class="btn btn-reject" onclick="showCancelModal({{ $request->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> Cancel
                                    </button>
                                </div>
                            @else
                                <span class="status-muted">
                                    {{ ucfirst($request->status) }}
                                    @if($request->approved_at)
                                        <br><small>{{ $request->approved_at->format('M d, Y') }}</small>
                                    @endif
                                    @if($request->completed_at)
                                        <br><small>{{ $request->completed_at->format('M d, Y') }}</small>
                                    @endif
                                    @if($request->cancelled_at)
                                        <br><small>{{ $request->cancelled_at->format('M d, Y') }}</small>
                                    @endif
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        
        {{-- Mobile card view --}}
        @foreach($allRequests as $request)
        <div class="mobile-card" data-status="{{ $request->status }}" data-request-id="{{ $request->id }}" data-branch="{{ $request->branchRelation?->name ?? '' }}">
            <div class="mobile-card-header">
                <div>
                    <strong class="mobile-learner-name">{{ $request->learner->name }}</strong>
                    <div class="mobile-learner-email">{{ $request->learner->email }}</div>
                    @php $licenseStatus = $request->learner->student_license_status ?? 'none'; @endphp
                    <span class="license-badge license-{{ $licenseStatus }} license-badge-inline">
                        🪪 {{ $licenseStatus === 'none' ? 'No License' : ucfirst($licenseStatus) }}
                    </span>
                </div>
                <span class="status-badge status-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Course</span>
                <span class="mobile-card-value">{{ $request->course->title ?? 'N/A' }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Branch</span>
                <span class="mobile-card-value">{{ $request->branchRelation?->name ?? '—' }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Fee</span>
                <span class="mobile-card-value">₱{{ number_format($request->course->price ?? 0, 2) }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Payment</span>
                <span class="payment-badge payment-{{ $request->payment_status }}">{{ ucfirst(str_replace('_', ' ', $request->payment_status)) }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Date</span>
                <span class="mobile-card-value">{{ $request->created_at->format('M d, Y h:i A') }}</span>
            </div>
            @if($request->status === 'pending')
                <div class="mobile-card-actions">
                    <form method="POST" action="{{ route('schools.admin.enrollments.approve', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" class="contents-form" id="mobileApproveForm{{ $request->id }}">
                        @csrf
                        <button type="button" class="btn btn-approve" onclick="document.getElementById('approveForm{{ $request->id }}').submit()">✓ Approve</button>
                    </form>
                    <button class="btn btn-reject" onclick="showRejectModal({{ $request->id }})"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> Reject</button>
                </div>
            @elseif($request->status === 'approved')
                <div class="mobile-card-actions">
                    <form method="POST" action="{{ route('schools.admin.enrollments.complete', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" class="contents-form" id="mobileCompleteForm{{ $request->id }}">
                        @csrf
                        <button type="button" class="btn btn-approve" onclick="completeEnrollment({{ $request->id }})">✓ Complete</button>
                    </form>
                    <button class="btn btn-reject" onclick="showCancelModal({{ $request->id }})"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> Cancel</button>
                </div>
            @endif
        </div>
        @endforeach
        
        <div class="mt-4">
            {{ $allRequests->links() }}
        </div>
    @else
        <div class="no-requests">
            <div class="no-requests-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="empty-state-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div class="no-requests-text">No enrollment requests yet</div>
        </div>
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="action-modal">
    <div class="action-modal-card">
        <h3 class="action-modal-title">Reject Enrollment Request</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="action-modal-field">
                <label for="remarks" class="action-modal-label">
                    Reason for Rejection *
                </label>
                <textarea id="remarks" name="remarks" rows="4" required
                    class="action-modal-input"
                    placeholder="Provide a reason for rejecting this enrollment request..."></textarea>
            </div>
            <div class="action-modal-actions">
                <button type="button" onclick="closeRejectModal()" class="action-modal-btn action-modal-btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="action-modal-btn action-modal-btn-danger">
                    Reject Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="action-modal">
    <div class="action-modal-card">
        <h3 class="action-modal-title">Cancel Enrollment</h3>
        <form id="cancelForm" method="POST">
            @csrf
            <div class="action-modal-field">
                <label for="cancel_remarks" class="action-modal-label">
                    Reason for Cancellation (optional)
                </label>
                <textarea id="cancel_remarks" name="remarks" rows="4"
                    class="action-modal-input"
                    placeholder="Provide a reason for cancelling this enrollment..."></textarea>
            </div>
            <div class="action-modal-actions">
                <button type="button" onclick="closeCancelModal()" class="action-modal-btn action-modal-btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="action-modal-btn action-modal-btn-neutral">
                    Cancel Enrollment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- License Reject Modal -->
<div id="licenseRejectModal" class="action-modal">
    <div class="action-modal-card">
        <h3 class="action-modal-title-tight">Reject Student License</h3>
        <p id="licenseRejectStudentName" class="action-modal-subtitle"></p>
        <form id="licenseRejectForm" method="POST">
            @csrf
            <div class="action-modal-field">
                <label for="rejection_reason" class="action-modal-label">
                    Reason for Rejection *
                </label>
                <textarea id="rejection_reason" name="rejection_reason" rows="4" required
                    class="action-modal-input"
                    placeholder="Explain why this license is being rejected (e.g., expired, unreadable, wrong document)..."></textarea>
            </div>
            <div class="action-modal-actions">
                <button type="button" onclick="closeLicenseRejectModal()" class="action-modal-btn action-modal-btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="action-modal-btn action-modal-btn-danger">
                    Reject License
                </button>
            </div>
        </form>
    </div>
</div>

<script>
var currentStatusFilter = 'all';
var currentBranchFilter = '';

function applyFilters() {
    const rows = document.querySelectorAll('.requests-table tbody tr');
    const mobileCards = document.querySelectorAll('.mobile-card');
    
    function shouldShow(el) {
        const statusMatch = currentStatusFilter === 'all' || el.dataset.status === currentStatusFilter;
        const branchMatch = currentBranchFilter === '' || el.dataset.branch === currentBranchFilter;
        return statusMatch && branchMatch;
    }
    
    rows.forEach(row => {
        row.style.display = shouldShow(row) ? '' : 'none';
    });
    mobileCards.forEach(card => {
        card.style.display = shouldShow(card) ? 'block' : 'none';
    });
}

function filterRequests(status, cardElement) {
    const cards = document.querySelectorAll('.stat-card');
    
    // Update active card
    cards.forEach(card => card.classList.remove('active'));
    cardElement.classList.add('active');
    
    currentStatusFilter = status;
    applyFilters();
}

// Branch filter
var branchSelect = document.getElementById('branchFilter');
if (branchSelect) {
    branchSelect.addEventListener('change', function() {
        currentBranchFilter = this.value;
        applyFilters();
    });
}

function showRejectModal(requestId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `{{ route('schools.admin.enrollments.reject', ['school' => $school, 'enrollmentRequest' => ':id']) }}`.replace(':id', requestId);
    modal.style.display = 'flex';
}

function showCancelModal(requestId) {
    const modal = document.getElementById('cancelModal');
    const form = document.getElementById('cancelForm');
    form.action = `{{ route('schools.admin.enrollments.cancel', ['school' => $school, 'enrollmentRequest' => ':id']) }}`.replace(':id', requestId);
    modal.style.display = 'flex';
}

function approveRequest(requestId) {
    showConfirm({
        type: 'success',
        title: 'Approve Enrollment',
        message: 'Are you sure you want to approve this enrollment request? This will promote the guest to a student.',
        confirmText: 'Approve',
        onConfirm: function() {
            document.getElementById('approveForm' + requestId).submit();
        }
    });
}

function completeEnrollment(requestId) {
    showConfirm({
        type: 'success',
        title: 'Complete Enrollment',
        message: 'Are you sure you want to mark this enrollment as completed? The student has finished the course.',
        confirmText: 'Complete',
        onConfirm: function() {
            document.getElementById('completeForm' + requestId).submit();
        }
    });
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.style.display = 'none';
    document.getElementById('remarks').value = '';
}

function closeCancelModal() {
    const modal = document.getElementById('cancelModal');
    modal.style.display = 'none';
    document.getElementById('cancel_remarks').value = '';
}

// Close modals when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});

// License Reject Modal
function showLicenseRejectModal(studentId, studentName) {
    const modal = document.getElementById('licenseRejectModal');
    const form = document.getElementById('licenseRejectForm');
    document.getElementById('licenseRejectStudentName').textContent = 'Student: ' + studentName;
    form.action = '{{ route('schools.admin.enrollments.rejectLicense', ['school' => $school, 'student' => '__STUDENT_ID__']) }}'.replace('__STUDENT_ID__', studentId);
    modal.style.display = 'flex';
}

function closeLicenseRejectModal() {
    const modal = document.getElementById('licenseRejectModal');
    modal.style.display = 'none';
    document.getElementById('rejection_reason').value = '';
}

document.getElementById('licenseRejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLicenseRejectModal();
    }
});

// Bulk Operations JavaScript
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.request-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.request-checkbox:checked');
    const bulkBar = document.getElementById('bulkActionsBar');
    const countSpan = document.getElementById('selectedCount');
    const selectAll = document.getElementById('selectAll');
    
    const count = checkboxes.length;
    countSpan.textContent = `${count} selected`;
    
    if (count > 0) {
        bulkBar.style.display = 'flex';
    } else {
        bulkBar.style.display = 'none';
    }
    
    // Update selectAll checkbox state
    const allCheckboxes = document.querySelectorAll('.request-checkbox');
    selectAll.checked = allCheckboxes.length > 0 && allCheckboxes.length === count;
}

function bulkApprove() {
    const checkboxes = document.querySelectorAll('.request-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        alert('Please select at least one enrollment request');
        return;
    }
    
    showConfirm({
        type: 'success',
        title: 'Bulk Approve Enrollments',
        message: `Are you sure you want to approve ${ids.length} enrollment request(s)? Selected guests will be promoted to students.`,
        confirmText: 'Approve All',
        onConfirm: function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('schools.admin.enrollments.bulkApprove', $school) }}';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'enrollment_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function bulkReject() {
    const checkboxes = document.querySelectorAll('.request-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        alert('Please select at least one enrollment request');
        return;
    }
    
    const remarks = prompt(`Enter rejection reason for ${ids.length} request(s):`);
    if (remarks === null) return; // User cancelled
    
    if (!remarks.trim()) {
        alert('Rejection reason is required');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('schools.admin.enrollments.bulkReject', $school) }}';
    
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);
    
    const remarksInput = document.createElement('input');
    remarksInput.type = 'hidden';
    remarksInput.name = 'remarks';
    remarksInput.value = remarks;
    form.appendChild(remarksInput);
    
    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'enrollment_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

// Export Menu Toggle
function toggleExportMenu() {
    const menu = document.getElementById('exportMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// Close export menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('exportMenu');
    const button = e.target.closest('button');
    if (menu && menu.style.display === 'block' && (!button || !button.onclick || button.onclick.toString().indexOf('toggleExportMenu') === -1)) {
        menu.style.display = 'none';
    }
});
</script>
@endsection
