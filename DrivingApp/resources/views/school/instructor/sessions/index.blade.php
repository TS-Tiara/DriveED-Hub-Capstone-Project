@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Session Logs')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $useGradient = $settings?->use_gradient_header ?? true;
    $schoolRoute = function($routeName, $params = []) use ($school) {
        return route('schools.' . $routeName, array_merge(['school' => $school->slug], $params));
    };
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .sessions-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 4px solid {{ $primaryColor }};
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .page-title {
        font-size: 2rem;
        color: #111827;
        margin: 0 0 4px 0;
        font-weight: 400;
    }

    .page-subtitle {
        font-size: 0.95rem;
        color: #6b7280;
        margin: 0;
    }

    .btn-log-session {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .btn-log-session:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        color: white;
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        text-align: center;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }

    .stat-label {
        font-size: 0.8rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: {{ $primaryColor }};
    }

    /* Filter Bar */
    .filter-bar {
        background: white;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .filter-bar label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
    }

    .filter-select {
        padding: 8px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #374151;
        background: white;
        cursor: pointer;
        min-width: 150px;
    }

    .filter-select:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px {{ $primaryColor }}22;
    }

    /* Sessions Table */
    .sessions-table-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 1.15rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .sessions-table {
        width: 100%;
        border-collapse: collapse;
    }

    .sessions-table thead th {
        background: #f9fafb;
        padding: 14px 20px;
        text-align: left;
        font-size: 0.8rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }

    .sessions-table tbody td {
        padding: 16px 20px;
        font-size: 0.9rem;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .sessions-table tbody tr:hover {
        background: #f9fafb;
    }

    .sessions-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Student Info */
    .student-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .student-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .student-name {
        font-weight: 600;
        color: #111827;
    }

    /* Badges */
    .type-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .type-theoretical {
        background: #dbeafe;
        color: #1e40af;
    }

    .type-practical {
        background: #ede9fe;
        color: #5b21b6;
    }

    .hours-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        background: #d1fae5;
        color: #065f46;
    }

    .date-cell .date {
        font-weight: 600;
        color: #111827;
    }

    .date-cell .time {
        font-size: 0.8rem;
        color: #9ca3af;
    }

    /* Action Buttons */
    .action-btns {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-view {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn-view:hover {
        background: #bfdbfe;
        color: #1e40af;
    }

    .btn-edit {
        background: #fef3c7;
        color: #92400e;
    }

    .btn-edit:hover {
        background: #fde68a;
        color: #92400e;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #f3f4f6;
        color: #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .empty-state h3 {
        font-size: 1.15rem;
        color: #374151;
        margin: 0 0 8px 0;
    }

    .empty-state p {
        font-size: 0.9rem;
        color: #9ca3af;
        margin: 0 0 20px 0;
    }

    .btn-first-session {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: {{ $primaryColor }};
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-first-session:hover {
        opacity: 0.9;
        color: white;
    }

    /* Pagination */
    .table-footer {
        padding: 16px 24px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pagination-info {
        font-size: 0.85rem;
        color: #6b7280;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 16px;
        }

        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="sessions-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">My Session Logs</h1>
            <p class="page-subtitle">View and manage your logged driving sessions</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <div class="export-dropdown" style="position: relative; display: inline-block;">
                <button onclick="this.nextElementSibling.classList.toggle('show')" class="btn-export-trigger" style="padding: 10px 18px; background: #10b981; color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Export
                </button>
                <div class="export-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 4px; background: white; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.15); min-width: 180px; z-index: 100; overflow: hidden;">
                    <a href="{{ $schoolRoute('instructor.exports.sessions.pdf') }}" style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; color: #374151; text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: background 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#ef4444" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                        Download PDF
                    </a>
                    <a href="{{ $schoolRoute('instructor.exports.sessions.excel') }}" style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; color: #374151; text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: background 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#10b981" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Download Excel
                    </a>
                </div>
            </div>
            <a href="{{ $schoolRoute('instructor.sessions.create') }}" class="btn-log-session">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Log New Session
        </a>
        </div>
    </div>

    <!-- Stats Summary -->
    @php
        $totalSessions = $sessions->total();
        $totalHours = $sessions->sum('hours_completed');
        $theoreticalCount = $sessions->where('session_type', 'theoretical')->count();
        $practicalCount = $sessions->where('session_type', 'practical')->count();
    @endphp
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Sessions</div>
            <div class="stat-value">{{ $totalSessions }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Hours Logged</div>
            <div class="stat-value">{{ number_format($totalHours, 1) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Theoretical</div>
            <div class="stat-value">{{ $theoreticalCount }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Practical</div>
            <div class="stat-value">{{ $practicalCount }}</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <label>Filter by:</label>
        <select class="filter-select" id="filterType" onchange="filterSessions()">
            <option value="">All Types</option>
            <option value="theoretical">Theoretical</option>
            <option value="practical">Practical</option>
        </select>
    </div>

    <!-- Sessions Table -->
    <div class="sessions-table-wrapper">
        <div class="table-header">
            <h2 class="table-title">Session History</h2>
            <span style="font-size: 0.85rem; color: #9ca3af;">{{ $totalSessions }} {{ Str::plural('session', $totalSessions) }}</span>
        </div>

        @if($sessions->count() > 0)
            <table class="sessions-table">
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
                    @foreach($sessions as $session)
                    <tr data-type="{{ $session->session_type }}">
                        <td>
                            <div class="date-cell">
                                <div class="date">{{ $session->session_date->format('M d, Y') }}</div>
                                <div class="time">{{ \Carbon\Carbon::parse($session->session_time)->format('g:i A') }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="student-info">
                                <div class="student-avatar">
                                    {{ strtoupper(substr($session->enrollment->student->name ?? 'N', 0, 1)) }}
                                </div>
                                <span class="student-name">{{ $session->enrollment->student->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>{{ $session->enrollment->course->title ?? 'N/A' }}</td>
                        <td>
                            <span class="type-badge type-{{ $session->session_type }}">
                                {{ ucfirst($session->session_type) }}
                            </span>
                        </td>
                        <td>
                            <span class="hours-badge">{{ number_format($session->hours_completed, 1) }}h</span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ $schoolRoute('instructor.sessions.show', ['sessionCompletion' => $session->id]) }}" 
                                   class="btn-action btn-view" title="View Details">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="{{ $schoolRoute('instructor.sessions.edit', ['sessionCompletion' => $session->id]) }}" 
                                   class="btn-action btn-edit" title="Edit Session">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($sessions->hasPages())
            <div class="table-footer">
                <div class="pagination-info">
                    Showing {{ $sessions->firstItem() }} to {{ $sessions->lastItem() }} of {{ $sessions->total() }} sessions
                </div>
                {{ $sessions->links() }}
            </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 36px; height: 36px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <h3>No sessions logged yet</h3>
                <p>Start logging your driving sessions to track your teaching progress.</p>
                <a href="{{ $schoolRoute('instructor.sessions.create') }}" class="btn-first-session">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 18px; height: 18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Log Your First Session
                </a>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    document.querySelectorAll('.export-menu').forEach(function(menu) {
        if (!menu.parentElement.contains(e.target)) {
            menu.classList.remove('show');
            menu.style.display = 'none';
        } else if (menu.classList.contains('show')) {
            menu.style.display = 'block';
        }
    });
    document.querySelectorAll('.export-menu.show').forEach(function(menu) {
        menu.style.display = 'block';
    });
});
// Override toggle to use style.display
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

function filterSessions() {
    const filter = document.getElementById('filterType').value.toLowerCase();
    const rows = document.querySelectorAll('.sessions-table tbody tr');

    rows.forEach(row => {
        const type = row.getAttribute('data-type') || '';
        row.style.display = (!filter || type === filter) ? '' : 'none';
    });
}
</script>
@endsection
