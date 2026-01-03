@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('page-title', 'My Session Logs - Instructor')

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
                    <i class="fas fa-clipboard-list me-2"></i>
                    My Session Logs
                </h1>
                <p class="page-subtitle">View and manage your logged driving sessions</p>
            </div>
            <a href="{{ $schoolRoute('instructor.sessions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Log New Session
            </a>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Session History</h2>
            <div class="table-actions">
                <select class="form-select" id="filterType">
                    <option value="">All Types</option>
                    <option value="theoretical">Theoretical</option>
                    <option value="practical">Practical</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Type</th>
                        <th>Hours</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr>
                        <td>
                            <div>
                                <strong>{{ $session->session_date->format('M d, Y') }}</strong><br>
                                <small class="text-muted">{{ $session->session_time }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    {{ strtoupper(substr($session->enrollment->student->name, 0, 1)) }}
                                </div>
                                <span class="user-name">
                                    {{ $session->enrollment->student->name }}
                                </span>
                            </div>
                        </td>
                        <td>{{ $session->enrollment->course->course_name }}</td>
                        <td>
                            <span class="badge bg-{{ $session->session_type === 'theoretical' ? 'info' : 'primary' }}">
                                {{ ucfirst($session->session_type) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success">
                                {{ $session->hours_completed }}h
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ $schoolRoute('instructor.sessions.show', ['sessionCompletion' => $session->id]) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ $schoolRoute('instructor.sessions.edit', ['sessionCompletion' => $session->id]) }}" 
                                   class="btn btn-sm btn-outline-warning"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No sessions logged yet</p>
                            <a href="{{ $schoolRoute('instructor.sessions.create') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus me-2"></i>Log Your First Session
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
        <div class="table-footer">
            <div class="pagination-info">
                Showing {{ $sessions->firstItem() }} to {{ $sessions->lastItem() }} of {{ $sessions->total() }} sessions
            </div>
            {{ $sessions->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.getElementById('filterType').addEventListener('change', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const type = row.querySelector('.badge')?.textContent.toLowerCase() || '';
        row.style.display = (!filter || type.includes(filter)) ? '' : 'none';
    });
});
</script>
@endpush

@endsection
