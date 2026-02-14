@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Students')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $instructorId = Auth::guard('instructor')->id();
    
    // Calculate statistics
    $totalStudents = $students->count();
    $myStudents = $students->where('is_assigned', true)->count();
    $activeStudents = $students->where('status', 'active')->count();
    $totalSessions = $students->sum('sessions_count');
    $avgProgress = $students->avg('avg_progress') ?? 0;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    /* ── Controls Bar ── */
    .controls-bar {
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        flex-wrap: wrap;
    }

    .search-wrapper {
        flex: 1;
        min-width: 220px;
    }

    .search-input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .search-input:focus { border-color: {{ $primaryColor }}; }

    .filter-select {
        padding: 10px 32px 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        font-size: 0.875rem;
        cursor: pointer;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        transition: border-color 0.2s;
    }

    .filter-select:focus { border-color: {{ $primaryColor }}; }

    .view-count {
        color: #6b7280;
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* ── Students Grid ── */
    .students-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 16px;
        margin-bottom: 30px;
    }

    .student-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #f3f4f6;
    }

    .student-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: {{ $primaryColor }};
    }

    .student-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .student-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: {{ $primaryColor }};
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .student-info { flex: 1; min-width: 0; }

    .student-name {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .student-detail {
        font-size: 0.82rem;
        color: #6b7280;
        margin: 1px 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .student-detail strong { color: #374151; font-weight: 600; }

    .student-grade {
        font-size: 0.95rem;
        color: {{ $primaryColor }};
        font-weight: 700;
        margin-top: 4px;
    }

    .assignment-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-top: 4px;
    }

    .badge-assigned { background: #dbeafe; color: #1e40af; }
    .badge-unassigned { background: #f3f4f6; color: #6b7280; }

    .no-students {
        grid-column: 1 / -1;
        text-align: center;
        padding: 48px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }

    .no-students-icon { font-size: 3rem; color: #d1d5db; margin-bottom: 12px; }
    .no-students-text { font-size: 1.1rem; color: #6b7280; font-weight: 500; }

    /* ── Export Dropdown ── */
    .export-dropdown { position: relative; display: inline-block; }

    .btn-export-trigger {
        padding: 10px 16px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-export-trigger:hover { background: #059669; }

    .export-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 4px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        min-width: 180px;
        z-index: 100;
        overflow: hidden;
    }

    .export-menu.show { display: block; }

    .export-menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        color: #374151;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: background 0.15s;
    }

    .export-menu a:hover { background: #f3f4f6; }

    @media (max-width: 768px) {
        .students-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .student-card { padding: 12px; }
        .student-card-header { flex-direction: column; text-align: center; gap: 8px; }
        .student-avatar { width: 42px; height: 42px; font-size: 1rem; }
        .student-info { text-align: center; }
        .student-name { font-size: 0.88rem; }
        .student-detail { font-size: 0.72rem; display: none; }
        .student-detail:first-of-type { display: block; }
        .student-grade { font-size: 0.8rem; }
        .assignment-badge { font-size: 0.6rem; }
        .controls-bar { padding: 12px; gap: 8px; }
        .search-wrapper { min-width: 150px; }
        .search-input { padding: 8px 10px; font-size: 0.82rem; }
        .filter-select { padding: 8px 10px; font-size: 0.78rem; }
    }

    @media (max-width: 480px) {
        .students-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
        .student-card { padding: 10px; }
        .student-avatar { width: 38px; height: 38px; font-size: 0.9rem; }
        .student-name { font-size: 0.82rem; }
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">My Students</h1>
            <p class="page-subtitle">View and manage your assigned students</p>
        </div>
        <div class="export-dropdown">
            <button class="btn-export-trigger" onclick="toggleExportMenu()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export
            </button>
            <div class="export-menu" id="exportMenu">
                <a href="{{ route('schools.instructor.exports.students.pdf', $school) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#ef4444" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Download PDF
                </a>
                <a href="{{ route('schools.instructor.exports.students.excel', $school) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#10b981" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card students">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Students</div>
                        <div class="stat-value">{{ $totalStudents }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">All students in the school</div>
            </div>
        </div>
        <div class="stat-card instructors">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">My Students</div>
                        <div class="stat-value">{{ $myStudents }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Assigned to you</div>
            </div>
        </div>
        <div class="stat-card active">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Sessions</div>
                        <div class="stat-value">{{ $totalSessions }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Completed sessions</div>
            </div>
        </div>
        <div class="stat-card growth">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Avg Progress</div>
                        <div class="stat-value">{{ number_format($avgProgress, 0) }}%</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Average student progress</div>
            </div>
        </div>
    </div>

    <!-- Controls Bar -->
    <div class="controls-bar">
        <div class="search-wrapper">
            <input type="text" class="search-input" id="searchInput" 
                   placeholder="Search students by name or email..." onkeyup="filterStudents()">
        </div>
        <select class="filter-select" id="assignmentFilter" onchange="filterStudents()">
            <option value="all">All Students</option>
            <option value="assigned">My Students</option>
            <option value="unassigned">Other Students</option>
        </select>
        <select class="filter-select" id="statusFilter" onchange="filterStudents()">
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <select class="filter-select" id="sortFilter" onchange="sortStudents()">
            <option value="assigned-first">My Students First</option>
            <option value="name-asc">Name (A-Z)</option>
            <option value="name-desc">Name (Z-A)</option>
            <option value="progress-desc">Progress (High-Low)</option>
            <option value="progress-asc">Progress (Low-High)</option>
        </select>
        <div class="view-count">
            Showing <span id="showingCount">{{ $totalStudents }}</span> students
        </div>
    </div>

    <!-- Students Grid -->
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
function toggleExportMenu() {
    document.getElementById('exportMenu').classList.toggle('show');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.export-dropdown')) {
        var menu = document.getElementById('exportMenu');
        if (menu) menu.classList.remove('show');
    }
});

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
</script>

@endsection
