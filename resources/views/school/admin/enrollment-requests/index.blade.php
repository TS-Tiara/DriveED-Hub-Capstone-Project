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
    .stat-card.active {
        border-left: 4px solid {{ $primaryColor }} !important;
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

    .export-actions {
        display: flex;
        gap: 10px;
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

    /* Unified Verification Modal Layout */
    .verification-modal-card {
        background: white;
        border-radius: 12px;
        width: min(1200px, 98vw);
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    
    .v-modal-header {
        padding: 16px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .v-modal-body {
        display: flex;
        overflow-y: auto;
        padding: 0;
        flex: 1;
    }
    
    .v-modal-sidebar {
        width: 300px;
        background: #fff;
        border-right: 1px solid #e2e8f0;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .v-modal-content {
        flex: 1;
        background: #f1f5f9;
        display: flex;
        gap: 2px;
    }
    
    .v-image-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #1e293b;
        position: relative;
    }
    
    .v-panel-title {
        background: rgba(0,0,0,0.5);
        color: white;
        padding: 8px 16px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .v-image-viewer {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        padding: 40px 20px;
    }
    
    .v-image-viewer img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        border: 2px solid #475569;
    }

    .v-info-label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 4px;
    }
    
    .v-info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        word-break: break-all;
    }
    
    .v-panel-status {
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 700;
    }

    .v-btn-group {
        display: flex;
        gap: 10px;
        margin-top: auto;
        padding: 20px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .v-empty-image {
        color: #64748b;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
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
    @if($branches->count() > 0)
    <div class="mb-3 branch-filter-wrap">
        <select id="branchFilter" class="form-select branch-filter-select" onchange="window.location.href = '{{ school_route('admin.enrollments.index') }}?branch=' + encodeURIComponent(this.value) + '&status={{ request('status', 'all') }}'">
            <option value="">All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->name }}" {{ request('branch') === $branch->name ? 'selected' : '' }}>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    @endif
    </div>
    @endif


    
    <div class="stats-grid">
        <div class="stat-card info {{ request('status', 'all') === 'all' ? 'active' : '' }}" onclick="window.location.href='{{ school_route('admin.enrollments.index', ['status' => 'all', 'branch' => request('branch')]) }}'">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">All Enrollments</div>
                        <div class="stat-value">{{ $allRequestsCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card pending {{ request('status') === 'pending' ? 'active' : '' }}" onclick="window.location.href='{{ school_route('admin.enrollments.index', ['status' => 'pending', 'branch' => request('branch')]) }}'">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending Approval</div>
                        <div class="stat-value">{{ $pendingRequestsCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card growth {{ request('status') === 'approved' ? 'active' : '' }}" onclick="window.location.href='{{ school_route('admin.enrollments.index', ['status' => 'approved', 'branch' => request('branch')]) }}'">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active</div>
                        <div class="stat-value">{{ $approvedRequestsCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card active {{ request('status') === 'completed' ? 'active' : '' }}" onclick="window.location.href='{{ school_route('admin.enrollments.index', ['status' => 'completed', 'branch' => request('branch')]) }}'">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Completed</div>
                        <div class="stat-value">{{ $completedRequestsCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card inactive {{ request('status') === 'cancelled' ? 'active' : '' }}" onclick="window.location.href='{{ school_route('admin.enrollments.index', ['status' => 'cancelled', 'branch' => request('branch')]) }}'">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Cancelled</div>
                        <div class="stat-value">{{ $cancelledRequestsCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="stat-card danger {{ request('status') === 'rejected' ? 'active' : '' }}" onclick="window.location.href='{{ school_route('admin.enrollments.index', ['status' => 'rejected', 'branch' => request('branch')]) }}'">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Rejected</div>
                        <div class="stat-value">{{ $rejectedRequestsCount }}</div>
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
                    <th>Learner</th>
                    <th>Experience</th>
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
                            <div class="learner-info">
                                <div class="learner-name" style="cursor: pointer; color: {{ $primaryColor }};" onclick="openVerificationModal({{ $request->id }})">
                                    {{ $request->learner->name }}
                                </div>
                                <div class="learner-email">{{ $request->learner->email }}</div>
                                @php $licenseStatus = $request->learner->student_license_status ?? 'none'; @endphp
                                <div>
                                    <span class="license-badge license-{{ $licenseStatus }}">
                                        License: {{ $licenseStatus === 'none' ? 'No License' : ucfirst($licenseStatus) }}
                                    </span>
                                </div>
                                @if($licenseStatus === 'pending')
                                    <div class="license-actions">
                                        <button type="button" class="btn-license-view" onclick="showLicensePreviewModal('{{ route('schools.admin.enrollments.viewLicense', ['school' => $school, 'student' => $request->learner->id]) }}', '{{ addslashes($request->learner->name) }}')">View</button>
                                        <form method="POST" action="{{ route('schools.admin.enrollments.verifyLicense', ['school' => $school, 'student' => $request->learner->id]) }}" style="display:inline;">
                                            @csrf
                                            <button type="button" class="btn-license-verify" onclick="showConfirm({title:'Verify License',message:'Verify this student\'s license?',type:'success',onConfirm:()=>this.closest('form').submit()})">&#10003; Verify</button>
                                        </form>
                                        <button type="button" class="btn-license-reject" onclick="showLicenseRejectModal({{ $request->learner->id }}, '{{ addslashes($request->learner->name) }}')">&#10005; Reject</button>
                                    </div>
                                @elseif($licenseStatus === 'verified')
                                    @if($request->learner->student_license_path || $request->learner->student_license_data)
                                        <div class="license-actions">
                                            <button type="button" class="btn-license-view" onclick="showLicensePreviewModal('{{ route('schools.admin.enrollments.viewLicense', ['school' => $school, 'student' => $request->learner->id]) }}', '{{ addslashes($request->learner->name) }}')">View License</button>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($request->experience_level === 'experienced')
                                <span class="badge bg-primary" title="Experienced Driver">Experienced</span>
                            @else
                                <span class="badge bg-secondary" title="New Driver">New Driver</span>
                            @endif
                        </td>
                        <td>
                            <div class="course-info">
                                <div class="course-name">{{ $request->course->title ?: 'N/A' }}</div>
                                <div class="course-type">{{ ucfirst($request->course->type ?? 'standard') }}</div>
                            </div>
                        </td>
                        <td>{{ $request->branchRelation?->name ?: '—' }}</td>
                        <td>
                            <strong>&#8369;{{ number_format($request->course->price ?? 0, 2) }}</strong>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $request->status }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ school_route('admin.payments.index', ['enrollment_id' => $request->id]) }}" 
                               class="payment-badge payment-{{ $request->payment_status }} hover:opacity-80 transition-opacity"
                               @if($request->payment_status === 'pending_verification') 
                               onclick="openVerificationModal({{ $request->id }}); return false;" 
                               @endif>
                                {{ ucfirst(str_replace('_', ' ', $request->payment_status)) }}
                            </a>
                        </td>
                        <td>
                            <div class="date-text">
                                {{ $request->created_at->timezone($school->timezone ?? 'Asia/Manila')->format('M d, Y') }}<br>
                                <small>{{ $request->created_at->timezone($school->timezone ?? 'Asia/Manila')->format('h:i A') }}</small>
                            </div>
                        </td>
                        <td>
                            @if($request->status === 'pending')
                                <div class="action-buttons">
                                    <form method="POST" action="{{ route('schools.admin.enrollments.approve', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" class="inline-form" id="approveForm{{ $request->id }}">
                                        @csrf
                                        <button type="button" class="btn btn-approve" onclick="approveRequest({{ $request->id }})">
                                            &#10003; Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-reject" onclick="showRejectModal({{ $request->id }})">
                                        &#10005; Reject
                                    </button>
                                </div>
                            @elseif($request->status === 'approved')
                                <div class="action-buttons">
                                    <form method="POST" action="{{ route('schools.admin.enrollments.complete', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" class="inline-form" id="completeForm{{ $request->id }}">
                                        @csrf
                                        <button type="button" class="btn btn-approve" onclick="completeEnrollment({{ $request->id }})">
                                            &#10003; Complete
                                        </button>
                                    </form>
                                    <button class="btn btn-reject" onclick="showCancelModal({{ $request->id }})">
                                        &#10005; Cancel
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
                    <strong class="mobile-learner-name" style="cursor: pointer; color: {{ $primaryColor }};" onclick="openVerificationModal({{ $request->id }})">
                        {{ $request->learner->name }}
                    </strong>
                    <div class="mobile-learner-email">{{ $request->learner->email }}</div>
                    @php $licenseStatus = $request->learner->student_license_status ?? 'none'; @endphp
                    <span class="license-badge license-{{ $licenseStatus }} license-badge-inline">
                        License: {{ $licenseStatus === 'none' ? 'No License' : ucfirst($licenseStatus) }}
                    </span>
                </div>
                <span class="status-badge status-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Course</span>
                <span class="mobile-card-value">{{ $request->course->title ?: 'N/A' }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Branch</span>
                <span class="mobile-card-value">{{ $request->branchRelation?->name ?: '—' }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Fee</span>
                <span class="mobile-card-value">&#8369;{{ number_format($request->course->price ?? 0, 2) }}</span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Payment</span>
                <span class="payment-badge payment-{{ $request->payment_status }}"
                      @if($request->payment_status === 'pending_verification') 
                      onclick="openVerificationModal({{ $request->id }})" 
                      @endif>
                    {{ ucfirst(str_replace('_', ' ', $request->payment_status)) }}
                </span>
            </div>
            <div class="mobile-card-row">
                <span class="mobile-card-label">Date</span>
                <span class="mobile-card-value">{{ $request->created_at->timezone($school->timezone ?? 'Asia/Manila')->format('M d, Y h:i A') }}</span>
            </div>
            @if($request->status === 'pending')
                <div class="mobile-card-actions">
                    <form method="POST" action="{{ route('schools.admin.enrollments.approve', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" class="contents-form" id="mobileApproveForm{{ $request->id }}">
                        @csrf
                        <button type="button" class="btn btn-approve" onclick="document.getElementById('approveForm{{ $request->id }}').submit()">&#10003; Approve</button>
                    </form>
                    <button class="btn btn-reject" onclick="showRejectModal({{ $request->id }})">&#10005; Reject</button>
                </div>
            @elseif($request->status === 'approved')
                <div class="mobile-card-actions">
                    <form method="POST" action="{{ route('schools.admin.enrollments.complete', ['school' => $school, 'enrollmentRequest' => $request->id]) }}" class="contents-form" id="mobileCompleteForm{{ $request->id }}">
                        @csrf
                        <button type="button" class="btn btn-approve" onclick="completeEnrollment({{ $request->id }})">&#10003; Complete</button>
                    </form>
                    <button class="btn btn-reject" onclick="showCancelModal({{ $request->id }})">&#10005; Cancel</button>
                </div>
            @endif
        </div>
        @endforeach

        @if($allRequests->hasPages())
            <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
                <div style="color: #6b7280; font-size: 0.9rem;">
                    Showing {{ $allRequests->firstItem() ?? 0 }} to {{ $allRequests->lastItem() ?? 0 }} of {{ $allRequests->total() }} results
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    @if($allRequests->onFirstPage())
                        <span style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; color: #9ca3af; background: #f9fafb;">Previous</span>
                    @else
                        <a href="{{ $allRequests->previousPageUrl() }}" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; color: #374151; text-decoration: none; background: white;">Previous</a>
                    @endif

                    <span style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; color: #374151; background: #f9fafb;">
                        Page {{ $allRequests->currentPage() }} of {{ $allRequests->lastPage() }}
                    </span>

                    @if($allRequests->hasMorePages())
                        <a href="{{ $allRequests->nextPageUrl() }}" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; color: #374151; text-decoration: none; background: white;">Next</a>
                    @else
                        <span style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; color: #9ca3af; background: #f9fafb;">Next</span>
                    @endif
                </div>
            </div>
        @endif
        
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

<!-- License Preview Modal -->
<div id="licensePreviewModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 1001; justify-content: center; align-items: center; padding: 16px;">
    <div style="background: white; border-radius: 12px; width: min(1100px, 96vw); height: min(88vh, 900px); display: flex; flex-direction: column; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">
            <div>
                <h3 style="margin: 0; color: #111827; font-size: 1rem;">Student License Preview</h3>
                <p id="licensePreviewStudent" style="margin: 2px 0 0 0; color: #6b7280; font-size: 0.85rem;"></p>
            </div>
            <button type="button" onclick="closeLicensePreviewModal()" style="border: none; background: transparent; font-size: 1.5rem; line-height: 1; color: #6b7280; cursor: pointer;">&times;</button>
        </div>
        <div style="flex: 1; background: #f3f4f6;">
            <iframe id="licensePreviewFrame" src="" title="License Preview" style="width: 100%; height: 100%; border: 0;"></iframe>
        </div>
    </div>
</div>

<script>
// Server-side filtering is now used.
// JS applyFilters and filterRequests functions removed.

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
        message: 'Are you sure you want to approve this enrollment request? Note: The student role will be promoted automatically once their first payment is verified.',
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

function showLicensePreviewModal(url, studentName) {
    const modal = document.getElementById('licensePreviewModal');
    const frame = document.getElementById('licensePreviewFrame');
    const student = document.getElementById('licensePreviewStudent');
    student.textContent = 'Student: ' + studentName;
    frame.src = url;
    modal.style.display = 'flex';
}

function closeLicensePreviewModal() {
    const modal = document.getElementById('licensePreviewModal');
    const frame = document.getElementById('licensePreviewFrame');
    modal.style.display = 'none';
    frame.src = '';
}

document.getElementById('licenseRejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLicenseRejectModal();
    }
});

document.getElementById('licensePreviewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLicensePreviewModal();
    }
});


// Prevent double-submit on modal forms (reject, cancel, license reject)
['rejectForm', 'cancelForm', 'licenseRejectForm'].forEach(function(formId) {
    var formEl = document.getElementById(formId);
    if (formEl) {
        formEl.addEventListener('submit', function(e) {
            var submitBtn = formEl.querySelector('button[type="submit"]');
            if (submitBtn.disabled) { e.preventDefault(); return; }
            submitBtn.disabled = true;
        });
    }
});

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

/**
 * Unified Verification Modal Logic
 */
let currentEnrollmentId = null;

function openVerificationModal(enrollmentId) {
    currentEnrollmentId = enrollmentId;
    const modal = document.getElementById('unifiedVerificationModal');
    modal.style.display = 'flex';
    
    // Show loading state
    document.getElementById('v-modal-loading').style.display = 'flex';
    document.getElementById('v-modal-content').style.visibility = 'hidden';
    
    fetch(`{{ school_route('admin.enrollments.index') }}/api/${enrollmentId}`)
        .then(res => res.json())
        .then(data => {
            // Update Info Sidebar
            document.getElementById('v-student-name').textContent = data.student_name;
            document.getElementById('v-course-title').textContent = data.course_title;
            document.getElementById('v-price').textContent = '₱' + data.total_price;
            document.getElementById('v-reference').textContent = data.reference_number || 'N/A';
            
            // Update Statuses in Panel Titles
            updatePanelStatus('license', data.license_status);
            updatePanelStatus('payment', data.payment_status);
            
            // Update Images
            updatePanelImage('license', data.license_url, 'Student License');
            updatePanelImage('payment', data.receipt_url, 'GCash Receipt');
            
            // Update Action Buttons Visibility
            const verifyPayBtn = document.getElementById('v-btn-verify-payment');
            const verifyLicBtn = document.getElementById('v-btn-verify-license');
            
            verifyPayBtn.style.display = (data.payment_status === 'pending_verification') ? 'block' : 'none';
            verifyLicBtn.style.display = (data.license_status === 'pending') ? 'block' : 'none';
            
            // Show content
            document.getElementById('v-modal-loading').style.display = 'none';
            document.getElementById('v-modal-content').style.visibility = 'visible';
        })
        .catch(err => {
            Toast.error('Failed to load enrollment details.');
            closeVerificationModal();
        });
}

function updatePanelStatus(type, status) {
    const badge = document.getElementById(`v-${type}-status`);
    badge.textContent = status.replace('_', ' ').toUpperCase();
    
    // Reset classes
    badge.className = 'v-panel-status';
    if (status === 'verified' || status === 'paid') badge.classList.add('bg-success', 'text-white');
    else if (status === 'pending' || status === 'pending_verification') badge.classList.add('bg-warning', 'text-dark');
    else badge.classList.add('bg-secondary', 'text-white');
}

function updatePanelImage(type, url, label) {
    const viewer = document.getElementById(`v-${type}-viewer`);
    if (url) {
        viewer.innerHTML = `<img src="${url}" alt="${label}" class="img-fluid" onclick="window.open('${url}', '_blank')">`;
    } else {
        viewer.innerHTML = `<div class="v-empty-image"><svg class="icon-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg><p>No image uploaded</p></div>`;
    }
}

function closeVerificationModal() {
    document.getElementById('unifiedVerificationModal').style.display = 'none';
    currentEnrollmentId = null;
}

function verifyPaymentAjax() {
    if (!currentEnrollmentId) return;
    
    const btn = document.getElementById('v-btn-verify-payment');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Verifying...';
    
    const url = `{{ school_route('admin.enrollments.index') }}/api/${currentEnrollmentId}/verify-payment`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.success(data.message);
            updateTableRowStatus(currentEnrollmentId, 'payment', 'paid');
            // If the message says "automatically approved", we should probably refresh or update status
            closeVerificationModal();
        } else {
            Toast.error(data.message);
        }
    })
    .catch(err => Toast.error('An error occurred during verification.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function verifyLicenseAjax() {
    if (!currentEnrollmentId) return;
    
    const btn = document.getElementById('v-btn-verify-license');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Verifying...';
    
    const url = `{{ school_route('admin.enrollments.index') }}/api/${currentEnrollmentId}/verify-license`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.success(data.message);
            updateTableRowStatus(currentEnrollmentId, 'license', 'verified');
            closeVerificationModal();
        } else {
            Toast.error(data.message);
        }
    })
    .catch(err => Toast.error('An error occurred during verification.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function updateTableRowStatus(id, type, status) {
    const row = document.querySelector(`tr[data-request-id="${id}"]`);
    if (!row) return;
    
    if (type === 'payment') {
        const badge = row.querySelector('.payment-badge');
        if (badge) {
            badge.className = `payment-badge payment-${status}`;
            badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        }
    } else if (type === 'license') {
        const badge = row.querySelector('.license-badge');
        if (badge) {
            badge.className = `license-badge license-${status}`;
            badge.textContent = 'License: ' + status.charAt(0).toUpperCase() + status.slice(1);
        }
        // Also hide the separate license verify/reject buttons in the table
        const actions = row.querySelector('.license-actions');
        if (actions) actions.style.display = 'none';
    }
}

// Close unified modal when clicking outside
document.getElementById('unifiedVerificationModal').addEventListener('click', function(e) {
    if (e.target === this) closeVerificationModal();
});
</script>

<!-- Unified Verification Modal -->
<div id="unifiedVerificationModal" class="action-modal" style="display: none;">
    <div class="verification-modal-card">
        <div class="v-modal-header">
            <h3 class="m-0 font-weight-bold" style="font-size: 1.1rem; color: #1e293b;">Verification Dashboard</h3>
            <button type="button" onclick="closeVerificationModal()" class="btn-close" style="border:none;background:none;font-size:1.5rem;">&times;</button>
        </div>
        
        <div id="v-modal-loading" style="display:none; flex:1; justify-content:center; align-items:center; flex-direction:column; gap:15px; background: #fff;">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted">Loading enrollment data...</p>
        </div>

        <div id="v-modal-content" class="v-modal-body">
            <!-- Sidebar with Info -->
            <div class="v-modal-sidebar">
                <div class="mb-3">
                    <div class="v-info-label">Learner Name</div>
                    <div id="v-student-name" class="v-info-value">-</div>
                </div>
                <div class="mb-3">
                    <div class="v-info-label">Enrolled Course</div>
                    <div id="v-course-title" class="v-info-value">-</div>
                </div>
                <div class="mb-3">
                    <div class="v-info-label">Total Amount</div>
                    <div id="v-price" class="v-info-value">-</div>
                </div>
                <hr class="my-3">
                <div class="mb-3">
                    <div class="v-info-label">GCash Reference No.</div>
                    <div id="v-reference" class="v-info-value" style="font-family: monospace; font-size: 1.1rem; color: #2563eb;">-</div>
                </div>
                <div class="mt-auto">
                    <p class="text-muted small" style="line-height: 1.4;">
                        <svg class="icon-14 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"></path></svg>
                        Tip: Zoom into images to verify expiration dates or transaction timestamps.
                    </p>
                </div>
            </div>

            <!-- Main Content: Images Side-by-Side -->
            <div class="v-modal-content">
                <!-- Panel 1: Student License -->
                <div class="v-image-panel">
                    <div class="v-panel-title">
                        <span>Identity Document</span>
                        <span id="v-license-status" class="v-panel-status">PENDING</span>
                    </div>
                    <div id="v-license-viewer" class="v-image-viewer">
                        <!-- Image injected here -->
                    </div>
                </div>

                <!-- Panel 2: GCash Receipt -->
                <div class="v-image-panel">
                    <div class="v-panel-title">
                        <span>Payment Receipt</span>
                        <span id="v-payment-status" class="v-panel-status">PENDING</span>
                    </div>
                    <div id="v-payment-viewer" class="v-image-viewer">
                        <!-- Image injected here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Footer for Actions -->
        <div class="v-btn-group">
            <button type="button" onclick="closeVerificationModal()" class="btn btn-secondary" style="background:#e2e8f0; color:#475569;">Close</button>
            <div class="ms-auto d-flex gap-2">
                <button type="button" id="v-btn-verify-license" class="btn btn-primary" onclick="verifyLicenseAjax()" style="display:none;">Verify License</button>
                <button type="button" id="v-btn-verify-payment" class="btn btn-success" style="background: #10b981; border:none; display:none;" onclick="verifyPaymentAjax()">
                    <svg class="icon-14 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
