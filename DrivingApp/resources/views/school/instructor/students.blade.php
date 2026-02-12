@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Students')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school?->schoolSetting;
    $instructorId = Auth::guard('instructor')->id();
    
    // Calculate statistics
    $totalStudents = $students->count();
    $myStudents = $students->where('is_assigned', true)->count();
    $activeStudents = $students->where('status', 'active')->count();
    $totalSessions = $students->sum('sessions_count');
    $avgProgress = $students->avg('avg_progress') ?? 0;
@endphp

<style>
    .students-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 4px solid {{ $settings->primary_color ?? '#667eea' }};
    }

    .page-title {
        font-size: 2rem;
        color: #111827;
        margin: 0;
        font-weight: 400;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-box {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .stat-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    .stat-box:nth-child(1)::before { background: #3b82f6; }
    .stat-box:nth-child(2)::before { background: {{ $settings->primary_color ?? '#10b981' }}; }
    .stat-box:nth-child(3)::before { background: #dc2626; }
    .stat-box:nth-child(4)::before { background: {{ $settings->primary_color ?? '#f59e0b' }}; }

    .stat-box:nth-child(1) .stat-value { color: #3b82f6; }
    .stat-box:nth-child(2) .stat-value { color: {{ $settings->primary_color ?? '#10b981' }}; }
    .stat-box:nth-child(3) .stat-value { color: #dc2626; }
    .stat-box:nth-child(4) .stat-value { color: {{ $settings->primary_color ?? '#f59e0b' }}; }

    .stat-label {
        font-size: 0.95rem;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
    }

    /* Controls Bar */
    .controls-bar {
        background: {{ $settings->primary_color ?? '#1e40af' }};
        padding: 20px 30px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .search-wrapper {
        flex: 1;
        min-width: 300px;
    }

    .search-input {
        width: 100%;
        padding: 12px 16px;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        outline: none;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .filter-label {
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .filter-select {
        padding: 10px 35px 10px 15px;
        border: none;
        border-radius: 8px;
        background: white;
        font-size: 0.95rem;
        cursor: pointer;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
    }

    .view-count {
        color: white;
        font-size: 0.9rem;
    }

    /* Students Grid */
    .students-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .student-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .student-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        border-color: {{ $settings->primary_color ?? '#f59e0b' }};
    }

    .student-card-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .student-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #1e293b;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .student-info {
        flex: 1;
        min-width: 0;
    }

    .student-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 4px;
    }

    .student-detail {
        font-size: 0.9rem;
        color: #6b7280;
        margin: 2px 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .student-detail strong {
        color: #374151;
        font-weight: 600;
    }

    .student-grade {
        font-size: 1.1rem;
        color: {{ $settings->primary_color ?? '#f59e0b' }};
        font-weight: 700;
        margin-top: 4px;
    }

    .assignment-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 6px;
    }

    .badge-assigned {
        background: rgba({{ hexdec(substr($settings->primary_color ?? '#1e40af', 1, 2)) }}, {{ hexdec(substr($settings->primary_color ?? '#1e40af', 3, 2)) }}, {{ hexdec(substr($settings->primary_color ?? '#1e40af', 5, 2)) }}, 0.15);
        color: {{ $settings->primary_color ?? '#1e40af' }};
    }

    .badge-unassigned {
        background: #f3f4f6;
        color: #6b7280;
    }

    .no-students {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .no-students-icon {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 15px;
    }

    .no-students-text {
        font-size: 1.25rem;
        color: #6b7280;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .students-container {
            padding: 15px;
        }

        .page-title {
            font-size: 1.75rem;
        }

        .stats-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .stat-box {
            padding: 10px;
            text-align: center;
        }

        .stat-label {
            font-size: 0.65rem;
        }

        .stat-value {
            font-size: 1.25rem;
        }

        .controls-bar {
            padding: 12px;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 8px;
        }

        .search-wrapper {
            flex: 1;
            min-width: 150px;
        }

        .search-input {
            padding: 8px 10px;
            font-size: 13px;
        }

        .filter-group {
            flex: 0 0 auto;
        }

        .filter-label {
            display: none;
        }

        .filter-select {
            padding: 8px 10px;
            font-size: 12px;
        }

        .view-count {
            width: 100%;
            text-align: center;
            font-size: 12px;
        }

        .students-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .student-card {
            padding: 12px;
        }

        .student-card-header {
            flex-direction: column;
            text-align: center;
            gap: 8px;
        }

        .student-avatar {
            width: 45px;
            height: 45px;
            font-size: 18px;
        }

        .student-info {
            text-align: center;
        }

        .student-name {
            font-size: 14px;
        }

        .student-detail {
            font-size: 11px;
            display: none;
        }

        .student-detail:first-of-type {
            display: block;
        }

        .student-grade {
            font-size: 12px;
        }

        .assignment-badge {
            font-size: 9px;
            padding: 2px 6px;
        }
    }

    @media (max-width: 480px) {
        .students-container {
            padding: 10px;
        }

        .page-title {
            font-size: 1.3rem;
        }

        .stats-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
        }

        .stat-box {
            padding: 8px 4px;
        }

        .stat-label {
            font-size: 0.55rem;
        }

        .stat-value {
            font-size: 1rem;
        }

        .controls-bar {
            padding: 10px;
            gap: 6px;
        }

        .search-input {
            padding: 6px 8px;
            font-size: 12px;
        }

        .filter-select {
            padding: 6px 8px;
            font-size: 11px;
        }

        .students-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .student-card {
            padding: 10px;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }

        .student-name {
            font-size: 13px;
        }

        .student-grade {
            font-size: 11px;
        }

        .assignment-badge {
            font-size: 8px;
            padding: 2px 5px;
        }
    }

    @media (max-width: 360px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .stat-box {
            padding: 8px;
        }

        .stat-label {
            font-size: 0.6rem;
        }

        .stat-value {
            font-size: 1.1rem;
        }
        
        .students-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
        }

        .student-card {
            padding: 8px;
        }

        .student-avatar {
            width: 35px;
            height: 35px;
            font-size: 14px;
        }

        .student-name {
            font-size: 12px;
        }
    }
</style>

<div class="students-container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">My Students</h1>
        <div class="export-dropdown" style="position: relative; display: inline-block;">
            <button onclick="this.nextElementSibling.classList.toggle('show')" style="padding: 10px 18px; background: #10b981; color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Export
            </button>
            <div class="export-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 4px; background: white; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.15); min-width: 180px; z-index: 100; overflow: hidden;">
                <a href="{{ route('schools.instructor.exports.students.pdf', $school) }}" style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; color: #374151; text-decoration: none; font-size: 0.875rem; font-weight: 500;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#ef4444" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                    Download PDF
                </a>
                <a href="{{ route('schools.instructor.exports.students.excel', $school) }}" style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; color: #374151; text-decoration: none; font-size: 0.875rem; font-weight: 500;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#10b981" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Download Excel
                </a>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-label">Total Students</div>
            <div class="stat-value">{{ $totalStudents }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">My Students</div>
            <div class="stat-value">{{ $myStudents }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Sessions</div>
            <div class="stat-value">{{ $totalSessions }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">AVG Progress</div>
            <div class="stat-value">{{ number_format($avgProgress, 0) }}</div>
        </div>
    </div>

    <div class="controls-bar">
        <div class="search-wrapper">
            <input type="text" class="search-input" id="searchInput" 
                   placeholder="Select students by name or email..." onkeyup="filterStudents()">
        </div>
        <div class="filter-group">
            <span class="filter-label">Assignment:</span>
            <select class="filter-select" id="assignmentFilter" onchange="filterStudents()">
                <option value="all">All Students</option>
                <option value="assigned">My Students</option>
                <option value="unassigned">Other Students</option>
            </select>
        </div>
        <div class="filter-group">
            <span class="filter-label">Status:</span>
            <select class="filter-select" id="statusFilter" onchange="filterStudents()">
                <option value="all">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="filter-group">
            <span class="filter-label">Sort:</span>
            <select class="filter-select" id="sortFilter" onchange="sortStudents()">
                <option value="assigned-first">My Students First</option>
                <option value="name-asc">Name (A-Z)</option>
                <option value="name-desc">Name (Z-A)</option>
                <option value="progress-desc">Progress (High-Low)</option>
                <option value="progress-asc">Progress (Low-High)</option>
            </select>
        </div>
        <div class="view-count">
            Showing <span id="showingCount">{{ $totalStudents }}</span> Students
        </div>
    </div>

    <div class="students-grid" id="studentsGrid">
        @forelse($students as $student)
            <div class="student-card" 
                 data-name="{{ strtolower($student->name) }}"
                 data-email="{{ strtolower($student->email) }}"
                 data-status="{{ $student->status }}"
                 data-progress="{{ $student->avg_progress ?? 0 }}"
                 data-sessions="{{ $student->sessions_count }}"
                 data-assigned="{{ $student->is_assigned ? 'true' : 'false' }}"
                 onclick="window.location.href='{{ $schoolRoute('instructor.students.show', ['id' => $student->id]) }}'">
                <div class="student-card-header">
                    <div class="student-avatar">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>
                    <div class="student-info">
                        <div class="student-name">{{ $student->name }}</div>
                        <div class="student-detail"><strong>Email:</strong> {{ $student->email }}</div>
                        <div class="student-detail"><strong>Contact:</strong> {{ $student->contact ?? 'N/A' }}</div>
                        @if($student->avg_progress)
                            <div class="student-grade"><strong>Grade:</strong> {{ number_format($student->avg_progress, 1) }}%</div>
                        @else
                            <div class="student-grade"><strong>Grade:</strong> -</div>
                        @endif
                        <div class="assignment-badge {{ $student->is_assigned ? 'badge-assigned' : 'badge-unassigned' }}">
                            {{ $student->is_assigned ? 'My Student' : 'Other' }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="no-students">
                <div class="no-students-icon">👥</div>
                <div class="no-students-text">No students found</div>
            </div>
        @endforelse
    </div>
</div>

<script>
function filterStudents() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const assignmentFilter = document.getElementById('assignmentFilter').value;
    const cards = document.querySelectorAll('.student-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.dataset.name;
        const email = card.dataset.email;
        const status = card.dataset.status;
        const isAssigned = card.dataset.assigned === 'true';

        const matchesSearch = name.includes(searchInput) || email.includes(searchInput);
        const matchesStatus = statusFilter === 'all' || status === statusFilter;
        const matchesAssignment = assignmentFilter === 'all' || 
                                 (assignmentFilter === 'assigned' && isAssigned) ||
                                 (assignmentFilter === 'unassigned' && !isAssigned);

        if (matchesSearch && matchesStatus && matchesAssignment) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('showingCount').textContent = visibleCount;
}

function sortStudents() {
    const sortBy = document.getElementById('sortFilter').value;
    const grid = document.getElementById('studentsGrid');
    const cards = Array.from(document.querySelectorAll('.student-card'));

    cards.sort((a, b) => {
        const aAssigned = a.dataset.assigned === 'true';
        const bAssigned = b.dataset.assigned === 'true';

        switch(sortBy) {
            case 'assigned-first':
                if (aAssigned === bAssigned) {
                    return a.dataset.name.localeCompare(b.dataset.name);
                }
                return aAssigned ? -1 : 1;
            case 'name-asc':
                return a.dataset.name.localeCompare(b.dataset.name);
            case 'name-desc':
                return b.dataset.name.localeCompare(a.dataset.name);
            case 'progress-desc':
                return parseFloat(b.dataset.progress) - parseFloat(a.dataset.progress);
            case 'progress-asc':
                return parseFloat(a.dataset.progress) - parseFloat(b.dataset.progress);
            default:
                return 0;
        }
    });

    cards.forEach(card => grid.appendChild(card));
}

// Export dropdown handler
document.querySelectorAll('.export-dropdown button').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var menu = this.nextElementSibling;
        var isVisible = menu.style.display === 'block';
        document.querySelectorAll('.export-menu').forEach(function(m) { m.style.display = 'none'; });
        menu.style.display = isVisible ? 'none' : 'block';
    });
});
document.addEventListener('click', function(e) {
    if (!e.target.closest('.export-dropdown')) {
        document.querySelectorAll('.export-menu').forEach(function(m) { m.style.display = 'none'; });
    }
});
</script>

@endsection
