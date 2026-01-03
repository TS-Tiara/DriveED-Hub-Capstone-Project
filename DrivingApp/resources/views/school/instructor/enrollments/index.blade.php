@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('page-title', 'Student Enrollments - Instructor')

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
                    <i class="fas fa-user-graduate me-2"></i>
                    Student Enrollments
                </h1>
                <p class="page-subtitle">View enrollments for students you've taught</p>
            </div>
        </div>
    </div>

    <!-- Enrollments Table -->
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Enrollments List</h2>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Sessions</th>
                        <th>Hours</th>
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
                                <span class="user-name">
                                    {{ $enrollment->student->name }}
                                </span>
                            </div>
                        </td>
                        <td>{{ $enrollment->course->course_name }}</td>
                        <td>
                            <span class="badge bg-{{ $enrollment->status === 'active' ? 'success' : ($enrollment->status === 'completed' ? 'primary' : 'secondary') }}">
                                {{ ucfirst($enrollment->status) }}
                            </span>
                        </td>
                        <td>{{ $enrollment->sessionCompletions->count() }}</td>
                        <td>
                            <span class="badge bg-primary">
                                {{ $enrollment->sessionCompletions->sum('hours_completed') }}h
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ $schoolRoute('instructor.enrollments.show', ['enrollment' => $enrollment->id]) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No enrollments found</p>
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
