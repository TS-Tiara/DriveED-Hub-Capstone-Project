@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'User Management')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    
    // Calculate statistics
    $totalStudents = $students->count();
    $activeStudents = $students->where('status', 'active')->count();
    $inactiveStudents = $students->where('status', 'inactive')->count();
    
    $totalInstructors = $instructors->count();
    $activeInstructors = $instructors->where('status', 'active')->count();
    $inactiveInstructors = $instructors->where('status', 'inactive')->count();
    
    $totalUsers = $totalStudents + $totalInstructors;
    $totalActive = $activeStudents + $activeInstructors;
    $totalInactive = $inactiveStudents + $inactiveInstructors;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .user-management-container {
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
        border-bottom: 3px solid {{ $settings->primary_color ?? '#667eea' }};
    }
    
    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
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
        border-left-color: #6366f1;
    }
    .stat-card.total::before {
        background: #6366f1;
    }
    .stat-card.total .stat-icon {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #4338ca;
    }

    .stat-card.inactive {
        border-left-color: #f59e0b;
    }
    .stat-card.inactive::before {
        background: #f59e0b;
    }
    .stat-card.inactive .stat-icon {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #b45309;
    }
    
    /* Tabs */
    .tabs-container {
        margin-bottom: 25px;
        border-bottom: 2px solid #e1e5e9;
    }
    
    .tabs {
        display: flex;
        gap: 10px;
    }
    
    .tab {
        padding: 12px 24px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: #666;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .tab:hover {
        color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }
    
    .tab.active {
        color: #667eea;
        border-bottom-color: #667eea;
        font-weight: 600;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
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
        border-color: #667eea;
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
    
    /* Table Styles */
    .table-container {
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    
    .btn-action {
        padding: 6px 12px;
        margin: 0 3px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.2s;
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
        width: 600px;
        max-width: 92%;
        border-radius: 16px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .modal-content h3 {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        max-height: 70vh;
        overflow-y: auto;
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
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
        .user-management-container {
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
            flex-direction: column;
            width: 100%;
        }
        
        .search-box {
            max-width: 100%;
            width: 100%;
        }
        
        .export-buttons {
            width: 100%;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-export {
            flex: 1;
            min-width: 140px;
        }
        
        .btn-create {
            width: 100%;
            justify-content: center;
        }
        
        .table-container {
            border-radius: 8px;
        }
        
        table {
            font-size: 0.85rem;
        }
        
        th, td {
            padding: 10px 8px;
        }
        
        .btn-action {
            padding: 5px 8px;
            font-size: 0.8rem;
            margin: 2px;
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
        .user-management-container {
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
        }
        
        th, td {
            padding: 8px 6px;
        }
        
        .btn-action {
            padding: 4px 6px;
            font-size: 0.75rem;
        }
        
        .btn-export {
            padding: 8px 12px;
            font-size: 0.8rem;
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
</style>

<div class="user-management-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">User Management</h1>
            <p style="color: #6b7280; font-size: 0.9rem; margin-top: 5px;">Manage students and instructors in your driving school</p>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail"><strong>{{ $totalStudents }}</strong> Students · <strong>{{ $totalInstructors }}</strong> Instructors</div>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail"><strong>{{ $activeStudents }}</strong> Students · <strong>{{ $activeInstructors }}</strong> Instructors</div>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail"><strong>{{ $inactiveStudents }}</strong> Students · <strong>{{ $inactiveInstructors }}</strong> Instructors</div>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail"><strong>{{ $activeStudents }}</strong> Active · <strong>{{ $inactiveStudents }}</strong> Inactive</div>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail"><strong>{{ $activeInstructors }}</strong> Active · <strong>{{ $inactiveInstructors }}</strong> Inactive</div>
            </div>
        </div>
    </div>
    
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
    
    <!-- Students Section -->
    <div id="students" class="user-section">
        <div class="action-bar">
            <div class="search-box">
                <input type="text" id="studentSearch" placeholder="Search students by name or email..." onkeyup="filterTable('studentSearch', 'studentsTable')">
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <div class="export-buttons">
                    <a href="{{ $schoolRoute('admin.exports.students.pdf') }}" class="btn-export btn-export-pdf" title="Export all students as PDF">
                        Export Students (PDF)
                    </a>
                    <a href="{{ $schoolRoute('admin.exports.students.excel') }}" class="btn-export btn-export-excel" title="Export all students as Excel CSV">
                        Export Students (Excel)
                    </a>
                </div>
                <button class="btn-create" onclick="openCreateStudentModal()">
                    <i class="bi bi-person-plus"></i> Add New Student
                </button>
            </div>
        </div>
        
        <div class="table-container">
            @if($students->count() > 0)
                <table id="studentsTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td><strong>{{ $student->name }}</strong></td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->contact ?? 'N/A' }}</td>
                            <td>{{ $student->address ?? 'N/A' }}</td>
                            <td>
                                <span class="status-badge status-{{ $student->status }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn-action btn-edit" onclick="editStudent({{ $student->id }}, '{{ $student->name }}', '{{ $student->email }}', '{{ $student->contact }}', '{{ $student->address }}')" >Edit</button>
                                <button class="btn-action btn-toggle" onclick="toggleStudentStatus({{ $student->id }}, '{{ $student->status }}')">
                                    {{ $student->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon"></div>
                    <div class="empty-state-text">No students found. Add your first student to get started!</div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Instructors Section -->
    <div id="instructors" class="user-section">
        <div class="action-bar">
            <div class="search-box">
                <input type="text" id="instructorSearch" placeholder="Search instructors by name or email..." onkeyup="filterTable('instructorSearch', 'instructorsTable')">
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <div class="export-buttons">
                    <a href="{{ $schoolRoute('admin.exports.instructors.pdf') }}" class="btn-export btn-export-pdf" title="Export all instructors as PDF">
                        Export Instructors (PDF)
                    </a>
                    <a href="{{ $schoolRoute('admin.exports.instructors.excel') }}" class="btn-export btn-export-excel" title="Export all instructors as Excel CSV">
                        Export Instructors (Excel)
                    </a>
                </div>
                <button class="btn-create" onclick="openCreateInstructorModal()">
                    <i class="bi bi-person-plus"></i> Add New Instructor
                </button>
            </div>
        </div>
        
        <div class="table-container">
            @if($instructors->count() > 0)
                <table id="instructorsTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>License Number</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instructors as $instructor)
                        <tr>
                            <td><strong>{{ $instructor->name }}</strong></td>
                            <td>{{ $instructor->email }}</td>
                            <td>{{ $instructor->contact ?? 'N/A' }}</td>
                            <td>{{ $instructor->license_number ?? 'N/A' }}</td>
                            <td>
                                <span class="status-badge status-{{ $instructor->status }}">
                                    {{ ucfirst($instructor->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn-action btn-edit" onclick="editInstructor({{ $instructor->id }}, '{{ $instructor->name }}', '{{ $instructor->email }}', '{{ $instructor->contact }}', '{{ $instructor->license_number }}')">Edit</button>
                                <button class="btn-action btn-toggle" onclick="toggleInstructorStatus({{ $instructor->id }}, '{{ $instructor->status }}')">
                                    {{ $instructor->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button class="btn-action btn-toggle" onclick="toggleInstructorAvailability({{ $instructor->id }}, '{{ $instructor->availability }}')">
                                    {{ $instructor->availability === 'available' ? 'Mark Unavailable' : 'Mark Available' }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon"></div>
                    <div class="empty-state-text">No instructors found. Add your first instructor to get started!</div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- CREATE STUDENT MODAL -->
<div id="createStudentModal" class="modal">
    <div class="modal-content">
        <h3>Create New Student</h3>
        <form method="POST" action="{{ $schoolRoute('admin.storeAccount') }}">
            @csrf
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Contact:</label>
                <input type="text" name="contact">
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address">
            </div>
            <input type="hidden" name="role" value="student">
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Save</button>
                <button type="button" class="btn-cancel" onclick="closeCreateStudentModal()">Cancel</button>
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
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Update</button>
                <button type="button" class="btn-cancel" onclick="closeEditStudentModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- CREATE INSTRUCTOR MODAL -->
<div id="createInstructorModal" class="modal">
    <div class="modal-content">
        <h3>Create New Instructor</h3>
        <form method="POST" action="{{ $schoolRoute('admin.storeAccount') }}">
            @csrf
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Contact:</label>
                <input type="text" name="contact">
            </div>
            <div class="form-group">
                <label>License Number:</label>
                <input type="text" name="license_number">
            </div>
            <input type="hidden" name="role" value="instructor">
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Save</button>
                <button type="button" class="btn-cancel" onclick="closeCreateInstructorModal()">Cancel</button>
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
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Update</button>
                <button type="button" class="btn-cancel" onclick="closeEditInstructorModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    const studentBaseUrl = '{{ $schoolUrl("admin/students") }}';
    const instructorBaseUrl = '{{ $schoolUrl("admin/instructors") }}';

    // Initialize user management page
    function initializeUserManagementPage() {
        // Any initialization code that needs to run on page load
        console.log('User Management page initialized');
    }

    // Call initialization on DOMContentLoaded (initial page load)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeUserManagementPage);
    } else {
        // DOM already loaded (AJAX navigation), initialize immediately
        initializeUserManagementPage();
    }

    // Filter Users by Type
    function filterUsers(type, card) {
        // Remove active class from all stat cards
        document.querySelectorAll('.stat-card').forEach(c => {
            c.classList.remove('active');
        });
        
        // Add active class to clicked card
        card.classList.add('active');
        
        // Show/hide sections based on filter
        const studentsSection = document.getElementById('students');
        const instructorsSection = document.getElementById('instructors');
        
        // Reset all rows to visible first
        document.querySelectorAll('.table-container table tbody tr').forEach(row => {
            row.style.display = '';
        });
        
        if (type === 'all') {
            studentsSection.style.display = 'block';
            instructorsSection.style.display = 'block';
        } else if (type === 'students') {
            studentsSection.style.display = 'block';
            instructorsSection.style.display = 'none';
        } else if (type === 'instructors') {
            studentsSection.style.display = 'none';
            instructorsSection.style.display = 'block';
        } else if (type === 'active') {
            studentsSection.style.display = 'block';
            instructorsSection.style.display = 'block';
            filterByStatus('active');
        } else if (type === 'inactive') {
            studentsSection.style.display = 'block';
            instructorsSection.style.display = 'block';
            filterByStatus('inactive');
        }
    }
    
    // Filter by Status (Active/Inactive)
    function filterByStatus(status) {
        const tables = document.querySelectorAll('.table-container table tbody tr');
        
        tables.forEach(row => {
            const statusSpan = row.querySelector('.status-badge');
            if (statusSpan) {
                const rowStatus = statusSpan.textContent.trim().toLowerCase();
                row.style.display = (rowStatus === status) ? '' : 'none';
            }
        });
    }

    // Filter Users by Type
    function filterUsers(type, card) {
        // Remove active class from all stat cards
        document.querySelectorAll('.stat-card').forEach(c => {
            c.classList.remove('active');
        });
        
        // Add active class to clicked card
        card.classList.add('active');
        
        // Show/hide sections based on filter
        const studentsSection = document.getElementById('students');
        const instructorsSection = document.getElementById('instructors');
        
        if (type === 'all') {
            studentsSection.style.display = 'block';
            instructorsSection.style.display = 'block';
        } else if (type === 'students') {
            studentsSection.style.display = 'block';
            instructorsSection.style.display = 'none';
        } else if (type === 'instructors') {
            studentsSection.style.display = 'none';
            instructorsSection.style.display = 'block';
        } else if (type === 'active') {
            studentsSection.style.display = 'block';
            instructorsSection.style.display = 'block';
            filterByStatus('active');
        } else if (type === 'inactive') {
            studentsSection.style.display = 'block';
            instructorsSection.style.display = 'block';
            filterByStatus('inactive');
        }
    }
    
    // Filter by Status (Active/Inactive)
    function filterByStatus(status) {
        const tables = document.querySelectorAll('.table-container table tbody tr');
        
        tables.forEach(row => {
            const statusBadge = row.querySelector('.badge');
            if (statusBadge) {
                const rowStatus = statusBadge.classList.contains('badge-active') ? 'active' : 'inactive';
                row.style.display = (rowStatus === status) ? '' : 'none';
            }
        });
    }
    
    // Table Search/Filter
    function filterTable(searchId, tableId) {
        const input = document.getElementById(searchId);
        const filter = input.value.toUpperCase();
        const table = document.getElementById(tableId);
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
    
    // Student Modal Functions
    function openCreateStudentModal() {
        document.getElementById('createStudentModal').style.display = 'flex';
    }
    
    function closeCreateStudentModal() {
        document.getElementById('createStudentModal').style.display = 'none';
    }
    
    function editStudent(id, name, email, contact, address) {
        const form = document.getElementById('editStudentForm');
        form.action = `${studentBaseUrl}/${id}`;
        document.getElementById('edit_student_name').value = name;
        document.getElementById('edit_student_email').value = email;
        document.getElementById('edit_student_contact').value = contact || '';
        document.getElementById('edit_student_address').value = address || '';
        document.getElementById('editStudentModal').style.display = 'flex';
    }
    
    function closeEditStudentModal() {
        document.getElementById('editStudentModal').style.display = 'none';
    }
    
    function viewStudent(id) {
        Toast.info('Student details view coming soon!', 'Feature Info');
    }
    
    function toggleStudentStatus(id, currentStatus) {
        const action = currentStatus === 'active' ? 'deactivate' : 'activate';
        showConfirm({
            type: currentStatus === 'active' ? 'warning' : 'success',
            title: `${currentStatus === 'active' ? 'Deactivate' : 'Activate'} Student`,
            message: `Are you sure you want to ${action} this student account?`,
            confirmText: `Yes, ${action.charAt(0).toUpperCase() + action.slice(1)}`,
            onConfirm: () => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `${studentBaseUrl}/${id}/toggle-status`;
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PATCH';
                
                form.appendChild(csrfInput);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    
    // Instructor Modal Functions
    function openCreateInstructorModal() {
        document.getElementById('createInstructorModal').style.display = 'flex';
    }
    
    function closeCreateInstructorModal() {
        document.getElementById('createInstructorModal').style.display = 'none';
    }
    
    function editInstructor(id, name, email, contact, license) {
        const form = document.getElementById('editInstructorForm');
        form.action = `${instructorBaseUrl}/${id}`;
        document.getElementById('edit_instructor_name').value = name;
        document.getElementById('edit_instructor_email').value = email;
        document.getElementById('edit_instructor_contact').value = contact || '';
        document.getElementById('edit_instructor_license').value = license || '';
        document.getElementById('editInstructorModal').style.display = 'flex';
    }
    
    function closeEditInstructorModal() {
        document.getElementById('editInstructorModal').style.display = 'none';
    }
    
    function viewInstructor(id) {
        Toast.info('Instructor details view coming soon!', 'Feature Info');
    }
    
    function toggleInstructorStatus(id, currentStatus) {
        const action = currentStatus === 'active' ? 'deactivate' : 'activate';
        showConfirm({
            type: currentStatus === 'active' ? 'warning' : 'success',
            title: `${currentStatus === 'active' ? 'Deactivate' : 'Activate'} Instructor`,
            message: `Are you sure you want to ${action} this instructor account?`,
            confirmText: `Yes, ${action.charAt(0).toUpperCase() + action.slice(1)}`,
            onConfirm: () => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `${instructorBaseUrl}/${id}/toggle-status`;
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PATCH';
                
                form.appendChild(csrfInput);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    
    function toggleInstructorAvailability(id, currentAvailability) {
        const action = currentAvailability === 'available' ? 'unavailable' : 'available';
        showConfirm({
            type: 'info',
            title: 'Change Availability',
            message: `Mark this instructor as ${action}?`,
            confirmText: `Yes, Mark ${action.charAt(0).toUpperCase() + action.slice(1)}`,
            onConfirm: () => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `${instructorBaseUrl}/${id}/availability`;
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PATCH';
                
                form.appendChild(csrfInput);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    
    // Close modal when clicking outside
    window.onclick = function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    }
</script>

@endsection
