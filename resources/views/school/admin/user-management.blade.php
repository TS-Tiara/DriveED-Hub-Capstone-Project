@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'User Management')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    
    $primaryColor = $settings->primary_color ?? '#3b82f6';
    $secondaryColor = $settings->secondary_color ?? '#60a5fa';
    
    // Statistics are now passed from the controller to ensure accuracy with pagination
    $totalUsers = $totalStudents + $totalInstructors;
    $totalActive = $activeStudents + $activeInstructors;
    $totalInactive = $inactiveStudents + $inactiveInstructors;

    $oldInviteRole = old('role');
    $showStudentInviteErrors = $errors->any() && $oldInviteRole === 'student';
    $showInstructorInviteErrors = $errors->any() && $oldInviteRole === 'instructor';
    $requireInstructorLicense = $settings?->require_instructor_license ?? true;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .admin-mgmt-container {
        padding: 20px;
        margin: 0 auto;
        max-width: 1600px;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid var(--primary-color);
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
    
    /* Statistics Cards - Using shared styles from admin-styles.blade.php */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    /* Additional stat card color variants for user management */
    .stat-card.total {
        border-left-color: var(--primary-color);
    }
    .stat-card.total::before {
        background: var(--primary-color);
    }
    .stat-card.total .stat-icon {
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary-color);
    }

    .stat-card.inactive {
        border-left-color: #6b7280;
    }
    .stat-card.inactive::before {
        background: #6b7280;
    }
    .stat-card.inactive .stat-icon {
        background: #6b728015;
        color: #6b7280;
    }
    
    /* Action Buttons */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .search-box {
        position: relative;
        flex: 1;
        max-width: 400px;
    }

    .control-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 6px;
    }

    .action-controls {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .search-box input {
        width: 100%;
        padding: 10px 40px 10px 15px;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.3s;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: var(--primary-color);
    }
    
    .search-box::after {
        content: "";
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .btn-create {
        padding: 10px 20px;
        @if(($settings->button_style ?? 'solid') === 'gradient')
        background: linear-gradient(135deg, var(--btn-primary-bg) 0%, var(--btn-secondary-bg) 100%);
        @else
        background: var(--btn-primary-bg);
        @endif
        color: var(--btn-primary-text);
        border: none;
        border-radius: var(--button-border-radius);
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.10);
    }
    
    .btn-create:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.18);
    }

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .icon-18 {
        width: 18px;
        height: 18px;
    }

    .icon-14 {
        width: 14px;
        height: 14px;
    }
    
    /* Table Styles */
    .table-container {
        background: white;
        border-radius: 12px;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
        white-space: nowrap;
    }
    
    thead {
        background: var(--header-gradient);
        color: white;
    }
    
    th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        font-size: 0.95rem;
    }
    
    td {
        padding: 15px;
        border-bottom: 1px solid #f1f3f5;
    }
    
    tbody tr {
        transition: background-color 0.2s;
    }
    
    tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .role-student, .role-guest {
        background: var(--role-student-bg);
        color: var(--role-student-text);
    }

    .role-guest {
        border: 1px dashed var(--role-student-text);
    }

    .role-instructor {
        background: var(--role-instructor-bg);
        color: var(--role-instructor-text);
    }

    .branch-label {
        font-size: 0.85rem;
    }

    .branch-assigned {
        color: #374151;
    }

    .branch-unassigned {
        color: #9ca3af;
    }

    .btn-action {
        padding: 6px 12px;
        margin: 0;
        border: none;
        border: 1px solid transparent;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        line-height: 1.2;
        white-space: nowrap;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-start;
        align-items: center;
        flex-wrap: wrap;
    }

    .action-buttons form {
        margin: 0;
        display: inline-flex;
    }

    .action-buttons .btn-action {
        padding: 8px 12px;
        border-radius: 10px;
    }

    .action-buttons .btn-info {
        background: #e8efff;
        color: #2563eb;
        border-color: #c7d2fe;
    }

    .action-buttons .btn-warning {
        background: #fef3c7;
        color: #b45309;
        border-color: #fde68a;
    }

    .action-buttons .btn-success {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .action-buttons .btn-secondary {
        background: #ffe4e6;
        color: #be123c;
        border-color: #fecdd3;
    }

    .action-buttons .btn-primary {
        background: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .action-buttons .btn-action:hover {
        transform: translateY(-1px);
        filter: brightness(0.98);
    }

    .actions-cell {
        min-width: 220px;
    }

    .actions-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }
    
    .btn-edit {
        background: #ffc107;
        color: #000;
    }
    
    .btn-edit:hover {
        background: #ffb300;
        transform: scale(1.05);
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
    }
    
    .btn-delete:hover {
        background: #c82333;
        transform: scale(1.05);
    }
    
    .btn-view {
        background: #17a2b8;
        color: white;
    }
    
    .btn-view:hover {
        background: #138496;
        transform: scale(1.05);
    }
    
    .btn-toggle {
        background: #f0ad4e;
        color: white;
    }
    
    .btn-toggle:hover {
        background: #ec971f;
        transform: scale(1.05);
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        justify-content: center;
        align-items: center;
    }
    
    .modal-content {
        width: min(600px, 92%);
        min-width: 0;
        max-width: 92%;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        animation: fadeIn 0.2s ease-out;
    }

    /* Override shared admin transform scaling for this page's .modal pattern. */
    #createStudentModal .modal-content,
    #editStudentModal .modal-content,
    #createInstructorModal .modal-content,
    #editInstructorModal .modal-content {
        transform: none;
        transition: none;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.98);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .modal-content h3 {
        background: var(--header-gradient);
        color: white;
        margin: 0;
        padding: 32px;
        font-size: 1.75rem;
        font-weight: 600;
        border-radius: 16px 16px 0 0;
    }
    
    .modal-content form {
        padding: 32px;
        background: #fff;
        margin: 0;
        border-radius: 0 0 16px 16px;
        max-height: 72vh;
        overflow-y: auto;
        scrollbar-gutter: stable;
    }

    .modal-required-note {
        margin: 0 0 18px;
        font-size: 0.86rem;
        color: #6b7280;
    }

    .required-indicator {
        color: #dc2626;
        margin-left: 2px;
    }

    .field-help {
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 4px;
    }

    .field-error {
        font-size: 0.8rem;
        color: #b91c1c;
        margin-top: 6px;
    }

    .modal-error-summary {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 16px;
    }

    .modal-error-summary strong {
        display: block;
        margin-bottom: 8px;
        font-size: 0.88rem;
    }

    .modal-error-summary ul {
        margin: 0;
        padding-left: 18px;
    }

    .modal-error-summary li {
        margin-bottom: 4px;
        font-size: 0.82rem;
    }
    
    .form-group {
        margin-bottom: 24px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: #1a202c;
        font-size: 0.95rem;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px 16px;
        box-sizing: border-box;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: #fff;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }
    
    .modal-buttons {
        display: flex;
        gap: 12px;
        padding: 24px 32px 32px 32px;
        background: #fff;
    }
    
    .btn-cancel {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
        flex: 1;
        padding: 16px 24px;
        font-size: 1.05rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-cancel:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        flex: 1;
        padding: 16px 24px;
        font-size: 1.05rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        border: none;
        cursor: pointer;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
    }

    .btn-submit:disabled {
        cursor: not-allowed;
        opacity: 0.75;
        transform: none;
        box-shadow: none;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 20px;
    }
    
    .empty-state-text {
        font-size: 1.2rem;
        color: #666;
    }

    .pagination-groups {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .pagination-title {
        font-size: 0.9rem;
        margin-bottom: 5px;
        color: #6b7280;
    }
    
    .pagination-title {
        font-size: 0.9rem;
        margin-bottom: 5px;
        color: #6b7280;
    }

    .export-dropdown-menu a .dot.pdf  { background: #ef4444; }
    .export-dropdown-menu a .dot.excel { background: #10b981; }
    
    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .admin-mgmt-container {
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
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .stat-card {
            padding: 18px;
        }
        
        .stat-value {
            font-size: 1.8rem;
        }
        
        .tabs {
            flex-wrap: wrap;
        }
        
        .tab {
            padding: 10px 16px;
            font-size: 0.9rem;
            flex: 1;
            min-width: 120px;
            text-align: center;
        }
        
        .action-bar {
            flex-direction: column;
            gap: 15px;
            padding: 12px;
        }
        
        .action-bar > div:last-child {
            flex-direction: row;
            width: auto;
        }

        .action-controls {
            width: 100%;
            align-items: center;
            justify-content: flex-start;
            gap: 8px;
        }
        
        .search-box {
            max-width: 100%;
            width: 100%;
        }

        .control-label {
            font-size: 0.8rem;
            margin-bottom: 4px;
        }
        
        .btn-create {
            width: auto;
            justify-content: center;
        }
        
        .table-container {
            border-radius: 8px;
        }
        
        table {
            font-size: 0.85rem;
            min-width: 780px;
        }
        
        th, td {
            padding: 10px 8px;
            white-space: nowrap;
        }
        
        .btn-action {
            padding: 5px 8px;
            font-size: 0.8rem;
            margin: 0;
        }

        .actions-group {
            gap: 4px;
        }
        
        .status-badge {
            padding: 4px 8px;
            font-size: 0.75rem;
        }
        
        /* Hide less important columns on mobile */
        .hide-mobile {
            display: none;
        }
        
        .modal-content {
            width: 95%;
            max-width: 95%;
            min-width: 0;
            margin: 10px;
        }
        
        .modal-content h3 {
            padding: 20px;
            font-size: 1.3rem;
        }
        
        .modal-content form {
            padding: 20px;
        }
        
        .modal-buttons {
            padding: 15px 20px 20px;
            flex-direction: column;
        }
        
        .btn-cancel, .btn-submit {
            width: 100%;
            padding: 14px;
        }
    }
    
    @media (max-width: 480px) {
        .admin-mgmt-container {
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
            font-size: 1.5rem;
        }
        
        .stat-label {
            font-size: 0.75rem;
        }
        
        .stat-detail {
            font-size: 0.75rem;
        }
        
        .tab {
            padding: 8px 12px;
            font-size: 0.85rem;
            min-width: 100px;
        }
        
        table {
            font-size: 0.8rem;
            min-width: 720px;
        }
        
        th, td {
            padding: 8px 6px;
            white-space: nowrap;
        }
        
        .btn-action {
            padding: 4px 6px;
            font-size: 0.75rem;
        }

        .form-group label {
            font-size: 0.9rem;
        }
        
        .form-group input,
        .form-group select {
            padding: 10px 12px;
            font-size: 0.95rem;
        }
    }

    /* Pagination styles are inherited from shared admin-styles */
</style>

<div class="admin-mgmt-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">Manage students and instructors in your driving school</p>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card total" onclick="filterUsers('all', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value">{{ $totalUsers }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail"><strong>{{ $totalStudents }}</strong> Students &middot; <strong>{{ $totalInstructors }}</strong> Instructors</div>
            </div>
        </div>
        
        <div class="stat-card active" onclick="filterUsers('active', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active Users</div>
                        <div class="stat-value">{{ $totalActive }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail"><strong>{{ $activeStudents }}</strong> Students &middot; <strong>{{ $activeInstructors }}</strong> Instructors</div>
            </div>
        </div>
        
        <div class="stat-card inactive" onclick="filterUsers('inactive', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Inactive Users</div>
                        <div class="stat-value">{{ $totalInactive }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail"><strong>{{ $inactiveStudents }}</strong> Students &middot; <strong>{{ $inactiveInstructors }}</strong> Instructors</div>
            </div>
        </div>
        
        <div class="stat-card students" onclick="filterUsers('students', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Students</div>
                        <div class="stat-value">{{ $totalStudents }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail"><strong>{{ $activeStudents }}</strong> Active &middot; <strong>{{ $inactiveStudents }}</strong> Inactive</div>
            </div>
        </div>
        
        <div class="stat-card instructors" onclick="filterUsers('instructors', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Instructors</div>
                        <div class="stat-value">{{ $totalInstructors }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail"><strong>{{ $activeInstructors }}</strong> Active &middot; <strong>{{ $inactiveInstructors }}</strong> Inactive</div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notifications handled at the bottom of the file -->
    
    <!-- Students Section -->
    <div id="usersSection" class="user-section">
        <div class="action-bar">
            <div class="search-box">
                <label for="userSearch" class="control-label">Search Users</label>
                <input type="text" id="userSearch" placeholder="Search users by name, email, or role..." onkeyup="filterTable('userSearch', 'usersTable')">
            </div>
            <div class="action-controls">
                @if(isset($branches) && $branches->count() > 0)
                <label for="branchFilter" class="control-label">Branch Filter</label>
                <select id="branchFilter" onchange="filterByBranch()" style="padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; background: white; cursor: pointer;">
                    <option value="">All Branches</option>
                    <option value="unassigned">Unassigned</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                @endif
                <div class="export-dropdown" id="exportDropdown">
                    <button class="btn-export-trigger" onclick="toggleExportDropdown()">
                        <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export
                        <svg class="chevron icon-14" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="export-dropdown-menu">
                        <div class="dropdown-header">Students</div>
                        <a href="{{ school_route('admin.exports.students.pdf') }}">
                            <span class="dot pdf"></span> Students (PDF)
                        </a>
                        <a href="{{ school_route('admin.exports.students.excel') }}">
                            <span class="dot excel"></span> Students (Excel)
                        </a>
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-header">Instructors</div>
                        <a href="{{ school_route('admin.exports.instructors.pdf') }}">
                            <span class="dot pdf"></span> Instructors (PDF)
                        </a>
                        <a href="{{ school_route('admin.exports.instructors.excel') }}">
                            <span class="dot excel"></span> Instructors (Excel)
                        </a>
                    </div>
                </div>
                <button class="btn-create" onclick="openCreateStudentModal()">
                    <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Invite Student
                </button>
                <button class="btn-create" onclick="openCreateInstructorModal()">
                    <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Invite Instructor
                </button>
            </div>
        </div>
        
        <div class="table-container">
            @if($users->count() > 0)
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr data-role="{{ $user->role }}" data-status="{{ $user->status }}" data-branch="{{ $user->branch_id ?? 'unassigned' }}">
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->contact ?: '—' }}</td>
                            <td>
                                <span class="role-badge role-{{ $user->role }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $branchName = $user->branch_id ? ($branches->firstWhere('id', $user->branch_id)?->name ?? null) : null;
                                @endphp
                                <span class="branch-label {{ $branchName ? 'branch-assigned' : 'branch-unassigned' }}">{{ $branchName ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $user->status }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    {{-- Global Edit Button --}}
                                    @if($user->role === 'student' || $user->role === 'guest')
                                        <button type="button" class="btn-action btn-info js-edit-user" 
                                            data-role="student"
                                            data-id="{{ $user->id }}" 
                                            data-name="{{ $user->name }}" 
                                            data-email="{{ $user->email }}" 
                                            data-contact="{{ $user->contact }}" 
                                            data-address="{{ $user->address }}" 
                                            data-branch="{{ $user->branch_id }}"
                                            title="Edit Student">
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Edit</span>
                                        </button>
                                    @elseif($user->role === 'instructor')
                                        <button type="button" class="btn-action btn-info js-edit-user" 
                                            data-role="instructor"
                                            data-id="{{ $user->id }}" 
                                            data-name="{{ $user->name }}" 
                                            data-email="{{ $user->email }}" 
                                            data-contact="{{ $user->contact }}" 
                                            data-license="{{ $user->license_number }}" 
                                            data-branch="{{ $user->branch_id }}"
                                            title="Edit Instructor">
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Edit</span>
                                        </button>
                                    @endif

                                    {{-- Global Status Toggle --}}
                                    @php
                                        $toggleRoute = '';
                                        if($user->role === 'student' || $user->role === 'guest') {
                                            $toggleRoute = route('schools.admin.students.toggleStatus', [$school, $user->id]);
                                        } elseif($user->role === 'instructor') {
                                            $toggleRoute = route('schools.admin.instructors.toggleStatus', [$school, $user->id]);
                                        }
                                    @endphp

                                    @if($toggleRoute)
                                        <button type="button" 
                                            class="btn-action {{ $user->status === 'active' ? 'btn-warning' : 'btn-success' }}" 
                                            onclick="unifiedToggle({{ $user->id }}, 'status', '{{ $user->status }}', '{{ $user->role }}')"
                                            title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi {{ $user->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                            <span>{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}</span>
                                        </button>
                                    @endif

                                    {{-- Instructor Specific Availability --}}
                                    @if($user->role === 'instructor')
                                        <button type="button" 
                                            class="btn-action {{ $user->availability === 'available' ? 'btn-secondary' : 'btn-primary' }}" 
                                            onclick="unifiedToggle({{ $user->id }}, 'availability', '{{ $user->availability }}', '{{ $user->role }}')"
                                            title="{{ $user->availability === 'available' ? 'Mark Unavailable' : 'Mark Available' }}">
                                            <i class="bi {{ $user->availability === 'available' ? 'bi-person-dash' : 'bi-person-check' }}"></i>
                                            <span>{{ $user->availability === 'available' ? 'Unavailable' : 'Available' }}</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon"></div>
                    <div class="empty-state-text">No users found. Add your first student or instructor to get started!</div>
                </div>
            @endif
        </div>
        
        @if($users->count() > 0)
            <div class="mt-4 pagination-groups">
                <div>
                    <h4 style="font-size: 0.9rem; margin-bottom: 5px; color: #6b7280;">
                        Showing {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                    </h4>
                    @if($users->hasPages())
                     <div class="admin-pagination-wrapper">
                        {!! $users->links('vendor.pagination.drivingapp') !!}
                    </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- INVITE STUDENT MODAL -->
<div id="createStudentModal" class="modal">
    <div class="modal-content">
        <h3>Invite New Student</h3>
        <form id="createStudentInviteForm" method="POST" action="{{ school_route('admin.storeAccount') }}" data-no-ajax="1">
            @csrf

            <p class="modal-required-note">Fields marked with <span class="required-indicator">*</span> are required.</p>

            @if($showStudentInviteErrors)
                <div class="modal-error-summary" role="alert">
                    <strong>Please fix the following before sending the invitation:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label>Name <span class="required-indicator">*</span></label>
                <input type="text" name="name" value="{{ $showStudentInviteErrors ? old('name') : '' }}" placeholder="Enter student's full name" required>
                @if($showStudentInviteErrors)
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            <div class="form-group">
                <label>Email <span class="required-indicator">*</span></label>
                <input type="email" name="email" value="{{ $showStudentInviteErrors ? old('email') : '' }}" placeholder="student@example.com" required>
                <p class="field-help">An invitation link will be sent to this email. Currently, Gmail and Yahoo are allowed.</p>
                @if($showStudentInviteErrors)
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            <div class="form-group">
                <label>Contact:</label>
                <input type="text" name="contact" value="{{ $showStudentInviteErrors ? old('contact') : '' }}" placeholder="09123456789">
                @if($showStudentInviteErrors)
                    @error('contact') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address" value="{{ $showStudentInviteErrors ? old('address') : '' }}" placeholder="Enter address (optional)">
                @if($showStudentInviteErrors)
                    @error('address') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            @if(isset($branches) && $branches->count() > 0)
            <div class="form-group">
                <label>Branch:</label>
                <select name="branch_id" class="branch-modal-select">
                    <option value="">No Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $showStudentInviteErrors && (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @if($showStudentInviteErrors)
                    @error('branch_id') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            @endif
            <input type="hidden" name="role" value="student">
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeCreateStudentModal()">Cancel</button>
                <button type="submit" class="btn-submit" data-default-text="Send Invitation">Send Invitation</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT STUDENT MODAL -->
<div id="editStudentModal" class="modal">
    <div class="modal-content">
        <h3>Edit Student</h3>
        <form id="editStudentForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Name:</label>
                <input type="text" id="edit_student_name" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" id="edit_student_email" name="email" required>
            </div>
            <div class="form-group">
                <label>Contact:</label>
                <input type="text" id="edit_student_contact" name="contact">
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" id="edit_student_address" name="address">
            </div>
            @if(isset($branches) && $branches->count() > 0)
            <div class="form-group">
                <label>Branch:</label>
                <select id="edit_student_branch" name="branch_id" class="branch-modal-select">
                    <option value="">No Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeEditStudentModal()">Cancel</button>
                <button type="submit" class="btn-create">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- INVITE INSTRUCTOR MODAL -->
<div id="createInstructorModal" class="modal">
    <div class="modal-content">
        <h3>Invite New Instructor</h3>
        <form id="createInstructorInviteForm" method="POST" action="{{ school_route('admin.storeAccount') }}" data-no-ajax="1">
            @csrf

            <p class="modal-required-note">Fields marked with <span class="required-indicator">*</span> are required.</p>

            @if($showInstructorInviteErrors)
                <div class="modal-error-summary" role="alert">
                    <strong>Please fix the following before sending the invitation:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label>Name <span class="required-indicator">*</span></label>
                <input type="text" name="name" value="{{ $showInstructorInviteErrors ? old('name') : '' }}" placeholder="Enter instructor's full name" required>
                @if($showInstructorInviteErrors)
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            <div class="form-group">
                <label>Email <span class="required-indicator">*</span></label>
                <input type="email" name="email" value="{{ $showInstructorInviteErrors ? old('email') : '' }}" placeholder="instructor@example.com" required>
                <p class="field-help">An invitation link will be sent to this email. Currently, Gmail and Yahoo are allowed.</p>
                @if($showInstructorInviteErrors)
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            <div class="form-group">
                <label>Contact:</label>
                <input type="text" name="contact" value="{{ $showInstructorInviteErrors ? old('contact') : '' }}" placeholder="09123456789">
                @if($showInstructorInviteErrors)
                    @error('contact') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            <div class="form-group">
                <label>License Number @if($requireInstructorLicense)<span class="required-indicator">*</span>@endif</label>
                <input type="text" name="license_number" value="{{ $showInstructorInviteErrors ? old('license_number') : '' }}" placeholder="Enter license number" @if($requireInstructorLicense)required @endif>
                @if($requireInstructorLicense)
                    <p class="field-help">Required because instructor license verification is enabled.</p>
                @endif
                @if($showInstructorInviteErrors)
                    @error('license_number') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address" value="{{ $showInstructorInviteErrors ? old('address') : '' }}" placeholder="Enter address (optional)">
                @if($showInstructorInviteErrors)
                    @error('address') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            @if(isset($branches) && $branches->count() > 0)
            <div class="form-group">
                <label>Branch:</label>
                <select name="branch_id" class="branch-modal-select">
                    <option value="">No Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $showInstructorInviteErrors && (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @if($showInstructorInviteErrors)
                    @error('branch_id') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            @endif
            <input type="hidden" name="role" value="instructor">
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeCreateInstructorModal()">Cancel</button>
                <button type="submit" class="btn-submit" data-default-text="Send Invitation">Send Invitation</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT INSTRUCTOR MODAL -->
<div id="editInstructorModal" class="modal">
    <div class="modal-content">
        <h3>Edit Instructor</h3>
        <form id="editInstructorForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Name:</label>
                <input type="text" id="edit_instructor_name" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" id="edit_instructor_email" name="email" required>
            </div>
            <div class="form-group">
                <label>Contact:</label>
                <input type="text" id="edit_instructor_contact" name="contact">
            </div>
            <div class="form-group">
                <label>License Number:</label>
                <input type="text" id="edit_instructor_license" name="license_number">
            </div>
            @if(isset($branches) && $branches->count() > 0)
            <div class="form-group">
                <label>Branch:</label>
                <select id="edit_instructor_branch" name="branch_id" class="branch-modal-select">
                    <option value="">No Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeEditInstructorModal()">Cancel</button>
                <button type="submit" class="btn-create">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    const schoolSlug = '{{ $school->slug }}';
    const studentBaseUrl = '/{{ $school->slug }}/admin/students';
    const instructorBaseUrl = '/{{ $school->slug }}/admin/instructors';

    function hardenUserManagementActionForms() {
        const actionForms = document.querySelectorAll('form[data-no-ajax][action*="/toggle-status"], form[data-no-ajax][action*="/availability"]');
        if (!actionForms.length) {
            return;
        }

        let pageToken = '';

        actionForms.forEach(form => {
            form.classList.add('native-form');
            form.setAttribute('data-no-ajax', '1');
            form.setAttribute('data-no-submit-guard', '1');

            const tokenInput = form.querySelector('input[name="_token"]');
            if (!pageToken && tokenInput && tokenInput.value) {
                pageToken = tokenInput.value;
            }
        });

        if (pageToken) {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta && csrfMeta.getAttribute('content') !== pageToken) {
                csrfMeta.setAttribute('content', pageToken);
            }
        }
    }

    // Initialize user management page
    function initializeUserManagementPage() {
        hardenUserManagementActionForms();
        console.log('User Management page initialized');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeUserManagementPage);
    } else {
        initializeUserManagementPage();
    }

    // Filter Users by Type (via stat card clicks)
    function filterUsers(type, card) {
        const rows = document.querySelectorAll('#usersTable tbody tr');

        rows.forEach(row => {
            const role = row.getAttribute('data-role');
            const status = row.getAttribute('data-status');
            let show = true;

            if (type === 'students') show = (role === 'student');
            else if (type === 'instructors') show = (role === 'instructor');
            else if (type === 'active') show = (status === 'active');
            else if (type === 'inactive') show = (status === 'inactive');
            // 'all' -> show everything

            row.style.display = show ? '' : 'none';
        });
    }

    function filterByBranch() {
        const val = document.getElementById('branchFilter').value;
        const rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(row => {
            const branch = row.getAttribute('data-branch');
            if (!val) {
                row.style.display = '';
            } else if (val === 'unassigned') {
                row.style.display = (branch === 'unassigned' || !branch) ? '' : 'none';
            } else {
                row.style.display = (branch === val) ? '' : 'none';
            }
        });
    }
    
    // Table Search/Filter
    function filterTable(searchId, tableId) {
        const input = document.getElementById(searchId);
        const filter = input.value.toUpperCase();
        const table = document.getElementById(tableId);
        if (!table) return;
        const tr = table.getElementsByTagName('tr');
        
        for (let i = 1; i < tr.length; i++) {
            const row = tr[i];
            const cells = row.getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell) {
                    const textValue = cell.textContent || cell.innerText;
                    if (textValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            row.style.display = found ? '' : 'none';
        }
    }

    function setInviteFormLoadingState(form, isLoading) {
        if (!form) return;

        const submitButton = form.querySelector('.btn-submit');
        if (!submitButton) return;

        const defaultText = submitButton.dataset.defaultText || submitButton.textContent.trim();
        submitButton.dataset.defaultText = defaultText;
        submitButton.disabled = isLoading;
        submitButton.textContent = isLoading ? 'Sending Invitation...' : defaultText;
    }

    function bindInviteFormSubmit(formId) {
        const form = document.getElementById(formId);
        if (!form || form.dataset.loadingBound === '1') {
            return;
        }

        form.dataset.loadingBound = '1';
        form.addEventListener('submit', function () {
            setInviteFormLoadingState(form, true);
        });
    }
    
    // Student Modal Functions
    function openCreateStudentModal() {
        document.getElementById('createStudentModal').style.display = 'flex';
        setInviteFormLoadingState(document.getElementById('createStudentInviteForm'), false);
    }
    
    function closeCreateStudentModal() {
        document.getElementById('createStudentModal').style.display = 'none';
        setInviteFormLoadingState(document.getElementById('createStudentInviteForm'), false);
    }
    
    function editStudent(id, name, email, contact, address, branchId) {
        const form = document.getElementById('editStudentForm');
        form.action = `${studentBaseUrl}/${id}`;
        document.getElementById('edit_student_name').value = name || '';
        document.getElementById('edit_student_email').value = email || '';
        document.getElementById('edit_student_contact').value = contact || '';
        document.getElementById('edit_student_address').value = address || '';
        const branchSelect = document.getElementById('edit_student_branch');
        if (branchSelect) branchSelect.value = branchId || '';
        document.getElementById('editStudentModal').style.display = 'flex';
    }
    
    function closeEditStudentModal() {
        document.getElementById('editStudentModal').style.display = 'none';
    }
    
    // Export Dropdown
    function toggleExportDropdown() {
        document.getElementById('exportDropdown').classList.toggle('open');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-edit-user');
        if (btn) {
            const role = btn.dataset.role;
            if (role === 'student') {
                editStudent(
                    btn.dataset.id, 
                    btn.dataset.name, 
                    btn.dataset.email, 
                    btn.dataset.contact, 
                    btn.dataset.address, 
                    btn.dataset.branch
                );
            } else if (role === 'instructor') {
                editInstructor(
                    btn.dataset.id, 
                    btn.dataset.name, 
                    btn.dataset.email, 
                    btn.dataset.contact, 
                    btn.dataset.license, 
                    btn.dataset.branch
                );
            }
        }

        const dropdown = document.getElementById('exportDropdown');
        if (dropdown && !dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });
    
    function viewStudent(id) {
        Toast.info('Student details view coming soon!', 'Feature Info');
    }
    
    function unifiedToggle(id, type, currentValue, role) {
        const action = (type === 'status') 
            ? (currentValue === 'active' ? 'Deactivate' : 'Activate')
            : (currentValue === 'available' ? 'Mark Unavailable' : 'Mark Available');

        const typeLabel = (type === 'status') ? 'Account Status' : 'Availability';

        showConfirm({
            type: (currentValue === 'active' || currentValue === 'available') ? 'warning' : 'success',
            title: `${action} ${role.charAt(0).toUpperCase() + role.slice(1)}`,
            message: `Are you sure you want to ${action.toLowerCase()} this ${role}'s ${typeLabel.toLowerCase()}?`,
            confirmText: `Yes, ${action}`,
            onConfirm: () => {
                // Get fresh token from meta tag
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Determine correct route (Relative paths for Hostinger/CORS safety)
                let url = '';
                if (type === 'availability') {
                    url = '/' + schoolSlug + '/admin/instructors/' + id + '/availability';
                } else {
                    const basePart = (role === 'instructor') ? 'instructors' : 'students';
                    url = '/' + schoolSlug + '/admin/' + basePart + '/' + id + '/toggle-status';
                }

                fetch(url, {
                    method: 'PATCH',
                    redirect: 'manual', // <--- PREVENT 405 error by stopping automatic PATCH follow
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async response => {
                    // Check for session timeouts (419/401)
                    if (response.status === 419 || response.status === 401) {
                        showConfirm({
                            type: 'error',
                            title: 'Session Expired',
                            message: 'Your session has timed out. Please refresh and log in again.',
                            confirmText: 'Log In Again',
                            onConfirm: () => window.location.reload()
                        });
                        return;
                    }

                    // SUCCESS: In 'manual' mode, status 0 or 302 means the PATCH worked and Laravel is redirecting "Back"
                    // Status 0 (opaque redirect) or ok (200) both mean SUCCESS here.
                    if (response.ok || response.status === 0 || response.status === 302) {
                        Toast.success(`${typeLabel} updated successfully!`);
                        
                        try {
                            // CLEAN REFRESH: Manually trigger a fresh GET request to reload the table
                            if (typeof loadContent === 'function') {
                                loadContent(window.location.pathname);
                            } else {
                                window.location.reload();
                            }
                        } catch (refreshErr) {
                            console.warn('Silent refresh error:', refreshErr);
                            window.location.reload();
                        }
                    } else {
                        const error = new Error(`Server error`);
                        error.status = response.status;
                        throw error;
                    }
                })
                .catch(error => {
                    console.error('Toggle error:', error);
                    // Forensic Reporting: No more "Unknown" if we have a status
                    const detail = error.status ? `(Status ${error.status})` : `(${error.message})`;
                    Toast.error('Failed to update ' + detail + '. Please refresh and try again.');
                });
            }
        });
    }

    // Instructor Modal Functions
    function openCreateInstructorModal() {
        document.getElementById('createInstructorModal').style.display = 'flex';
        setInviteFormLoadingState(document.getElementById('createInstructorInviteForm'), false);
    }
    
    function closeCreateInstructorModal() {
        document.getElementById('createInstructorModal').style.display = 'none';
        setInviteFormLoadingState(document.getElementById('createInstructorInviteForm'), false);
    }
    
    function editInstructor(id, name, email, contact, license, branchId) {
        const form = document.getElementById('editInstructorForm');
        form.action = `${instructorBaseUrl}/${id}`;
        document.getElementById('edit_instructor_name').value = name;
        document.getElementById('edit_instructor_email').value = email;
        document.getElementById('edit_instructor_contact').value = contact || '';
        document.getElementById('edit_instructor_license').value = license || '';
        const branchSelect = document.getElementById('edit_instructor_branch');
        if (branchSelect) branchSelect.value = branchId || '';
        document.getElementById('editInstructorModal').style.display = 'flex';
    }
    
    function closeEditInstructorModal() {
        document.getElementById('editInstructorModal').style.display = 'none';
    }
    
    function viewInstructor(id) {
        Toast.info('Instructor details view coming soon!', 'Feature Info');
    }

    bindInviteFormSubmit('createStudentInviteForm');
    bindInviteFormSubmit('createInstructorInviteForm');

    (function restoreInviteModalAfterValidationError() {
        const hasInviteValidationErrors = @json($errors->any());
        const inviteRole = @json($oldInviteRole);

        if (!hasInviteValidationErrors) {
            return;
        }

        if (inviteRole === 'student') {
            openCreateStudentModal();
        } else if (inviteRole === 'instructor') {
            openCreateInstructorModal();
        }
    })();
    
    // Close modal when clicking outside
    window.onclick = function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    }
    
</script>

@endsection
