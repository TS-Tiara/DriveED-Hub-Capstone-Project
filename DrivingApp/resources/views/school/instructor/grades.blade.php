@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Grade Management')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school->schoolSetting;
@endphp

<style>
    .grades-container {
        padding: 20px;
        max-width: 1600px;
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

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        color: {{ $settings->primary_color ?? '#667eea' }};
        border: 2px solid {{ $settings->primary_color ?? '#667eea' }};
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
        margin-bottom: 20px;
    }

    .back-button:hover {
        background: {{ $settings->primary_color ?? '#667eea' }};
        color: white;
        transform: translateX(-5px);
    }

    .controls-bar {
        background: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-input {
        flex: 1;
        min-width: 250px;
        padding: 12px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: {{ $settings->primary_color ?? '#667eea' }};
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .filter-select {
        padding: 10px 15px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        transition: border-color 0.2s;
    }

    .filter-select:focus {
        outline: none;
        border-color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .btn-export {
        padding: 10px 20px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-export:hover {
        background: #059669;
        transform: translateY(-2px);
    }

    .btn-save-all {
        padding: 10px 20px;
        background: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-save-all:hover {
        background: {{ $school->schoolSetting->secondary_color ?? '#764ba2' }};
        transform: translateY(-2px);
    }

    .grades-table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .grades-table {
        width: 100%;
        border-collapse: collapse;
    }

    .grades-table thead {
        background: #f3f4f6;
        border-bottom: 2px solid {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    .grades-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        color: #374151;
        white-space: nowrap;
    }

    .grades-table th.sortable {
        cursor: pointer;
        user-select: none;
    }

    .grades-table th.sortable:hover {
        background: #e5e7eb;
    }

    .grades-table td {
        padding: 15px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    .grades-table tbody tr:hover {
        background: #f9fafb;
    }

    .student-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
        flex-shrink: 0;
    }

    .student-info {
        flex: 1;
    }

    .student-name {
        font-weight: 600;
        color: #111827;
        display: block;
        margin-bottom: 2px;
    }

    .student-email {
        font-size: 12px;
        color: #6b7280;
    }

    .grade-input {
        width: 80px;
        padding: 8px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
        transition: all 0.2s;
    }

    .grade-input:focus {
        outline: none;
        border-color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    .grade-input.changed {
        border-color: #f59e0b;
        background: #fffbeb;
    }

    .grade-badge {
        padding: 6px 12px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 13px;
        display: inline-block;
    }

    .grade-excellent {
        background: #d1fae5;
        color: #065f46;
    }

    .grade-good {
        background: #dbeafe;
        color: #1e40af;
    }

    .grade-average {
        background: #fef3c7;
        color: #92400e;
    }

    .grade-poor {
        background: #fee2e2;
        color: #991b1b;
    }

    .grade-none {
        background: #f3f4f6;
        color: #6b7280;
    }

    .sessions-badge {
        background: #e5e7eb;
        color: #374151;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
    }

    .action-cell {
        display: flex;
        gap: 8px;
    }

    .btn-icon {
        padding: 8px;
        border: none;
        background: transparent;
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 18px;
    }

    .btn-view {
        color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    .btn-view:hover {
        background: {{ $school->schoolSetting->primary_color ?? '#667eea' }}20;
    }

    .btn-save {
        color: #10b981;
    }

    .btn-save:hover {
        background: #d1fae5;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }

    .stats-summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-box {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
    }

    .stat-label {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    @media (max-width: 1024px) {
        .stats-summary {
            grid-template-columns: repeat(2, 1fr);
        }

        .controls-bar {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            min-width: 200px;
        }
    }

    @media (max-width: 768px) {
        .grades-container {
            padding: 15px;
        }

        .stats-summary {
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

        .search-input {
            flex: 1;
            min-width: 150px;
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

        .action-buttons {
            width: 100%;
            display: flex;
            gap: 8px;
        }

        .btn-export,
        .btn-save-all {
            flex: 1;
            padding: 8px 12px;
            font-size: 12px;
        }

        .grades-table {
            font-size: 12px;
        }

        .grades-table th,
        .grades-table td {
            padding: 10px 8px;
        }
    }

    @media (max-width: 480px) {
        .grades-container {
            padding: 10px;
        }

        .page-title {
            font-size: 1.3rem;
        }

        .stats-summary {
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
            font-size: 12px;
            padding: 6px 8px;
        }

        .filter-select {
            font-size: 11px;
            padding: 6px 8px;
        }

        .action-buttons {
            gap: 6px;
        }

        .btn-export,
        .btn-save-all {
            padding: 6px 10px;
            font-size: 11px;
        }

        .grades-table-container {
            overflow-x: auto;
            margin: 0 -10px;
            padding: 0 10px;
        }

        .grades-table {
            font-size: 11px;
            min-width: 600px;
        }

        .grades-table th,
        .grades-table td {
            padding: 8px 6px;
            white-space: nowrap;
        }
    }

    @media (max-width: 360px) {
        .stats-summary {
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
    }
</style>

<div class="grades-container">
    <div class="page-header">
        <h1 class="page-title">Grade Management</h1>
    </div>

    <!-- Summary Statistics -->
    <div class="stats-summary">
        <div class="stat-box">
            <div class="stat-label">Total Students</div>
            <div class="stat-value">{{ $students->count() }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Graded Sessions</div>
            <div class="stat-value">{{ $gradedSessions }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Average Grade</div>
            <div class="stat-value">{{ number_format($averageGrade, 1) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Pending Grades</div>
            <div class="stat-value">{{ $pendingGrades }}</div>
        </div>
    </div>

    <!-- Controls Bar -->
    <div class="controls-bar">
        <input type="text" class="search-input" id="searchInput" 
               placeholder="Search students by name or email..." onkeyup="filterTable()">
        
        <div class="filter-group">
            <span class="filter-label">Sort By:</span>
            <select class="filter-select" id="sortFilter" onchange="applySortFilter()">
                <option value="name-asc">Name (A-Z)</option>
                <option value="name-desc">Name (Z-A)</option>
                <option value="enrollment-newest">Enrollment (Newest First)</option>
                <option value="enrollment-oldest">Enrollment (Oldest First)</option>
                <option value="sessions-most">Most Sessions</option>
                <option value="sessions-least">Least Sessions</option>
                <option value="grade-highest">Highest Grade</option>
                <option value="grade-lowest">Lowest Grade</option>
            </select>
        </div>

        <div class="action-buttons">
            <button class="btn-export" onclick="exportGrades()">
                Export CSV
            </button>
            <button class="btn-save-all" onclick="saveAllChanges()">
                Save All Changes
            </button>
        </div>
    </div>

    <!-- Grades Table -->
    <div class="grades-table-container">
        @if($students->count() > 0)
            <table class="grades-table" id="gradesTable">
                <thead>
                    <tr>
                        <th class="sortable" onclick="sortTable('name')">Student Name</th>
                        <th class="sortable" onclick="sortTable('sessions')">Sessions</th>
                        <th class="sortable" onclick="sortTable('avg')">Avg Grade</th>
                        <th>Last Session</th>
                        <th>Last Grade</th>
                        <th>Quick Grade</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="gradesTableBody">
                    @foreach($students as $student)
                        @php
                            $completedSessions = $student->bookings->where('status', 'completed')->count();
                            $avgGrade = $student->bookings->whereNotNull('session_grade')->avg('session_grade');
                            $lastSession = $student->bookings->sortByDesc('scheduled_at')->first();
                            $enrollmentTimestamp = $student->enrollment_date ? strtotime($student->enrollment_date) : 0;
                            $gradeCategory = $avgGrade >= 90 ? 'excellent' : ($avgGrade >= 75 ? 'good' : ($avgGrade >= 60 ? 'average' : ($avgGrade ? 'poor' : 'none')));
                        @endphp
                        <tr class="grade-row" 
                            data-name="{{ strtolower($student->name) }}"
                            data-email="{{ strtolower($student->email) }}"
                            data-enrollment="{{ $enrollmentTimestamp }}"
                            data-sessions="{{ $completedSessions }}"
                            data-avg-grade="{{ $avgGrade ?? 0 }}">
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <div class="student-info">
                                        <span class="student-name">{{ $student->name }}</span>
                                        <span class="student-email">{{ $student->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="sessions-badge">{{ $completedSessions }} sessions</span>
                            </td>
                            <td>
                                @if($avgGrade)
                                    <span class="grade-badge grade-{{ $gradeCategory }}">
                                        {{ number_format($avgGrade, 1) }}
                                    </span>
                                @else
                                    <span class="grade-badge grade-none">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($lastSession && ($lastSession->scheduled_at || $lastSession->booking_date))
                                    {{ ($lastSession->scheduled_at ?? $lastSession->booking_date)?->format('M d, Y') ?? 'N/A' }}
                                @else
                                    <span style="color: #9ca3af;">No sessions</span>
                                @endif
                            </td>
                            <td>
                                @if($lastSession && $lastSession->session_grade)
                                    <strong>{{ $lastSession->session_grade }}</strong>
                                @else
                                    <span style="color: #9ca3af;">-</span>
                                @endif
                            </td>
                            <td>
                                <input type="number" 
                                       class="grade-input" 
                                       min="0" 
                                       max="100" 
                                       step="0.1"
                                       data-student-id="{{ $student->id }}"
                                       data-last-booking-id="{{ $lastSession ? $lastSession->id : '' }}"
                                       placeholder="0-100"
                                       onchange="markChanged(this)">
                            </td>
                            <td>
                                <div class="action-cell">
                                    <button class="btn-icon btn-view" 
                                            onclick="viewStudentDetails({{ $student->id }})"
                                            title="View Details">
                                        View
                                    </button>
                                    <button class="btn-icon btn-save" 
                                            onclick="saveGrade({{ $student->id }})"
                                            title="Save Grade">
                                        Save
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">-</div>
                <p style="font-size: 18px; font-weight: 600; margin-bottom: 10px;">No Students Found</p>
                <p>You don't have any students assigned yet.</p>
            </div>
        @endif
    </div>
</div>

<script>
    // Filter table by search only
    function filterTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.grade-row');
        
        rows.forEach(row => {
            const name = row.dataset.name;
            const email = row.dataset.email;
            
            const matchesSearch = name.includes(searchInput) || email.includes(searchInput);
            
            row.style.display = matchesSearch ? '' : 'none';
        });
    }

    // Apply sort filter
    function applySortFilter() {
        const sortOption = document.getElementById('sortFilter').value;
        const table = document.getElementById('gradesTableBody');
        const rows = Array.from(table.querySelectorAll('.grade-row'));
        
        rows.sort((a, b) => {
            let aVal, bVal;
            
            switch(sortOption) {
                case 'name-asc':
                    aVal = a.dataset.name;
                    bVal = b.dataset.name;
                    return aVal.localeCompare(bVal);
                    
                case 'name-desc':
                    aVal = a.dataset.name;
                    bVal = b.dataset.name;
                    return bVal.localeCompare(aVal);
                    
                case 'enrollment-newest':
                    aVal = parseInt(a.dataset.enrollment) || 0;
                    bVal = parseInt(b.dataset.enrollment) || 0;
                    return bVal - aVal;
                    
                case 'enrollment-oldest':
                    aVal = parseInt(a.dataset.enrollment) || 0;
                    bVal = parseInt(b.dataset.enrollment) || 0;
                    return aVal - bVal;
                    
                case 'sessions-most':
                    aVal = parseInt(a.dataset.sessions) || 0;
                    bVal = parseInt(b.dataset.sessions) || 0;
                    return bVal - aVal;
                    
                case 'sessions-least':
                    aVal = parseInt(a.dataset.sessions) || 0;
                    bVal = parseInt(b.dataset.sessions) || 0;
                    return aVal - bVal;
                    
                case 'grade-highest':
                    aVal = parseFloat(a.dataset.avgGrade) || 0;
                    bVal = parseFloat(b.dataset.avgGrade) || 0;
                    return bVal - aVal;
                    
                case 'grade-lowest':
                    aVal = parseFloat(a.dataset.avgGrade) || 0;
                    bVal = parseFloat(b.dataset.avgGrade) || 0;
                    return aVal - bVal;
                    
                default:
                    return 0;
            }
        });
        
        rows.forEach(row => table.appendChild(row));
    }

    // Mark input as changed
    function markChanged(input) {
        input.classList.add('changed');
    }

    // Sort table
    let sortDirection = {};
    function sortTable(column) {
        const table = document.getElementById('gradesTableBody');
        const rows = Array.from(table.querySelectorAll('.grade-row'));
        
        sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';
        
        rows.sort((a, b) => {
            let aVal, bVal;
            
            if (column === 'name') {
                aVal = a.dataset.name;
                bVal = b.dataset.name;
            } else if (column === 'sessions') {
                aVal = parseInt(a.querySelectorAll('.sessions-badge')[0].textContent) || 0;
                bVal = parseInt(b.querySelectorAll('.sessions-badge')[0].textContent) || 0;
            } else if (column === 'avg') {
                aVal = parseFloat(a.querySelectorAll('.grade-badge')[0].textContent) || 0;
                bVal = parseFloat(b.querySelectorAll('.grade-badge')[0].textContent) || 0;
            }
            
            if (sortDirection[column] === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });
        
        rows.forEach(row => table.appendChild(row));
    }

    // View student details
    function viewStudentDetails(studentId) {
        loadContent('/{{ $school->slug }}/instructor/students/' + studentId);
    }

    // Save individual grade
    function saveGrade(studentId) {
        const input = document.querySelector(`input[data-student-id="${studentId}"]`);
        const grade = parseFloat(input.value);
        const bookingId = input.dataset.lastBookingId;
        
        if (!grade || grade < 0 || grade > 100) {
            alert('Please enter a valid grade between 0 and 100');
            return;
        }
        
        if (!bookingId) {
            alert('This student has no sessions to grade');
            return;
        }
        
        // Send AJAX request
        fetch(`/{{ $school->slug }}/instructor/lessons/${bookingId}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                session_grade: grade
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.classList.remove('changed');
                alert('Grade saved successfully!');
                location.reload();
            } else {
                alert('Failed to save grade');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the grade');
        });
    }

    // Save all changed grades
    function saveAllChanges() {
        const changedInputs = document.querySelectorAll('.grade-input.changed');
        
        if (changedInputs.length === 0) {
            alert('No changes to save');
            return;
        }
        
        let savedCount = 0;
        const totalChanges = changedInputs.length;
        
        changedInputs.forEach(input => {
            const studentId = input.dataset.studentId;
            const grade = parseFloat(input.value);
            const bookingId = input.dataset.lastBookingId;
            
            if (grade && grade >= 0 && grade <= 100 && bookingId) {
                fetch(`/{{ $school->slug }}/instructor/lessons/${bookingId}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        session_grade: grade
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        savedCount++;
                        input.classList.remove('changed');
                        
                        if (savedCount === totalChanges) {
                            alert(`Successfully saved ${savedCount} grade(s)!`);
                            location.reload();
                        }
                    }
                });
            }
        });
    }

    // Export grades to CSV
    function exportGrades() {
        const rows = document.querySelectorAll('.grade-row:not([style*="display: none"])');
        let csv = 'Student Name,Email,Sessions,Average Grade,Last Session,Last Grade\n';
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const name = cells[0].querySelector('.student-name').textContent;
            const email = cells[0].querySelector('.student-email').textContent;
            const sessions = cells[1].textContent.trim();
            const avgGrade = cells[2].textContent.trim();
            const lastSession = cells[3].textContent.trim();
            const lastGrade = cells[4].textContent.trim();
            
            csv += `"${name}","${email}","${sessions}","${avgGrade}","${lastSession}","${lastGrade}"\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `grades_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }
</script>

@endsection
