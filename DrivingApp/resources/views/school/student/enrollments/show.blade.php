@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Enrollment Details')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $school->schoolSetting->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<div class="container-fluid p-4">
    <!-- Header with Back Button -->
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="mb-1">{{ $enrollment->course->title }}</h2>
            <p class="text-muted mb-0">Enrollment Details & Course Materials</p>
        </div>
        <a href="{{ route('schools.student.enrollments.index', $school) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Enrollments
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Course Info & Progress -->
        <div class="col-lg-8">
            <!-- Course Overview Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <h5 class="mb-2">{{ $enrollment->course->title }}</h5>
                            <div class="mb-3">
                                <span class="badge {{ $enrollment->course->course_type == 'theoretical' ? 'bg-info' : 'bg-primary' }} me-2">
                                    {{ ucfirst($enrollment->course->course_type) }}
                                </span>
                                <span class="badge bg-secondary">
                                    {{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}
                                </span>
                            </div>
                        </div>
                        <span class="badge {{ $enrollment->status == 'active' ? 'bg-success' : ($enrollment->status == 'completed' ? 'bg-primary' : 'bg-secondary') }} fs-6">
                            {{ ucfirst($enrollment->status) }}
                        </span>
                    </div>

                    @if($enrollment->course->description)
                        <p class="text-muted mb-0">{{ $enrollment->course->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Progress Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0">Your Progress</h5>
                </div>
                <div class="card-body">
                    <!-- Overall Progress -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Overall Completion</span>
                            <span class="fw-bold" style="color: {{ $primaryColor }}">{{ number_format($enrollment->completion_percentage, 0) }}%</span>
                        </div>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $enrollment->completion_percentage }}%; background: {{ $primaryColor }};"
                                 aria-valuenow="{{ $enrollment->completion_percentage }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="bg-light rounded p-3 text-center">
                                <div class="h3 mb-1">{{ number_format($enrollment->total_hours, 1) }}</div>
                                <small class="text-muted">Hours Completed</small>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="bg-light rounded p-3 text-center">
                                <div class="h3 mb-1">{{ number_format($enrollment->course->hours_required, 1) }}</div>
                                <small class="text-muted">Hours Required</small>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="bg-light rounded p-3 text-center">
                                <div class="h3 mb-1">{{ $enrollment->sessionCompletions->count() }}</div>
                                <small class="text-muted">Sessions Logged</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Modules -->
            @if($enrollment->course->modules->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="mb-0">Course Modules</h5>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="modulesAccordion">
                            @foreach($enrollment->course->modules->sortBy('order') as $index => $module)
                                <div class="accordion-item border-0 mb-3 shadow-sm">
                                    <h2 class="accordion-header" id="heading{{ $module->id }}">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $module->id }}" 
                                                aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" 
                                                aria-controls="collapse{{ $module->id }}">
                                            <div class="d-flex align-items-center w-100">
                                                <span class="badge bg-secondary me-3">Module {{ $module->order }}</span>
                                                <strong>{{ $module->title }}</strong>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $module->id }}" 
                                         class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" 
                                         aria-labelledby="heading{{ $module->id }}" 
                                         data-bs-parent="#modulesAccordion">
                                        <div class="accordion-body">
                                            @if($module->description)
                                                <p class="text-muted mb-3">{{ $module->description }}</p>
                                            @endif

                                            @if($module->lessons->count() > 0)
                                                <div class="list-group list-group-flush">
                                                    @foreach($module->lessons->sortBy('order') as $lesson)
                                                        <div class="list-group-item px-0">
                                                            <div class="d-flex align-items-start">
                                                                <i class="fas fa-book-open text-muted me-3 mt-1"></i>
                                                                <div class="flex-grow-1">
                                                                    <h6 class="mb-1">{{ $lesson->title }}</h6>
                                                                    @if($lesson->content)
                                                                        <p class="text-muted small mb-2">{{ Str::limit($lesson->content, 150) }}</p>
                                                                    @endif
                                                                    
                                                                    @if($lesson->video_url)
                                                                        <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                                                            <i class="fas fa-play me-1"></i>Watch Video
                                                                        </a>
                                                                    @endif
                                                                    
                                                                    @if($lesson->attachment_path)
                                                                        <a href="{{ Storage::url($lesson->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                            <i class="fas fa-download me-1"></i>Download Materials
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted mb-0"><em>No lessons available yet</em></p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-book text-muted mb-3" style="font-size: 3rem;"></i>
                        <h6 class="text-muted">No Modules Available</h6>
                        <p class="text-muted small mb-0">Course materials will be added soon</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Session History & Status -->
        <div class="col-lg-4">
            <!-- Theoretical Status (if applicable) -->
            @if($enrollment->course->course_type == 'theoretical')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        @if($enrollment->theoretical_passed)
                            <div class="text-center">
                                <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                                <h6 class="mb-2">Theoretical Complete!</h6>
                                <p class="text-muted small mb-0">Marked as passed on {{ $enrollment->theoretical_passed_at ? $enrollment->theoretical_passed_at->format('M d, Y') : 'N/A' }}</p>
                            </div>
                        @else
                            <div class="text-center">
                                <i class="fas fa-hourglass-half text-warning mb-3" style="font-size: 3rem;"></i>
                                <h6 class="mb-2">Theoretical In Progress</h6>
                                <p class="text-muted small mb-3">Complete all required hours and your instructor will mark you as passed</p>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                         style="width: {{ $enrollment->completion_percentage }}%"
                                         aria-valuenow="{{ $enrollment->completion_percentage }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Session History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h6 class="mb-0">Session History</h6>
                </div>
                <div class="card-body">
                    @if($enrollment->sessionCompletions->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($enrollment->sessionCompletions->sortByDesc('completed_at')->take(10) as $session)
                                <div class="list-group-item px-0 py-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <small class="text-muted d-block">{{ $session->completed_at ? $session->completed_at->format('M d, Y') : 'N/A' }}</small>
                                            <small class="text-muted">{{ $session->completed_at ? $session->completed_at->format('g:i A') : 'N/A' }}</small>
                                        </div>
                                        <span class="badge bg-primary">{{ number_format($session->hours_completed, 1) }} hrs</span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-user-tie me-1"></i>{{ $session->instructor->name ?? 'Instructor' }}
                                    </small>
                                    @if($session->notes)
                                        <p class="mb-0 mt-2 small text-muted"><em>{{ Str::limit($session->notes, 80) }}</em></p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times text-muted mb-2" style="font-size: 2rem;"></i>
                            <p class="text-muted small mb-0">No sessions logged yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .progress {
        border-radius: 10px;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    .accordion-button:not(.collapsed) {
        color: {{ $primaryColor }};
        background-color: rgba(102, 126, 234, 0.1);
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    
    .list-group-item {
        border-left: 0;
        border-right: 0;
    }
</style>
@endsection
