@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Details')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
@endphp

<style>
    .student-detail-container {
        padding: 30px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .back-btn {
        background: white;
        border: 2px solid #e5e7eb;
        padding: 12px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 20px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .back-btn:hover {
        background: {{ $school->schoolSetting->primary_color ?? '#1e40af' }};
        color: white;
        border-color: {{ $school->schoolSetting->primary_color ?? '#1e40af' }};
    }

    .student-header-card {
        background: white;
        padding: 40px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border-left: 6px solid {{ $school->schoolSetting->primary_color ?? '#f59e0b' }};
    }

    .student-header-content {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 30px;
    }

    .student-main-info {
        flex: 1;
    }

    .student-main-info h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a202c;
        margin: 0 0 16px 0;
    }

    .student-main-info p {
        color: #6b7280;
        margin: 10px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1rem;
    }

    .student-main-info p strong {
        color: #374151;
        min-width: 90px;
    }

    .progress-summary {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        min-width: 300px;
    }

    .progress-item {
        text-align: center;
        background: #f9fafb;
        padding: 20px;
        border-radius: 8px;
    }

    .progress-value {
        font-size: 2rem;
        font-weight: 700;
        color: {{ $school->schoolSetting->primary_color ?? '#1e40af' }};
        display: block;
    }

    .progress-label {
        font-size: 0.9rem;
        color: #6b7280;
        display: block;
        margin-top: 6px;
        font-weight: 600;
    }

    .section-header {
        margin-bottom: 20px;
    }

    .section-header h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .section-header h2 i {
        color: {{ $school->schoolSetting->primary_color ?? '#1e40af' }};
    }

    .section-subtitle {
        font-size: 0.9rem;
        color: #6b7280;
        margin: 8px 0 0 0;
    }

    /* Sessions List */
    .sessions-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .sessions-list {
        display: grid;
        gap: 16px;
    }

    .session-card {
        background: #fafafa;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        border-left: 4px solid #9ca3af;
    }

    .session-card.my-session {
        background: white;
        border-left: 4px solid {{ $school->schoolSetting->primary_color ?? '#1e40af' }};
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .session-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .session-date {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1a202c;
    }

    .session-course {
        font-size: 0.9rem;
        color: {{ $school->schoolSetting->primary_color ?? '#1e40af' }};
        margin-top: 4px;
        font-weight: 600;
    }

    .session-instructor {
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .session-badges {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
    }

    .my-session-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        background: {{ $school->schoolSetting->primary_color ?? '#1e40af' }}20;
        color: {{ $school->schoolSetting->primary_color ?? '#1e40af' }};
        text-transform: uppercase;
    }

    .session-status {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-scheduled {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-no-show {
        background: #fef3c7;
        color: #92400e;
    }

    .session-notes {
        padding: 14px;
        background: #fffbeb;
        border-left: 3px solid #f59e0b;
        border-radius: 6px;
        font-size: 0.9rem;
        color: #92400e;
    }

    .no-notes {
        color: #9ca3af;
        font-style: italic;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .empty-state i {
        font-size: 3.5rem;
        color: #d1d5db;
        margin-bottom: 15px;
    }

    .empty-state p {
        color: #6b7280;
        font-size: 1rem;
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .student-detail-container {
            padding: 15px;
        }

        .student-header-card {
            padding: 25px;
        }

        .student-header-content {
            flex-direction: column;
            gap: 20px;
        }

        .student-main-info h1 {
            font-size: 1.8rem;
        }

        .progress-summary {
            grid-template-columns: repeat(2, 1fr);
            min-width: auto;
        }

        .progress-item {
            padding: 15px;
        }

        .progress-value {
            font-size: 1.6rem;
        }

        .content-tabs {
            padding: 6px;
        }

        .tab-btn {
            padding: 12px 16px;
            font-size: 0.9rem;
        }

        .course-progress-card,
        .session-card {
            padding: 20px;
        }

        .course-name {
            font-size: 1.15rem;
        }

        .course-percent {
            font-size: 1.5rem;
        }

        .session-card {
            padding: 15px;
        }

        .session-header {
            flex-direction: column;
            gap: 10px;
        }
    }

    @media (max-width: 480px) {
        .student-header-card {
            padding: 16px;
        }

        .student-main-info h1 {
            font-size: 1.3rem;
        }

        .progress-value {
            font-size: 1.3rem;
        }

        .progress-label {
            font-size: 0.75rem;
        }

        .tab-btn {
            padding: 10px;
            font-size: 0.9rem;
        }
    }
</style>

<div class="student-detail-container">
    <!-- Back Button -->
    <button class="back-btn" onclick="goBack()">
        <i class="fas fa-arrow-left"></i>
        Back to Students
    </button>

    <!-- Student Header -->
    <div class="student-header-card">
        <div class="student-header-content">
            <div class="student-main-info">
                <h1>{{ $student->name }}</h1>
                @if($student->contact)
                    <p><i class="fas fa-phone"></i> {{ $student->contact }}</p>
                @endif
                <p><i class="fas fa-circle" style="color: {{ $student->status === 'active' ? '#10b981' : '#ef4444' }}; font-size: 0.6rem;"></i> {{ ucfirst($student->status) }} Student</p>
            </div>

            <div class="progress-summary">
                <div class="progress-item">
                    <span class="progress-value">{{ $myCompletedCount }}</span>
                    <span class="progress-label">Sessions with You</span>
                </div>
                <div class="progress-item">
                    <span class="progress-value">{{ $myUpcomingCount }}</span>
                    <span class="progress-label">Your Upcoming</span>
                </div>
                <div class="progress-item">
                    <span class="progress-value">{{ $sessions->count() }}</span>
                    <span class="progress-label">Total Sessions</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Session History Header -->
    <div class="section-header">
        <h2><i class="fas fa-history"></i> Session History for {{ $student->name }}</h2>
        <p class="section-subtitle">View notes from all instructors to maintain teaching continuity</p>
    </div>

    <!-- Sessions List -->
    <div class="sessions-container">
        @if($sessions->count() > 0)
            <div class="sessions-list">
                @foreach($sessions as $session)
                    <div class="session-card {{ $session['is_mine'] ? 'my-session' : '' }}">
                        <div class="session-header">
                            <div>
                                <div class="session-date">{{ \Carbon\Carbon::parse($session['date'])->format('l, M d, Y - g:i A') }}</div>
                                <div class="session-course">{{ $session['course'] }}</div>
                                <div class="session-instructor">
                                    <i class="fas fa-user-tie"></i> 
                                    {{ $session['is_mine'] ? 'You' : $session['instructor_name'] }}
                                </div>
                            </div>
                            <div class="session-badges">
                                @if($session['is_mine'])
                                    <span class="my-session-badge">Your Session</span>
                                @endif
                                <span class="session-status status-{{ $session['status'] }}">{{ ucfirst($session['status']) }}</span>
                            </div>
                        </div>
                        @if($session['notes'])
                            <div class="session-notes">
                                <strong><i class="fas fa-sticky-note"></i> Notes:</strong><br>
                                {{ $session['notes'] }}
                            </div>
                        @else
                            <div class="session-notes no-notes">
                                No notes recorded for this session
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No session history with this student yet</p>
            </div>
        @endif
    </div>
</div>

<script>
function goBack() {
    const url = `{{ url($school->slug . '/instructor/students') }}`;
    if (typeof loadContent === 'function') {
        loadContent(url);
    } else {
        window.location.href = url;
    }
}
</script>

@endsection
