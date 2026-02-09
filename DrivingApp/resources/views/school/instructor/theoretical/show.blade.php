@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('page-title', 'Theoretical Completion Details - Instructor')

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
                    <h2 class="h4 mb-1">Theoretical Completion Details</h2>
                    <p class="text-muted mb-0">Review and mark student as passed</p>
                </div>
                <a href="{{ $schoolRoute('instructor.theoretical.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
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
                    <p><strong>Course:</strong> {{ $enrollment->course->title ?? $enrollment->course->course_name ?? 'N/A' }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $enrollment->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($enrollment->status) }}
                        </span>
                    </p>
                    <p class="mb-0"><strong>Enrolled:</strong> {{ $enrollment->enrolled_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Progress Card -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-chart-line me-2"></i>Training Progress
                    </h5>
                    @php
                        $requiredHours = $enrollment->course->theoretical_hours ?? 15;
                        $completedHours = $enrollment->total_hours ?? 0;
                        $progress = min(100, round(($completedHours / $requiredHours) * 100));
                    @endphp
                    <div class="row text-center mb-3">
                        <div class="col-md-4">
                            <h3 class="text-primary">{{ $completedHours }}</h3>
                            <p class="text-muted mb-0">Hours Completed</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-secondary">{{ $requiredHours }}</h3>
                            <p class="text-muted mb-0">Required Hours</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-success">{{ $progress }}%</h3>
                            <p class="text-muted mb-0">Progress</p>
                        </div>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : 'primary' }}" 
                             role="progressbar" 
                             style="width: {{ $progress }}%"
                             aria-valuenow="{{ $progress }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ $progress }}%
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
                                    <th>Hours</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enrollment->sessionCompletions as $session)
                                <tr>
                                    <td>{{ $session->session_date->format('M d, Y') }}</td>
                                    <td>{{ $session->session_time }}</td>
                                    <td>{{ $session->hours_completed }}h</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ ucfirst($session->session_type) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No sessions recorded</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mark as Passed Section -->
    @if(!$enrollment->student->has_passed_theoretical)
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h5 class="card-title text-success mb-3">
                        <i class="fas fa-check-circle me-2"></i>Mark as Passed
                    </h5>
                    <p>Once the student has completed all theoretical training requirements, you can mark them as passed.</p>
                    <form action="{{ $schoolRoute('instructor.theoretical.pass', ['enrollment' => $enrollment->id]) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to mark this student as passed theoretical training?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Mark as Passed
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        This student has already been marked as passed on {{ $enrollment->student->theoretical_passed_at->format('M d, Y') }}
    </div>
    @endif
</div>

@endsection
