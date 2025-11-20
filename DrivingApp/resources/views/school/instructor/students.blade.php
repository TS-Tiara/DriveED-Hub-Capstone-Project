@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Students')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $instructorId = Auth::guard('instructor')->id();
@endphp

<style>
    .students-container {
        padding: 0;
        max-width: 1600px;
        margin: 0 auto;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 30px;
        border-radius: 12px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 8px 0;
    }

    .page-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid #667eea;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #667eea;
    }

    /* Search and Filter Bar */
    .controls-bar {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }

    .search-box input {
        width: 100%;
        padding: 12px 45px 12px 15px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .search-box input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .search-box i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }

    .filter-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .filter-label {
        font-size: 0.9rem;
        color: #666;
        font-weight: 600;
    }

    .filter-select {
        padding: 10px 35px 10px 15px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
    }

    .filter-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .view-count {
        font-size: 0.9rem;
        color: #666;
        padding: 10px 15px;
        background: #f3f4f6;
        border-radius: 8px;
        white-space: nowrap;
    }

    /* Table View */
    .students-table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .students-table {
        width: 100%;
        border-collapse: collapse;
    }

    .students-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .students-table th {
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
        position: relative;
    }

    .students-table th:hover {
        background: rgba(255,255,255,0.1);
    }

    .students-table th i {
        margin-left: 6px;
        font-size: 0.8rem;
        opacity: 0.7;
    }

    .students-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .students-table tbody tr:hover {
        background: #f8f9fa;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
    }

    .students-table td {
        padding: 16px 20px;
        font-size: 0.95rem;
        color: #333;
    }

    .student-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .student-name-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .student-name {
        font-weight: 700;
        color: #333;
        font-size: 1rem;
    }

    .student-email {
        font-size: 0.85rem;
        color: #666;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .progress-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .progress-bar-mini {
        flex: 1;
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        max-width: 120px;
    }

    .progress-bar-mini-fill {
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: width 0.5s ease;
    }

    .progress-percent {
        font-weight: 700;
        color: #667eea;
        font-size: 0.95rem;
        min-width: 45px;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .sessions-count {
        font-weight: 700;
        color: #333;
        font-size: 1rem;
    }

    .date-text {
        color: #666;
        font-size: 0.9rem;
    }

    .recent-note-preview {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #666;
        font-style: italic;
        font-size: 0.9rem;
    }

    .no-note {
        color: #9ca3af;
    }

    .action-cell {
        text-align: right;
    }

    .view-btn {
        padding: 8px 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .view-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .empty-state i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        color: #666;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #999;
    }

    /* Mobile Responsiveness */
    @media (max-width: 1024px) {
        .students-table {
            font-size: 0.9rem;
        }

        .students-table th,
        .students-table td {
            padding: 12px 15px;
        }

        .recent-note-preview {
            max-width: 200px;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 20px;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 1.4rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .stat-card {
            padding: 15px;
        }

        .stat-value {
            font-size: 1.5rem;
        }

        .controls-bar {
            padding: 15px;
        }

        /* Mobile: Card Layout */
        .students-table-container {
            background: transparent;
            box-shadow: none;
        }

        .students-table thead {
            display: none;
        }

        .students-table,
        .students-table tbody,
        .students-table tr,
        .students-table td {
            display: block;
            width: 100%;
        }

        .students-table tbody tr {
            background: white;
            margin-bottom: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 20px;
            border: 2px solid transparent;
        }

        .students-table tbody tr:hover {
            transform: none;
            border-color: #667eea;
        }

        .students-table td {
            padding: 10px 0;
            border: none;
            text-align: left;
            position: relative;
            padding-left: 45%;
        }

        .students-table td::before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            width: 40%;
            padding-right: 10px;
            font-weight: 700;
            color: #667eea;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .students-table td:first-child {
            padding-left: 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
            margin-bottom: 10px;
        }

        .students-table td:first-child::before {
            display: none;
        }

        .student-name-cell {
            justify-content: flex-start;
        }

        .action-cell {
            padding-left: 0 !important;
            text-align: left !important;
            margin-top: 10px;
        }

        .action-cell::before {
            display: none;
        }

        .view-btn {
            width: 100%;
            padding: 12px;
        }

        .progress-cell {
            justify-content: flex-start;
        }

        .progress-bar-mini {
            max-width: 100%;
            flex: 1;
        }

        .recent-note-preview {
            max-width: 100%;
            white-space: normal;
        }
    }

    @media (max-width: 480px) {
        .page-header {
            padding: 16px;
        }

        .page-title {
            font-size: 1.2rem;
        }

        .page-subtitle {
            font-size: 0.85rem;
        }

        .stat-card {
            padding: 12px;
        }

        .stat-label {
            font-size: 0.75rem;
        }

        .stat-value {
            font-size: 1.3rem;
        }

        .controls-bar {
            padding: 12px;
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            min-width: 100%;
        }

        .filter-group {
            width: 100%;
            justify-content: space-between;
        }

        .view-count {
            text-align: center;
        }

        .students-table tbody tr {
            padding: 15px;
        }

        .students-table td {
            padding-left: 40%;
            font-size: 0.9rem;
        }

        .students-table td::before {
            font-size: 0.75rem;
            width: 35%;
        }

        .student-avatar {
            width: 35px;
            height: 35px;
            font-size: 0.9rem;
        }

        .student-name {
            font-size: 0.95rem;
        }

        .student-email {
            font-size: 0.8rem;
        }
    }
</style>

<div class="students-container">
    <!-- Header -->
    <div class="page-header">
        <h1 class="page-title">My Students</h1>
        <p class="page-subtitle">View and track your students' progress</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Students</div>
            <div class="stat-value">{{ $students->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Students</div>
            <div class="stat-value">{{ $students->where('status', 'active')->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Sessions</div>
            <div class="stat-value">{{ $students->sum('total_sessions') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Avg Progress</div>
            <div class="stat-value">{{ $students->count() > 0 ? number_format($students->avg('overall_progress'), 0) : 0 }}%</div>
        </div>
    </div>

    <!-- Search and Filter Controls -->
    <div class="controls-bar">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search students by name or email..." onkeyup="filterStudents()">
            <i class="fas fa-search"></i>
        </div>
        
        <div class="filter-group">
            <label class="filter-label">Status:</label>
            <select id="statusFilter" class="filter-select" onchange="filterStudents()">
                <option value="all">All Students</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Sort:</label>
            <select id="sortBy" class="filter-select" onchange="sortStudents()">
                <option value="name-asc">Name (A-Z)</option>
                <option value="name-desc">Name (Z-A)</option>
                <option value="progress-desc">Progress (High-Low)</option>
                <option value="progress-asc">Progress (Low-High)</option>
                <option value="sessions-desc">Sessions (High-Low)</option>
                <option value="date-desc">Recently Enrolled</option>
            </select>
        </div>

        <div class="view-count" id="viewCount">
            Showing {{ $students->count() }} students
        </div>
    </div>

    <!-- Students Table -->
    @if($students->count() > 0)
        <div class="students-table-container">
            <table class="students-table" id="studentsTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Student <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(1)">Progress <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2)">Sessions <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(3)">Status <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(4)">Enrolled <i class="fas fa-sort"></i></th>
                        <th>Recent Note</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    @foreach($students as $student)
                        <tr class="student-row" 
                            data-name="{{ strtolower($student->name) }}" 
                            data-email="{{ strtolower($student->email) }}"
                            data-status="{{ $student->status }}"
                            data-progress="{{ $student->overall_progress }}"
                            data-sessions="{{ $student->total_sessions }}"
                            onclick="viewStudent({{ $student->id }})">
                            <td data-label="Student">
                                <div class="student-name-cell">
                                    <div class="student-avatar">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <div class="student-name-info">
                                        <span class="student-name">{{ $student->name }}</span>
                                        <span class="student-email">{{ $student->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Progress">
                                <div class="progress-cell">
                                    <div class="progress-bar-mini">
                                        <div class="progress-bar-mini-fill" style="width: {{ $student->overall_progress }}%"></div>
                                    </div>
                                    <span class="progress-percent">{{ number_format($student->overall_progress, 0) }}%</span>
                                </div>
                            </td>
                            <td data-label="Sessions">
                                <span class="sessions-count">{{ $student->total_sessions }}</span>
                            </td>
                            <td data-label="Status">
                                <span class="status-badge status-{{ $student->status }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                            <td data-label="Enrolled">
                                <span class="date-text">{{ \Carbon\Carbon::parse($student->enrollment_date)->format('M d, Y') }}</span>
                            </td>
                            <td data-label="Recent Note">
                                @if($student->recent_note && $student->recent_note !== 'No notes yet')
                                    <div class="recent-note-preview" title="{{ $student->recent_note }}">
                                        "{{ Str::limit($student->recent_note, 50) }}"
                                    </div>
                                @else
                                    <span class="recent-note-preview no-note">No notes yet</span>
                                @endif
                            </td>
                            <td class="action-cell">
                                <button class="view-btn" onclick="event.stopPropagation(); viewStudent({{ $student->id }})">
                                    View
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <h3>No Students Yet</h3>
            <p>You haven't taught any students yet. Students will appear here once you have bookings.</p>
        </div>
    @endif
</div>

<script>
function viewStudent(studentId) {
    const url = `{{ url($school->slug . '/instructor/students') }}/${studentId}`;
    if (typeof loadContent === 'function') {
        loadContent(url);
    } else {
        window.location.href = url;
    }
}

function filterStudents() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.student-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const email = row.getAttribute('data-email');
        const status = row.getAttribute('data-status');

        const matchesSearch = name.includes(searchInput) || email.includes(searchInput);
        const matchesStatus = statusFilter === 'all' || status === statusFilter;

        if (matchesSearch && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    updateViewCount(visibleCount);
}

function sortStudents() {
    const sortBy = document.getElementById('sortBy').value;
    const tbody = document.getElementById('studentsTableBody');
    const rows = Array.from(tbody.querySelectorAll('.student-row'));

    rows.sort((a, b) => {
        let aValue, bValue;

        switch(sortBy) {
            case 'name-asc':
                aValue = a.getAttribute('data-name');
                bValue = b.getAttribute('data-name');
                return aValue.localeCompare(bValue);
            
            case 'name-desc':
                aValue = a.getAttribute('data-name');
                bValue = b.getAttribute('data-name');
                return bValue.localeCompare(aValue);
            
            case 'progress-desc':
                aValue = parseFloat(a.getAttribute('data-progress'));
                bValue = parseFloat(b.getAttribute('data-progress'));
                return bValue - aValue;
            
            case 'progress-asc':
                aValue = parseFloat(a.getAttribute('data-progress'));
                bValue = parseFloat(b.getAttribute('data-progress'));
                return aValue - bValue;
            
            case 'sessions-desc':
                aValue = parseInt(a.getAttribute('data-sessions'));
                bValue = parseInt(b.getAttribute('data-sessions'));
                return bValue - aValue;
            
            case 'date-desc':
                aValue = a.querySelector('.date-text').textContent;
                bValue = b.querySelector('.date-text').textContent;
                return new Date(bValue) - new Date(aValue);
            
            default:
                return 0;
        }
    });

    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

function sortTable(columnIndex) {
    const table = document.getElementById('studentsTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Determine current sort direction
    const th = table.querySelectorAll('th')[columnIndex];
    const currentDirection = th.getAttribute('data-direction') || 'asc';
    const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
    
    // Reset all column sort indicators
    table.querySelectorAll('th').forEach(header => {
        header.setAttribute('data-direction', '');
        const icon = header.querySelector('i');
        if (icon) icon.className = 'fas fa-sort';
    });
    
    // Set new direction
    th.setAttribute('data-direction', newDirection);
    const icon = th.querySelector('i');
    if (icon) {
        icon.className = newDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
    }
    
    // Sort rows
    rows.sort((a, b) => {
        let aValue, bValue;
        const aCells = a.querySelectorAll('td');
        const bCells = b.querySelectorAll('td');
        
        switch(columnIndex) {
            case 0: // Name
                aValue = aCells[0].querySelector('.student-name').textContent.toLowerCase();
                bValue = bCells[0].querySelector('.student-name').textContent.toLowerCase();
                break;
            case 1: // Progress
                aValue = parseFloat(a.getAttribute('data-progress'));
                bValue = parseFloat(b.getAttribute('data-progress'));
                break;
            case 2: // Sessions
                aValue = parseInt(a.getAttribute('data-sessions'));
                bValue = parseInt(b.getAttribute('data-sessions'));
                break;
            case 3: // Status
                aValue = aCells[3].textContent.toLowerCase();
                bValue = bCells[3].textContent.toLowerCase();
                break;
            case 4: // Date
                aValue = new Date(aCells[4].textContent);
                bValue = new Date(bCells[4].textContent);
                break;
            default:
                return 0;
        }
        
        if (typeof aValue === 'string') {
            return newDirection === 'asc' 
                ? aValue.localeCompare(bValue)
                : bValue.localeCompare(aValue);
        } else {
            return newDirection === 'asc'
                ? aValue - bValue
                : bValue - aValue;
        }
    });
    
    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

function updateViewCount(count) {
    const viewCount = document.getElementById('viewCount');
    viewCount.textContent = `Showing ${count} student${count !== 1 ? 's' : ''}`;
}
</script>

@endsection
