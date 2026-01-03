@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Theoretical Completion Management')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $school->schoolSetting->primary_color ?? '#667eea';
    $secondaryColor = $school->schoolSetting->secondary_color ?? '#764ba2';
    
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
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        padding: 20px;
        border-radius: 12px;
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-bottom: 8px;
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .data-table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .table-header {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: white;
        padding: 20px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .table-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin: 0;
    }
</style>

<div class="user-management-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Theoretical Completion</h1>
            <p class="page-subtitle">Mark students as passed theoretical to unlock practical courses</p>
        </div>
        <a href="{{ route('schools.admin.theoretical.passed', ['school' => $school->slug]) }}" class="btn btn-primary">
            <i class="fas fa-check-circle me-2"></i>View Passed Students
        </a>
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
            <div class="stat-label">Pending Completion</div>
            <div class="stat-value">{{ $enrollments->total() }}</div>
            <div class="stat-breakdown">Students awaiting theoretical pass</div>
        </div>
    </div>

    <!-- Enrollments Table -->
    <div class="data-table-container">
        <div class="table-header">
            <h2 class="table-title">Students Pending Theoretical Completion</h2>
        </div>
            @if($enrollments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Enrolled Date</th>
                                <th>Hours Completed</th>
                                <th>Progress</th>
                                <th>Sessions</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enrollments as $enrollment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <span class="text-primary fw-bold">{{ substr($enrollment->student->name ?? 'N', 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $enrollment->student->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $enrollment->student->email ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $enrollment->course->title }}</div>
                                        <small class="text-muted">
                                            <span class="badge bg-info">{{ ucfirst($enrollment->course->course_type) }}</span>
                                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <small>{{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('M d, Y') : 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ number_format($enrollment->total_hours, 1) }} hrs</div>
                                        <small class="text-muted">of {{ number_format($enrollment->course->hours_required, 1) }} required</small>
                                    </td>
                                    <td>
                                        @php
                                            $percentage = $enrollment->course->hours_required > 0 
                                                ? ($enrollment->total_hours / $enrollment->course->hours_required) * 100 
                                                : 0;
                                            $percentage = min(100, $percentage);
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: {{ $percentage }}%; background: {{ $primaryColor }};"
                                                 aria-valuenow="{{ $percentage }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ number_format($percentage, 0) }}%</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $enrollment->sessionCompletions->count() }} sessions</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('schools.admin.theoretical.show', ['school' => $school->slug, 'enrollment' => $enrollment->id]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i>Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3 bg-light border-top">
                    {{ $enrollments->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox text-muted mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-muted">No Pending Theoretical Completions</h5>
                    <p class="text-muted mb-0">All theoretical students have been marked as passed or there are no active theoretical enrollments.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }
    
    .badge {
        font-weight: 500;
        font-size: 11px;
        padding: 4px 8px;
    }
</style>
@endsection
