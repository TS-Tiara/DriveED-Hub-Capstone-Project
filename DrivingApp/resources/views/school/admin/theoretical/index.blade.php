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
        font-size: 0.95rem;
        margin-top: 5px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 4px solid {{ $primaryColor }};
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: {{ $primaryColor }};
        border-radius: 50%;
        opacity: 0.1;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .stat-card:hover::before {
        transform: scale(1.2);
        opacity: 0.15;
    }
    
    .stat-content {
        position: relative;
        z-index: 1;
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }
    
    .stat-value {
        font-size: 2.25rem;
        font-weight: 700;
        color: #111827;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-detail {
        font-size: 0.875rem;
        color: #6b7280;
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
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
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
    
    .btn-action {
        padding: 6px 12px;
        margin: 0 3px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-view {
        background: #17a2b8;
        color: white;
    }
    
    .btn-view:hover {
        background: #138496;
        transform: scale(1.05);
        color: white;
    }
    
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }
    
    .empty-state-icon::before {
        content: "📋";
    }
    
    .empty-state-text {
        font-size: 1.1rem;
        color: #6b7280;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 12px;
        vertical-align: middle;
    }
    
    .user-info {
        display: inline-block;
        vertical-align: middle;
    }
    
    .user-name {
        font-weight: 600;
        color: #1f2937;
        display: block;
    }
    
    .user-email {
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
        margin-right: 5px;
    }
    
    .badge-info {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .badge-secondary {
        background: #e2e3e5;
        color: #383d41;
    }
    
    .badge-success {
        background: #d4edda;
        color: #155724;
    }
    
    .progress-bar-container {
        width: 120px;
        height: 8px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
        margin-right: 8px;
    }
    
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        border-radius: 10px;
        transition: width 0.3s ease;
    }
    
    .progress-text {
        font-size: 0.85rem;
        color: #6b7280;
        vertical-align: middle;
    }
</style>

<div class="user-management-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Theoretical Completion</h1>
            <p class="page-subtitle">Mark students as passed theoretical to unlock practical courses</p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tabs-container">
        <div class="tabs">
            <a href="{{ route('schools.admin.theoretical.index', ['school' => $school->slug]) }}" class="tab active">
                Pending Completion
            </a>
            <a href="{{ route('schools.admin.theoretical.passed', ['school' => $school->slug]) }}" class="tab">
                Passed Students
            </a>
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

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending Completion</div>
                        <div class="stat-value">{{ $enrollments->total() }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">Students awaiting theoretical pass</div>
            </div>
        </div>
    </div>

    <!-- Enrollments Table -->
    <div class="table-container">
        @if($enrollments->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Enrolled Date</th>
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
                                <div class="user-avatar">{{ substr($enrollment->student->name ?? 'N', 0, 1) }}</div>
                                <div class="user-info">
                                    <span class="user-name">{{ $enrollment->student->name ?? 'N/A' }}</span>
                                    <span class="user-email">{{ $enrollment->student->email ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $enrollment->course->title }}</strong><br>
                                <span class="badge badge-info">{{ ucfirst($enrollment->course->course_type) }}</span>
                                <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}</span>
                            </td>
                            <td>{{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <strong>{{ number_format($enrollment->total_hours, 1) }} hrs</strong><br>
                                <small style="color: #6b7280;">of {{ number_format($enrollment->course->hours_required, 1) }} required</small>
                            </td>
                            <td>
                                @php
                                    $percentage = $enrollment->course->hours_required > 0 
                                        ? ($enrollment->total_hours / $enrollment->course->hours_required) * 100 
                                        : 0;
                                    $percentage = min(100, $percentage);
                                @endphp
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: {{ $percentage }}%;"></div>
                                </div>
                                <span class="progress-text">{{ number_format($percentage, 0) }}%</span>
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
                <div class="empty-state-text">No students pending theoretical completion. All students have been marked as passed!</div>
            </div>
        @endif
    </div>
    
    @if($enrollments->count() > 0)
        <div style="margin-top: 20px;">
            {{ $enrollments->links() }}
        </div>
    @endif
</div>
@endsection
