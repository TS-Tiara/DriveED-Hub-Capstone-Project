@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Session Completions')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<style>
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
        width: 100%;
        border-collapse: collapse;
    }

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
            </div>
        @endif
    </div>
</div>
@endsection
