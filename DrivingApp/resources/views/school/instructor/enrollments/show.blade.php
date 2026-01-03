@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('page-title', 'Enrollment Details - Instructor')

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
                    <p class="text-muted mb-0">Student enrollment information</p>
                </div>
                <a href="{{ $schoolRoute('instructor.enrollments.index') }}" class="btn btn-outline-secondary">
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

        <!-- Course & Sessions -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-book me-2"></i>Course Information
                    </h5>
                    <p><strong>Course:</strong> {{ $enrollment->course->course_name }}</p>
                    <p><strong>Type:</strong> {{ ucfirst($enrollment->course->course_type) }}</p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-{{ $enrollment->status === 'active' ? 'success' : ($enrollment->status === 'completed' ? 'primary' : 'secondary') }}">
                            {{ ucfirst($enrollment->status) }}
                        </span>
                    </p>
                    <p class="mb-0"><strong>Enrolled:</strong> {{ $enrollment->enrolled_at->format('M d, Y') }}</p>
                </div>
            </div>

            <!-- Session History -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>My Sessions with this Student
                        </h5>
                        <a href="{{ $schoolRoute('instructor.sessions.create', ['enrollment_id' => $enrollment->id]) }}" 
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-2"></i>Log Session
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Hours</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enrollment->sessionCompletions as $session)
                                <tr>
                                    <td>{{ $session->session_date->format('M d, Y') }}</td>
                                    <td>{{ $session->session_time }}</td>
                                    <td>{{ $session->hours_completed }}h</td>
                                    <td>
                                        <span class="badge bg-{{ $session->session_type === 'theoretical' ? 'info' : 'primary' }}">
                                            {{ ucfirst($session->session_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ $schoolRoute('instructor.sessions.edit', ['sessionCompletion' => $session->id]) }}" 
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
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
                    
                    @if($enrollment->sessionCompletions->count() > 0)
                    <div class="mt-3">
                        <strong>Total Hours:</strong> 
                        <span class="badge bg-success">
                            {{ $enrollment->sessionCompletions->sum('hours_completed') }} hours
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
