@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('page-title', 'Enrollment Details - Admin')

@section('content')
@php
    $schoolRoute = function($routeName, $params = []) use ($school) {
        return route('schools.' . $routeName, array_merge(['school' => $school->slug], $params));
    };
@endphp

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1">Enrollment Details</h2>
                    <p class="text-muted mb-0">Complete enrollment information</p>
                </div>
                <a href="{{ $schoolRoute('admin.enrollments.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Enrollments
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Student Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-user me-2"></i>Student Information
                    </h5>
                    <div class="mb-3 text-center">
                        <div class="user-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ strtoupper(substr($enrollment->student->name, 0, 1)) }}
                        </div>
                        <h6>{{ $enrollment->student->name }}</h6>
                        <p class="text-muted mb-0">{{ $enrollment->student->email }}</p>
                    </div>
                    <hr>
                    <p><strong>Student Type:</strong> {{ ucfirst(str_replace('_', ' ', $enrollment->student->student_type)) }}</p>
                    <p><strong>License Type:</strong> {{ ucfirst(str_replace('_', ' ', $enrollment->student->license_type)) }}</p>
                    <p class="mb-0">
                        <strong>Theoretical:</strong>
                        <span class="badge bg-{{ $enrollment->student->has_passed_theoretical ? 'success' : 'warning' }}">
                            {{ $enrollment->student->has_passed_theoretical ? 'Passed' : 'Pending' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Course & Enrollment Info -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-book me-2"></i>Course Information
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Course:</strong> {{ $enrollment->course->course_name }}</p>
                            <p><strong>Type:</strong> {{ ucfirst($enrollment->course->course_type) }}</p>
                            <p><strong>License Type:</strong> {{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong>
                                <span class="badge bg-{{ $enrollment->status === 'active' ? 'success' : ($enrollment->status === 'completed' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($enrollment->status) }}
                                </span>
                            </p>
                            <p><strong>Enrolled:</strong> {{ $enrollment->enrolled_at->format('M d, Y') }}</p>
                            @if($enrollment->completed_at)
                            <p><strong>Completed:</strong> {{ $enrollment->completed_at->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Stats -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-chart-line me-2"></i>Progress Statistics
                    </h5>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4 class="text-primary">{{ $enrollment->sessionCompletions->count() }}</h4>
                            <p class="text-muted mb-0">Total Sessions</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-success">{{ $enrollment->sessionCompletions->sum('hours_completed') }}</h4>
                            <p class="text-muted mb-0">Hours Completed</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info">{{ $enrollment->course->theoretical_hours ?? 0 }}</h4>
                            <p class="text-muted mb-0">Required Hours</p>
                        </div>
                        <div class="col-md-3">
                            @php
                                $progress = $enrollment->course->theoretical_hours ? 
                                    min(100, round(($enrollment->sessionCompletions->sum('hours_completed') / $enrollment->course->theoretical_hours) * 100)) : 0;
                            @endphp
                            <h4 class="text-warning">{{ $progress }}%</h4>
                            <p class="text-muted mb-0">Progress</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session History -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-history me-2"></i>Session History
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Instructor</th>
                                    <th>Hours</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enrollment->sessionCompletions as $session)
                                <tr>
                                    <td>{{ $session->session_date->format('M d, Y') }}</td>
                                    <td>{{ $session->session_time }}</td>
                                    <td>{{ $session->instructor->name }}</td>
                                    <td>{{ $session->hours_completed }}h</td>
                                    <td>
                                        <span class="badge bg-{{ $session->session_type === 'theoretical' ? 'info' : 'primary' }}">
                                            {{ ucfirst($session->session_type) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No sessions recorded yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    @if($enrollment->status === 'active')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Actions</h5>
                    <div class="d-flex gap-2">
                        <form action="{{ $schoolRoute('admin.enrollments.complete', ['enrollment' => $enrollment->id]) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to mark this enrollment as complete?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Mark as Complete
                            </button>
                        </form>

                        <form action="{{ $schoolRoute('admin.enrollments.cancel', ['enrollment' => $enrollment->id]) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to cancel this enrollment?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times me-2"></i>Cancel Enrollment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection
