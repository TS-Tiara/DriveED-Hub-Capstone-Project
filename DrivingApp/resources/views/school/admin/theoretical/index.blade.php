@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Theoretical Completion Management')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
    
    // Helper function for school-scoped routes
    $schoolRoute = function($routeName, $params = []) use ($school) {
        return route('schools.' . $routeName, array_merge(['school' => $school->slug], $params));
    };
@endphp

@include('school.admin.partials.admin-styles')

<style>
    /* === CONTAINER & LAYOUT === */
    .theoretical-container {
        padding: 25px;
        margin: 15px auto;
        max-width: 1600px;
    }
    
    /* === PAGE HEADER === */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .page-header-left h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 4px 0;
        letter-spacing: -0.01em;
    }
    
    .page-header-left p {
        color: #6b7280;
        font-size: 0.875rem;
        margin: 0;
    }
    
    .page-header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    /* === COMPACT STATS BADGE === */
    .stats-badge {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 10px 20px;
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-radius: 30px;
        border: 2px solid #3b82f6;
    }
    
    .stats-badge-icon {
        width: 32px;
        height: 32px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e40af;
        flex-shrink: 0;
    }
    
    .stats-badge-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .stats-badge-label {
        font-size: 0.7rem;
        color: #1e40af;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        line-height: 1;
    }
    
    .stats-badge-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e40af;
        line-height: 1;
    }
    
    /* === TABS NAVIGATION === */
    .tabs-container {
        margin-bottom: 20px;
    }
    
    .tabs {
        display: flex;
        gap: 4px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .tab {
        padding: 10px 20px;
        color: #6b7280;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        border: none;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        position: relative;
        margin-bottom: -2px;
        background: transparent;
        cursor: pointer;
    }
    
    .tab:hover {
        color: #3b82f6;
        background: rgba(59, 130, 246, 0.05);
    }
    
    .tab.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
        font-weight: 600;
    }
    
    /* === TAB CONTENT === */
    .tab-content-wrapper {
        position: relative;
    }
    
    .tab-content {
        display: none;
        animation: fadeInContent 0.3s ease-out;
    }
    
    .tab-content.active {
        display: block;
    }
    
    @keyframes fadeInContent {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* === MINI STATS GRID === */
    .stats-mini-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    
    .stat-mini {
        background: white;
        border-radius: 10px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    
    .stat-mini-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    
    .stat-mini-icon.success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }
    
    .stat-mini-icon.primary {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
    }
    
    .stat-mini-content {
        flex: 1;
    }
    
    .stat-mini-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
        margin-bottom: 4px;
    }
    
    .stat-mini-label {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    /* === ALERT MESSAGES === */
    .alert {
        padding: 12px 18px;
        margin-bottom: 20px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
        border-left: 4px solid;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left-color: #10b981;
    }
    
    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-left-color: #ef4444;
    }
    
    .alert i {
        font-size: 1.1rem;
    }
    
    .btn-close {
        margin-left: auto;
        background: transparent;
        border: none;
        font-size: 1.3rem;
        color: inherit;
        opacity: 0.6;
        cursor: pointer;
        padding: 0 4px;
        line-height: 1;
    }
    
    .btn-close:hover {
        opacity: 1;
    }
    
    .btn-close::before {
        content: "×";
    }
    
    /* === CONTENT WRAPPER === */
    .content-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    
    .section-header {
        padding: 14px 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    
    .section-subtitle {
        font-size: 0.8rem;
        color: #6b7280;
        margin: 0;
    }
    
    /* === TABLE STYLES === */
    .table-wrapper {
        overflow-x: auto;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    thead {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: white;
    }
    
    th {
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }
    
    td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.9rem;
    }
    
    tbody tr {
        transition: background-color 0.2s ease;
    }
    
    tbody tr:hover {
        background-color: #f8fafc;
    }
    
    tbody tr:last-child td {
        border-bottom: none;
    }
    
    /* === TABLE CELL CONTENT === */
    .user-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }
    
    .user-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .user-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
    }
    
    .user-email {
        font-size: 0.75rem;
        color: #6b7280;
    }
    
    .course-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    
    .course-title {
        color: #1f2937;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .course-badges {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .hours-info {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    
    .hours-value {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
    }
    
    .hours-label {
        color: #6b7280;
        font-size: 0.75rem;
    }
    
    /* === BADGES === */
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    
    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .badge-secondary {
        background: #e5e7eb;
        color: #4b5563;
    }
    
    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }
    
    /* === PROGRESS BAR === */
    .progress-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .progress-bar-container {
        width: 100px;
        height: 8px;
        background: #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        flex-shrink: 0;
    }
    
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        border-radius: 10px;
        transition: width 0.4s ease;
    }
    
    .progress-text {
        font-size: 0.8rem;
        color: #374151;
        font-weight: 600;
        white-space: nowrap;
    }
    
    /* === ACTION BUTTONS === */
    .btn-action {
        padding: 7px 14px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-view {
        background: #0ea5e9;
        color: white;
    }
    
    .btn-view:hover {
        background: #0284c7;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(14, 165, 233, 0.3);
        color: white;
    }
    
    /* === EMPTY STATE === */
    .empty-state {
        padding: 60px 30px;
        text-align: center;
    }
    
    .empty-state-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
    }
    
    .empty-state-icon::before {
        content: "✓";
        color: #10b981;
        font-weight: bold;
    }
    
    .empty-state-text {
        font-size: 1rem;
        color: #6b7280;
        line-height: 1.5;
    }
    
    /* === PAGINATION === */
    .pagination-wrapper {
        padding: 16px 20px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    
    .pagination a,
    .pagination span {
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        color: #6b7280;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        background: white;
        min-width: 36px;
        text-align: center;
    }
    
    .pagination a:hover {
        background: {{ $primaryColor }};
        color: white;
        border-color: {{ $primaryColor }};
    }
    
    .pagination .active span {
        background: {{ $primaryColor }};
        color: white;
        border-color: {{ $primaryColor }};
    }
    
    .pagination .disabled span {
        opacity: 0.4;
        cursor: not-allowed;
    }
    
    /* === RESPONSIVE DESIGN === */
    @media (max-width: 1024px) {
        .theoretical-container {
            padding: 20px;
        }
        
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        
        .page-header-right {
            width: 100%;
            justify-content: flex-start;
        }
    }
    
    @media (max-width: 768px) {
        .theoretical-container {
            padding: 15px;
            margin: 10px auto;
        }
        
        .page-header-left h1 {
            font-size: 1.3rem;
        }
        
        .page-header-left p {
            font-size: 0.8rem;
        }
        
        .tabs {
            width: 100%;
        }
        
        .tab {
            flex: 1;
            text-align: center;
            padding: 10px 12px;
            font-size: 0.85rem;
        }
        
        .stats-badge {
            padding: 8px 16px;
        }
        
        .stats-badge-value {
            font-size: 1.3rem;
        }
        
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        table {
            min-width: 800px;
        }
        
        th, td {
            padding: 10px 12px;
            font-size: 0.8rem;
        }
        
        .user-avatar {
            width: 34px;
            height: 34px;
            font-size: 0.9rem;
        }
        
        .btn-action {
            padding: 6px 10px;
            font-size: 0.75rem;
        }
    }
    
    @media (max-width: 480px) {
        .page-header-left h1 {
            font-size: 1.2rem;
        }
        
        .stats-badge {
            font-size: 0.85rem;
        }
        
        .stats-badge-value {
            font-size: 1.2rem;
        }
        
        .section-header {
            padding: 12px 16px;
        }
        
        .empty-state {
            padding: 40px 20px;
        }
        
        .empty-state-icon {
            width: 60px;
            height: 60px;
            font-size: 2rem;
        }
    }
    
    /* === UTILITY === */
    .fade {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .fade.show {
        opacity: 1;
    }
</style>

<div class="theoretical-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>Theoretical Completion</h1>
            <p>Mark students as passed theoretical to unlock practical courses</p>
        </div>
        <div class="page-header-right">
            <div class="stats-badge">
                <div class="stats-badge-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 18px; height: 18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div class="stats-badge-content">
                    <div class="stats-badge-label">Pending</div>
                    <div class="stats-badge-value">{{ $enrollments->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tabs-container">
        <div class="tabs">
            <button type="button" class="tab active" data-tab="pending">
                Pending Completion
            </button>
            <button type="button" class="tab" data-tab="passed">
                Passed Students
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tab Content Sections -->
    <div class="tab-content-wrapper">
        <!-- Pending Completion Tab Content -->
        <div class="tab-content active" data-content="pending">
            <div class="content-section">
        <div class="section-header">
            <div>
                <h2 class="section-title">Students Awaiting Theoretical Completion</h2>
                <p class="section-subtitle">Review and mark students who have completed their theoretical requirements</p>
            </div>
        </div>

        <div class="table-wrapper">
            @if($enrollments->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Enrolled</th>
                            <th>Hours Completed</th>
                            <th>Progress</th>
                            <th>Sessions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments as $enrollment)
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ substr($enrollment->student->name ?? 'N', 0, 1) }}</div>
                                        <div class="user-info">
                                            <div class="user-name">{{ $enrollment->student->name ?? 'N/A' }}</div>
                                            <div class="user-email">{{ $enrollment->student->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="course-info">
                                        <div class="course-title">{{ $enrollment->course->title }}</div>
                                        <div class="course-badges">
                                            <span class="badge badge-info">{{ ucfirst($enrollment->course->course_type) }}</span>
                                            <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <div class="hours-info">
                                        <div class="hours-value">{{ number_format($enrollment->total_hours, 1) }} hrs</div>
                                        <div class="hours-label">of {{ number_format($enrollment->course->hours_required, 1) }} required</div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $percentage = $enrollment->course->hours_required > 0 
                                            ? ($enrollment->total_hours / $enrollment->course->hours_required) * 100 
                                            : 0;
                                        $percentage = min(100, $percentage);
                                    @endphp
                                    <div class="progress-wrapper">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar-fill" style="width: {{ $percentage }}%;"></div>
                                        </div>
                                        <span class="progress-text">{{ number_format($percentage, 0) }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ $enrollment->sessionCompletions->count() }} sessions</span>
                                </td>
                                <td>
                                    <a href="{{ route('schools.admin.theoretical.show', ['school' => $school->slug, 'enrollment' => $enrollment->id]) }}" 
                                       class="btn-action btn-view">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon"></div>
                    <div class="empty-state-text">All students have been marked as passed! No pending theoretical completions.</div>
                </div>
            @endif
        </div>
        
        @if($enrollments->count() > 0)
            <div class="pagination-wrapper">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>
        </div>
        <!-- End Pending Completion Tab Content -->
        
        <!-- Passed Students Tab Content -->
        <div class="tab-content" data-content="passed">
            <div class="content-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Passed Students</h2>
                        <p class="section-subtitle">Students who have successfully completed theoretical training</p>
                    </div>
                </div>

                <!-- Stats Grid for Passed Students -->
                <div class="stats-mini-grid">
                    <div class="stat-mini">
                        <div class="stat-mini-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-mini-content">
                            <div class="stat-mini-value">{{ $totalPassed ?? 0 }}</div>
                            <div class="stat-mini-label">Total Passed</div>
                        </div>
                    </div>
                    
                    <div class="stat-mini">
                        <div class="stat-mini-icon primary">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-mini-content">
                            <div class="stat-mini-value">{{ $passedThisMonth ?? 0 }}</div>
                            <div class="stat-mini-label">This Month</div>
                        </div>
                    </div>
                </div>

                <div class="table-wrapper">
                    @if($passedStudents->count() > 0)
                        <table>
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Course</th>
                                    <th>Completion Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($passedStudents as $student)
                                    <tr>
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar">{{ substr($student->name ?? 'N', 0, 1) }}</div>
                                                <div class="user-info">
                                                    <div class="user-name">{{ $student->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $student->email ?? 'N/A' }}</td>
                                        <td>
                                            @if($student->enrollments->count() > 0)
                                                @foreach($student->enrollments->take(2) as $enrollment)
                                                    <span class="badge badge-info">{{ $enrollment->course->title ?? 'N/A' }}</span>
                                                @endforeach
                                            @else
                                                <span class="badge badge-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $student->updated_at ? $student->updated_at->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('schools.admin.students.show', ['school' => $school->slug, 'student' => $student->id]) }}" 
                                               class="btn-action btn-view">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon"></div>
                            <div class="empty-state-text">No students have passed theoretical training yet.</div>
                        </div>
                    @endif
                </div>
                
                @if($passedStudents->count() > 0)
                    <div class="pagination-wrapper">
                        {{ $passedStudents->links() }}
                    </div>
                @endif
            </div>
        </div>
        <!-- End Passed Students Tab Content -->
    </div>
    <!-- End Tab Content Wrapper -->
</div>

@push('scripts')
<script>
(function() {
    // Get all tab buttons
    const tabButtons = document.querySelectorAll('.tab[data-tab]');
    const tabContents = document.querySelectorAll('.tab-content[data-content]');
    
    // Add click event listeners to all tabs
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding content
            const targetContent = document.querySelector(`.tab-content[data-content="${targetTab}"]`);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
})();
</script>
@endpush

@endsection
