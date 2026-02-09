@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Review Theoretical Completion')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolRoute = function($routeName, $params = []) use ($school) {
        return route('schools.' . $routeName, array_merge(['school' => $school->slug], $params));
    };
    
    $totalHours = $enrollment->sessionCompletions->sum('hours_completed');
    $requiredHours = $enrollment->course->theoretical_hours ?? 15;
    $progress = $requiredHours > 0 ? min(100, round(($totalHours / $requiredHours) * 100)) : 0;
    $primaryColor = $settings->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .theoretical-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1400px;
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
    
    .info-card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
    }
    
    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f3f4f6;
    }
    
    .info-row {
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid #f9fafb;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 500;
        color: #6b7280;
        min-width: 130px;
    }
    
    .info-value {
        color: #1f2937;
    }
</style>

<div class="theoretical-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Review Theoretical Completion</h1>
            <p class="page-subtitle">{{ $enrollment->student->name }} - {{ $enrollment->course->title ?? 'N/A' }}</p>
        </div>
        <a href="{{ $schoolRoute('admin.theoretical.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-md-4">
            <!-- Student Info -->
            <div class="info-card">
                <h5 class="card-title"><i class="fas fa-user me-2"></i>Student Information</h5>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $enrollment->student->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $enrollment->student->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Student Type:</span>
                    <span class="info-value">
                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $enrollment->student->student_type)) }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">License Type:</span>
                    <span class="info-value">
                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $enrollment->student->license_type)) }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Enrolled:</span>
                    <span class="info-value">{{ $enrollment->enrolled_at->format('M d, Y') }}</span>
                </div>
            </div>

            <!-- Course Info -->
            <div class="info-card">
                <h5 class="card-title"><i class="fas fa-book me-2"></i>Course Details</h5>
                <div class="info-row">
                    <span class="info-label">Course:</span>
                    <span class="info-value fw-bold">{{ $enrollment->course->title ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value">
                        <span class="badge bg-primary">{{ ucfirst($enrollment->course->course_type) }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">License:</span>
                    <span class="info-value">
                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Required Hours:</span>
                    <span class="info-value fw-bold">{{ $requiredHours }} hours</span>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-8">
            <!-- Progress -->
            <div class="info-card">
                <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Training Progress</h5>
                <div class="row text-center mb-3">
                    <div class="col-md-4">
                        <h3 class="mb-0">{{ $totalHours }}</h3>
                        <small class="text-muted">Hours Completed</small>
                    </div>
                    <div class="col-md-4">
                        <h3 class="mb-0">{{ $requiredHours }}</h3>
                        <small class="text-muted">Required Hours</small>
                    </div>
                    <div class="col-md-4">
                        <h3 class="mb-0 text-{{ $progress >= 100 ? 'success' : 'warning' }}">{{ $progress }}%</h3>
                        <small class="text-muted">Progress</small>
                    </div>
                </div>
                <div class="progress" style="height: 30px;">
                    <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : 'primary' }}" 
                         style="width: {{ $progress }}%">
                        {{ $progress }}%
                    </div>
                </div>
            </div>

            <!-- Session History -->
            <div class="info-card">
                <h5 class="card-title"><i class="fas fa-history me-2"></i>Session History</h5>
                @if($enrollment->sessionCompletions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Instructor</th>
                                    <th>Hours</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollment->sessionCompletions->sortByDesc('session_date') as $session)
                                    <tr>
                                        <td>{{ $session->session_date->format('M d, Y') }}</td>
                                        <td>{{ $session->session_time }}</td>
                                        <td>{{ $session->instructor->name }}</td>
                                        <td><span class="badge bg-success">{{ $session->hours_completed }}h</span></td>
                                        <td><span class="badge bg-info">{{ ucfirst($session->session_type) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3 mb-0">No sessions recorded yet</p>
                @endif
            </div>

            <!-- Mark as Passed -->
            <div class="info-card">
                <h5 class="card-title">
                    <i class="fas fa-{{ $validation['allowed'] ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
                    Mark as Passed
                </h5>
                @if($validation['allowed'])
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle me-2"></i>{{ $validation['message'] }}
                    </div>
                    <form action="{{ $schoolRoute('admin.theoretical.markAsPassed') }}" 
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to mark this student as passed theoretical training?');">
                        @csrf
                        <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">
                        
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Add any additional notes..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check-circle me-2"></i>Mark as Passed Theoretical
                        </button>
                    </form>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ $validation['message'] }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
