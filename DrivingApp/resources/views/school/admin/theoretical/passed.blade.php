@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('page-title', 'Passed Students - Theoretical Completion')

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
                    Passed Students
                </h1>
                <p class="page-subtitle">Students who have completed theoretical training</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalPassed ?? 0 }}</div>
                <div class="stat-label">Total Passed</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $passedThisMonth ?? 0 }}</div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $avgHours ?? 0 }}</div>
                <div class="stat-label">Avg. Hours</div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Passed Students List</h2>
            <div class="table-actions">
                <input type="text" class="form-control search-input" placeholder="Search students..." id="searchInput">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Total Hours</th>
                        <th>Date Passed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($passedStudents as $student)
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                                <span class="user-name">{{ $student->name }}</span>
                            </div>
                        </td>
                        <td>{{ $student->email }}</td>
                        <td>
                            <span class="badge bg-primary">
                                {{ $student->total_theoretical_hours ?? 0 }} hours
                            </span>
                        </td>
                        <td>{{ optional($student->theoretical_passed_at)->format('M d, Y') ?? 'N/A' }}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ $schoolRoute('admin.students.show', ['student' => $student->id]) }}" 
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
                            <p>No passed students yet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($passedStudents) && $passedStudents->hasPages())
        <div class="table-footer">
            <div class="pagination-info">
                Showing {{ $passedStudents->firstItem() }} to {{ $passedStudents->lastItem() }} of {{ $passedStudents->total() }} students
            </div>
            {{ $passedStudents->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
@endpush

@endsection
