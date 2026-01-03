@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Enrollments')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $school->schoolSetting->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="mb-1">My Enrollments</h2>
        <p class="text-muted mb-0">Track your course progress and access learning materials</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Theoretical Status Banner -->
    @if($student && $student->hasPassedTheoretical())
        <div class="alert alert-success mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-3 me-3"></i>
                <div>
                    <h6 class="mb-1">Theoretical Training Complete!</h6>
                    <p class="mb-0 small">You've passed theoretical training on {{ $student->theoretical_passed_at ? $student->theoretical_passed_at->format('M d, Y') : 'N/A' }}. You can now enroll in practical courses.</p>
                </div>
            </div>
        </div>
    @endif

    @if($enrollments->count() > 0)
        <div class="row g-4">
            @foreach($enrollments as $enrollment)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <!-- Course Header -->
                        <div class="card-header bg-white border-0 pt-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="flex-grow-1">
                                    <h5 class="mb-2">{{ $enrollment->course->title }}</h5>
                                    <div class="mb-2">
                                        <span class="badge {{ $enrollment->course->course_type == 'theoretical' ? 'bg-info' : 'bg-primary' }}">
                                            {{ ucfirst($enrollment->course->course_type) }}
                                        </span>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}
                                        </span>
                                    </div>
                                </div>
                                <span class="badge {{ $enrollment->status == 'active' ? 'bg-success' : ($enrollment->status == 'completed' ? 'bg-primary' : 'bg-secondary') }}">
                                    {{ ucfirst($enrollment->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Progress Bar -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted">Progress</small>
                                    <small class="fw-bold">{{ number_format($enrollment->completion_percentage, 0) }}%</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $enrollment->completion_percentage }}%; background: {{ $primaryColor }};"
                                         aria-valuenow="{{ $enrollment->completion_percentage }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="bg-light rounded p-2 text-center">
                                        <div class="fw-bold">{{ number_format($enrollment->total_hours, 1) }}</div>
                                        <small class="text-muted">Hours Done</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded p-2 text-center">
                                        <div class="fw-bold">{{ number_format($enrollment->course->hours_required, 1) }}</div>
                                        <small class="text-muted">Total Required</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Theoretical Status for this enrollment -->
                            @if($enrollment->course->course_type == 'theoretical')
                                @if($enrollment->theoretical_passed)
                                    <div class="alert alert-success py-2 mb-3">
                                        <i class="fas fa-check-circle me-1"></i>
                                        <small>Marked as Passed on {{ $enrollment->theoretical_passed_at ? $enrollment->theoretical_passed_at->format('M d, Y') : 'N/A' }}</small>
                                    </div>
                                @else
                                    <div class="alert alert-warning py-2 mb-3">
                                        <i class="fas fa-hourglass-half me-1"></i>
                                        <small>Pending theoretical completion</small>
                                    </div>
                                @endif
                            @endif

                            <!-- Sessions Count -->
                            <div class="d-flex align-items-center text-muted mb-3">
                                <i class="fas fa-calendar-check me-2"></i>
                                <small>{{ $enrollment->sessionCompletions->count() }} sessions completed</small>
                            </div>

                            <!-- Enrolled Date -->
                            <div class="d-flex align-items-center text-muted mb-3">
                                <i class="fas fa-clock me-2"></i>
                                <small>Enrolled {{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('M d, Y') : 'N/A' }}</small>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer bg-white border-0 pb-3">
                            <div class="d-grid gap-2">
                                <a href="{{ route('schools.student.enrollments.show', [$school, $enrollment]) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                @if($enrollment->course->modules->count() > 0)
                                    <a href="{{ route('schools.student.courses.modules.index', [$school, $enrollment->course]) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-book me-1"></i>Course Materials
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-graduation-cap text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-muted mb-2">No Active Enrollments</h5>
                <p class="text-muted mb-4">You don't have any active course enrollments yet.</p>
                <a href="{{ route('schools.student.courses.index', $school) }}" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Browse Courses
                </a>
            </div>
        </div>
    @endif
</div>

<style>
    .progress {
        border-radius: 10px;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    .btn-sm {
        padding: 8px 16px;
        font-size: 13px;
    }
    
    .badge {
        font-weight: 500;
        font-size: 11px;
    }
</style>
@endsection
