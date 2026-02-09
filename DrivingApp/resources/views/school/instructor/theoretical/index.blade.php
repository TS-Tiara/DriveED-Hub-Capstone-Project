@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('page-title', 'Theoretical Completion - Instructor')

@section('content')
@php
    $schoolRoute = function($routeName, $params = []) use ($school) {
        return route('schools.' . $routeName, array_merge(['school' => $school->slug], $params));
    };
@endphp

<div class="user-management-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="page-title-group">
                <h1 class="page-title">
                    <i class="fas fa-graduation-cap me-2"></i>
                    Theoretical Completion
                </h1>
                <p class="page-subtitle">Mark students as passed theoretical training</p>
            </div>
        </div>
    </div>

    <!-- Students Pending Completion -->
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Students Pending Theoretical Completion</h2>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Hours Completed</th>
                        <th>Progress</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    {{ strtoupper(substr($enrollment->student->name, 0, 1)) }}
                                </div>
                                <span class="user-name">{{ $enrollment->student->name }}</span>
                            </div>
                        </td>
                        <td>{{ $enrollment->course->title ?? $enrollment->course->course_name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-primary">
                                {{ $enrollment->total_hours ?? 0 }} hours
                            </span>
                        </td>
                        <td>
                            @php
                                $requiredHours = $enrollment->course->theoretical_hours ?? 15;
                                $completedHours = $enrollment->total_hours ?? 0;
                                $progress = min(100, round(($completedHours / $requiredHours) * 100));
                            @endphp
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $progress }}%"
                                     aria-valuenow="{{ $progress }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $progress }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ $schoolRoute('instructor.theoretical.show', ['enrollment' => $enrollment->id]) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No pending theoretical completions</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($enrollments->hasPages())
        <div class="table-footer">
            <div class="pagination-info">
                Showing {{ $enrollments->firstItem() }} to {{ $enrollments->lastItem() }} of {{ $enrollments->total() }} enrollments
            </div>
            {{ $enrollments->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
