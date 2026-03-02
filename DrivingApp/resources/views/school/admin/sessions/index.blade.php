@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Session Completions')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
<<<<<<< HEAD
=======
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $useGradient = $settings?->use_gradient_header ?? true;
>>>>>>> deploy-testing
@endphp

@include('school.admin.partials.admin-styles')

<style>
<<<<<<< HEAD
    .page-wrap {
        max-width: 1500px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 3px solid {{ $primaryColor }};
        padding-bottom: 12px;
    }

    .page-title {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 700;
        color: #111827;
    }

    .subtitle {
        margin-top: 6px;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .filters {
        background: #fff;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .filters input,
    .filters select {
        width: 100%;
        padding: 9px 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .filters .btn {
        border: none;
        border-radius: 8px;
        padding: 10px 14px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-apply {
        background: {{ $primaryColor }};
        color: #fff;
    }

    .btn-clear {
        background: #f3f4f6;
        color: #374151;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .table-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .table-top {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #374151;
        font-size: 0.9rem;
    }

    table {
=======
    /* ── Filter Bar ── */
    .filter-bar {
        background: white;
        border-radius: 12px;
        padding: 14px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .filter-bar label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
    }

    .filter-select {
        padding: 8px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #374151;
        background: white;
        cursor: pointer;
        min-width: 150px;
        outline: none;
        transition: border-color 0.2s;
    }

    .filter-select:focus { border-color: {{ $primaryColor }}; }

    /* ── Sessions Table ── */
    .sessions-table-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .table-header {
        padding: 18px 24px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .sessions-table {
>>>>>>> deploy-testing
        width: 100%;
        border-collapse: collapse;
    }

<<<<<<< HEAD
    thead th {
        text-align: left;
        background: #f9fafb;
        color: #6b7280;
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 11px 14px;
        border-bottom: 1px solid #e5e7eb;
    }

    tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
        font-size: 0.9rem;
    }

    tbody tr:hover {
        background: #fafafa;
    }

    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-theoretical { background: #dbeafe; color: #1d4ed8; }
    .badge-practical { background: #ede9fe; color: #6d28d9; }

    .empty {
        text-align: center;
        padding: 24px;
        color: #6b7280;
    }

    .pagination-wrap {
        padding: 12px 16px;
    }
</style>

<div class="page-wrap">
    <div class="page-header">
        <div>
            <h1 class="page-title">Session Completions</h1>
            <div class="subtitle">Review all instructor logged sessions</div>
        </div>
    </div>

    <form class="filters" method="GET" action="{{ route('schools.admin.sessions.index', ['school' => $school->slug]) }}">
        <div>
            <label for="session_type">Session Type</label>
            <select id="session_type" name="session_type">
                <option value="">All Types</option>
                <option value="theoretical" {{ request('session_type') === 'theoretical' ? 'selected' : '' }}>Theoretical</option>
                <option value="practical" {{ request('session_type') === 'practical' ? 'selected' : '' }}>Practical</option>
            </select>
        </div>

        <div>
            <label for="instructor_id">Instructor</label>
            <select id="instructor_id" name="instructor_id">
                <option value="">All Instructors</option>
                @foreach($instructors as $instructor)
                    <option value="{{ $instructor->id }}" {{ (string) request('instructor_id') === (string) $instructor->id ? 'selected' : '' }}>
                        {{ $instructor->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="date_from">Date From</label>
            <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}">
        </div>

        <div>
            <label for="date_to">Date To</label>
            <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}">
        </div>

        <div style="display:flex; gap:8px; align-items:end;">
            <button type="submit" class="btn btn-apply">Apply</button>
            <a class="btn btn-clear" href="{{ route('schools.admin.sessions.index', ['school' => $school->slug]) }}">Clear</a>
        </div>
    </form>

    <div class="table-card">
        <div class="table-top">
            <span>Total records: {{ $sessions->total() }}</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Instructor</th>
                    <th>Type</th>
                    <th>Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>{{ optional($session->session_date)->format('M d, Y') ?? 'N/A' }}</td>
                        <td>{{ $session->session_time ? \Carbon\Carbon::parse($session->session_time)->format('h:i A') : 'N/A' }}</td>
                        <td>{{ $session->enrollment->student->name ?? $session->enrollment->learner->name ?? 'N/A' }}</td>
                        <td>{{ $session->enrollment->course->title ?? 'N/A' }}</td>
                        <td>{{ $session->instructor->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $session->session_type === 'theoretical' ? 'badge-theoretical' : 'badge-practical' }}">
                                {{ $session->session_type ?? 'N/A' }}
                            </span>
                        </td>
                        <td>{{ number_format((float) ($session->hours_completed ?? 0), 1) }}</td>
                        <td>{{ ucfirst($session->status ?? 'completed') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty">No session completions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($sessions->hasPages())
            <div class="pagination-wrap">
                {{ $sessions->appends(request()->query())->links() }}
=======
    .sessions-table thead th {
        background: #f9fafb;
        padding: 12px 20px;
        text-align: left;
        font-size: 0.78rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #e5e7eb;
    }

    .sessions-table tbody td {
        padding: 14px 20px;
        font-size: 0.88rem;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .sessions-table tbody tr:hover { background: #fafbfc; }
    .sessions-table tbody tr:last-child td { border-bottom: none; }

    /* ── Student/Instructor Info ── */
    .person-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .person-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: {{ $primaryColor }};
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    .person-avatar.instructor-avatar {
        background: #f59e0b;
    }

    .person-name { font-weight: 600; color: #1f2937; }

    /* ── Badges ── */
    .type-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 16px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .type-theoretical { background: #dbeafe; color: #1e40af; }
    .type-practical { background: #ede9fe; color: #5b21b6; }

    .hours-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 16px;
        font-size: 0.78rem;
        font-weight: 600;
        background: #d1fae5;
        color: #065f46;
    }

    .date-cell .date { font-weight: 600; color: #1f2937; }
    .date-cell .time { font-size: 0.78rem; color: #9ca3af; }

    /* ── Action Buttons ── */
    .action-btns { display: flex; gap: 6px; }

    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-view { background: #dbeafe; color: #1e40af; }
    .btn-view:hover { background: #bfdbfe; color: #1e40af; }
    .btn-delete { background: #fee2e2; color: #dc2626; }
    .btn-delete:hover { background: #fecaca; color: #dc2626; }

    /* ── Empty State ── */
    .empty-state { text-align: center; padding: 48px 20px; }

    .empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #f3f4f6;
        color: #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }

    .empty-state h3 { font-size: 1.05rem; color: #374151; margin: 0 0 6px 0; }
    .empty-state p { font-size: 0.88rem; color: #9ca3af; margin: 0 0 16px 0; }

    /* ── Pagination ── */
    .table-footer {
        padding: 14px 24px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pagination-info { font-size: 0.82rem; color: #6b7280; }

    @media (max-width: 768px) {
        .filter-bar { flex-direction: column; align-items: stretch; }
        
        .admin-container { padding: 12px; }
        .page-header { flex-direction: column; gap: 12px; align-items: flex-start; }
        .page-title { font-size: 1.3rem; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .stat-value { font-size: 1.3rem; }
        
        /* Hide table, show cards */
        .table-container { display: none; }
        .session-mobile-card { display: block; }
        
        .btn-action { min-height: 44px; min-width: 44px; padding: 10px; }
    }
    
    @media (max-width: 480px) {
        .admin-container { padding: 8px; }
        .page-title { font-size: 1.1rem; }
        .stats-grid { grid-template-columns: 1fr; }
    }
    
    /* Session mobile cards */
    .session-mobile-card {
        display: none;
        background: white;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        border-left: 4px solid var(--primary-color, #667eea);
    }
    .session-mobile-card .card-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .session-mobile-card .card-row:last-child { border-bottom: none; }
    .session-mobile-card .card-label { color: #6b7280; font-size: 0.8rem; }
    .session-mobile-card .card-val { font-weight: 600; color: #1f2937; font-size: 0.85rem; }
    .session-mobile-card .card-actions {
        display: flex; gap: 8px; margin-top: 10px;
    }
    .session-mobile-card .card-actions a, 
    .session-mobile-card .card-actions button {
        flex: 1; text-align: center; padding: 10px; border-radius: 8px;
        text-decoration: none; font-size: 0.85rem; font-weight: 600; min-height: 44px;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        border: none; cursor: pointer;
    }
    .session-mobile-card .card-actions .btn-view-card { background: #eff6ff; color: #2563eb; }
    .session-mobile-card .card-actions .btn-delete-card { background: #fee2e2; color: #dc2626; }

    .icon-16 {
        width: 16px;
        height: 16px;
    }

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .icon-36 {
        width: 36px;
        height: 36px;
    }

    .table-session-count {
        font-size: 0.82rem;
        color: #9ca3af;
    }

    .inline-form {
        display: inline;
    }

    .session-mobile-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .form-flex-1 {
        flex: 1;
    }

    .btn-full-width {
        width: 100%;
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Session Completions</h1>
            <p class="page-subtitle">View all logged driving sessions by instructors</p>
        </div>
    </div>

    <!-- Stats Cards -->
    @php
        $totalSessions = $stats['total_sessions'] ?? 0;
        $totalHours = $stats['total_hours'] ?? 0;
        $theoreticalCount = $stats['theoretical_count'] ?? 0;
        $practicalCount = $stats['practical_count'] ?? 0;
    @endphp
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Sessions</div>
                        <div class="stat-value">{{ $totalSessions }}</div>
                    </div>
                    <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                </div>
                <div class="stat-detail">All logged sessions</div>
            </div>
        </div>
        <div class="stat-card growth">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Hours Logged</div>
                        <div class="stat-value">{{ number_format($totalHours, 1) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Total teaching hours</div>
            </div>
        </div>
        <div class="stat-card students">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Theoretical</div>
                        <div class="stat-value">{{ $theoreticalCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Classroom sessions</div>
            </div>
        </div>
        <div class="stat-card instructors">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Practical</div>
                        <div class="stat-value">{{ $practicalCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Driving sessions</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ school_route('admin.sessions.index') }}" class="filter-bar">
        <label>Filter by:</label>
        <select class="filter-select" name="session_type" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="theoretical" {{ request('session_type') === 'theoretical' ? 'selected' : '' }}>Theoretical</option>
            <option value="practical" {{ request('session_type') === 'practical' ? 'selected' : '' }}>Practical</option>
        </select>
        <select class="filter-select" name="instructor_id" onchange="this.form.submit()">
            <option value="">All Instructors</option>
            @foreach($instructors as $instructor)
                <option value="{{ $instructor->id }}" {{ request('instructor_id') == $instructor->id ? 'selected' : '' }}>
                    {{ $instructor->name }}
                </option>
            @endforeach
        </select>
        <input type="date" class="filter-select" name="date_from" value="{{ request('date_from') }}" placeholder="From Date" onchange="this.form.submit()">
        <input type="date" class="filter-select" name="date_to" value="{{ request('date_to') }}" placeholder="To Date" onchange="this.form.submit()">
    </form>

    <!-- Sessions Table -->
    <div class="sessions-table-wrapper">
        <div class="table-header">
            <h2 class="table-title">Session History</h2>
            <span class="table-session-count">{{ $totalSessions }} {{ Str::plural('session', $totalSessions) }}</span>
        </div>

        @if($sessions->count() > 0)
            <div class="table-container">
            <table class="sessions-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Student</th>
                        <th>Instructor</th>
                        <th>Course</th>
                        <th>Type</th>
                        <th>Hours</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                    <tr>
                        <td>
                            <div class="date-cell">
                                <div class="date">{{ $session->session_date->format('M d, Y') }}</div>
                                <div class="time">{{ \Carbon\Carbon::parse($session->session_time)->format('g:i A') }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="person-info">
                                <div class="person-avatar">
                                    {{ strtoupper(substr($session->enrollment->student->name ?? 'N', 0, 1)) }}
                                </div>
                                <span class="person-name">{{ $session->enrollment->student->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="person-info">
                                <div class="person-avatar instructor-avatar">
                                    {{ strtoupper(substr($session->instructor->name ?? 'N', 0, 1)) }}
                                </div>
                                <span class="person-name">{{ $session->instructor->name ?? 'N/A' }}</span>
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
                                <a href="{{ school_route('admin.sessions.show', ['sessionCompletion' => $session->id]) }}" 
                                   class="btn-action btn-view" title="View Details">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ school_route('admin.sessions.destroy', ['sessionCompletion' => $session->id]) }}" 
                                      class="inline-form" onsubmit="return confirm('Are you sure you want to delete this session?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Delete Session">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            
            {{-- Mobile card view --}}
            @foreach($sessions as $session)
            <div class="session-mobile-card">
                <div class="session-mobile-card-header">
                    <strong>{{ $session->enrollment->student->name ?? 'N/A' }}</strong>
                    <span class="type-badge type-{{ $session->session_type }}">{{ ucfirst($session->session_type) }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Date</span>
                    <span class="card-val">{{ $session->session_date->format('M d, Y') }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Time</span>
                    <span class="card-val">{{ \Carbon\Carbon::parse($session->session_time)->format('g:i A') }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Instructor</span>
                    <span class="card-val">{{ $session->instructor->name ?? 'N/A' }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Course</span>
                    <span class="card-val">{{ $session->enrollment->course->title ?? 'N/A' }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Hours</span>
                    <span class="card-val">{{ number_format($session->hours_completed, 1) }}h</span>
                </div>
                <div class="card-actions">
                    <a href="{{ school_route('admin.sessions.show', ['sessionCompletion' => $session->id]) }}" class="btn-view-card">View</a>
                    <form method="POST" action="{{ school_route('admin.sessions.destroy', ['sessionCompletion' => $session->id]) }}" 
                                                    class="form-flex-1" onsubmit="return confirm('Are you sure you want to delete this session?')">
                        @csrf
                        @method('DELETE')
                                                <button type="submit" class="btn-delete-card btn-full-width">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach

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
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-36">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <h3>No sessions logged yet</h3>
                <p>Instructors will log their driving sessions which will appear here.</p>
>>>>>>> deploy-testing
            </div>
        @endif
    </div>
</div>
@endsection
