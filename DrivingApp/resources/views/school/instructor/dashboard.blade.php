@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Instructor Dashboard')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $useGradient = $settings?->use_gradient_header ?? true;
@endphp

<style>
    .instructor-dashboard {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 4px solid {{ $primaryColor }};
    }

    .page-title {
        font-size: 2rem;
        color: #111827;
        margin: 0;
        font-weight: 400;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .stat-card h3 {
        margin: 0 0 15px 0;
        font-size: 14px;
        color: #666;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 700;
        color: {{ $primaryColor }};
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .dashboard-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .dashboard-card h2 {
        margin: 0 0 20px 0;
        font-size: 18px;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid {{ $primaryColor }};
        padding-bottom: 10px;
    }

    .next-lesson {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid {{ $primaryColor }};
    }

    .lesson-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .lesson-date {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .lesson-time {
        font-size: 16px;
        color: #555;
        margin-bottom: 10px;
    }

    .lesson-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-scheduled {
        background: #e3f2fd;
        color: #1976d2;
    }

    .status-completed {
        background: #e8f5e9;
        color: #388e3c;
    }

    .status-cancelled {
        background: #ffebee;
        color: #d32f2f;
    }

    .bookings-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .booking-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 3px solid {{ $primaryColor }};
    }

    .booking-student {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .booking-course {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }

    .booking-datetime {
        font-size: 13px;
        color: #888;
        margin-bottom: 8px;
    }

    .booking-status {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
    }

    .no-data {
        text-align: center;
        color: #999;
        font-style: italic;
        padding: 20px;
    }

    .quick-actions {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .quick-actions h2 {
        margin: 0 0 20px 0;
        font-size: 18px;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid {{ $primaryColor }};
        padding-bottom: 10px;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 15px 20px;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: white;
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .instructor-dashboard {
            padding: 15px;
            margin: 0 auto;
            width: 100%;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .stat-card {
            padding: 15px;
        }

        .stat-card h3 {
            font-size: 11px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 22px;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .dashboard-card {
            padding: 18px;
        }

        .dashboard-card h2 {
            font-size: 15px;
            margin-bottom: 12px;
        }

        .next-lesson {
            padding: 15px;
        }

        .lesson-label {
            font-size: 10px;
        }

        .lesson-date {
            font-size: 15px;
        }

        .lesson-time {
            font-size: 13px;
        }

        .lesson-status {
            font-size: 10px;
            padding: 3px 10px;
        }

        .booking-student {
            font-size: 13px;
        }

        .booking-course {
            font-size: 11px;
        }

        .booking-datetime {
            font-size: 11px;
        }

        .booking-status {
            font-size: 9px;
            padding: 2px 8px;
        }

        .actions-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .action-btn {
            padding: 10px 14px;
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .instructor-dashboard {
            padding: 10px;
            margin: 0 auto;
            width: 100%;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .stat-card {
            padding: 12px;
        }

        .stat-card h3 {
            font-size: 10px;
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 18px;
        }

        .dashboard-grid {
            gap: 12px;
        }

        .dashboard-card {
            padding: 15px;
        }

        .dashboard-card h2 {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .next-lesson {
            padding: 12px;
        }

        .lesson-label {
            font-size: 9px;
        }

        .lesson-date {
            font-size: 14px;
        }

        .lesson-time {
            font-size: 12px;
        }

        .bookings-list {
            gap: 10px;
        }

        .booking-item {
            padding: 10px;
        }

        .booking-student {
            font-size: 12px;
        }

        .booking-course {
            font-size: 10px;
        }

        .booking-datetime {
            font-size: 10px;
        }

        .quick-actions {
            padding: 15px;
        }

        .quick-actions h2 {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .actions-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .action-btn {
            padding: 9px 12px;
            font-size: 11px;
        }
    }
</style>

<div class="instructor-dashboard">
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
    </div>
    
    <!-- Key Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Today's Lessons</h3>
            <div class="stat-value">{{ $todaysLessons }}</div>
        </div>
        <div class="stat-card">
            <h3>This Week</h3>
            <div class="stat-value">{{ $weeklyLessons }}</div>
        </div>
        <div class="stat-card">
            <h3>Active Students</h3>
            <div class="stat-value">{{ $activeStudents }}</div>
        </div>
        <div class="stat-card">
            <h3>Pending Schedules</h3>
            <div class="stat-value">{{ $pendingBookings }}</div>
        </div>
    </div>

    <!-- Schedule Overview + Upcoming -->
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h2>Schedule Overview</h2>
            @if($nextLesson)
                <div class="next-lesson">
                    <div class="lesson-label">Next Lesson</div>
                    <div class="lesson-date">{{ \Carbon\Carbon::parse($nextLesson->date)->format('l, F j, Y') }}</div>
                    <div class="lesson-time">{{ \Carbon\Carbon::parse($nextLesson->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($nextLesson->end_time)->format('g:i A') }}</div>
                    <div class="lesson-status status-{{ $nextLesson->status }}">{{ ucfirst($nextLesson->status) }}</div>
                </div>
            @else
                <p class="no-data">No upcoming lessons scheduled</p>
            @endif
        </div>

        <div class="dashboard-card">
            <h2>Upcoming Schedules</h2>
            @if($upcomingBookings->count() > 0)
                <div class="bookings-list">
                    @foreach($upcomingBookings as $booking)
                        <div class="booking-item">
                            <div class="booking-student">{{ $booking->student->name }}</div>
                            <div class="booking-course">{{ $booking->course->title }}</div>
                            <div class="booking-datetime">{{ \Carbon\Carbon::parse($booking->scheduled_at)->format('M j, Y g:i A') }}</div>
                            <div class="booking-status status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="no-data">No upcoming schedules</p>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="actions-grid">
            <a href="{{ $schoolRoute('instructor.schedule') }}" class="action-btn">My Schedule</a>
            <a href="{{ $schoolRoute('instructor.sessions.create') }}" class="action-btn">Log Session</a>
            <a href="{{ $schoolRoute('instructor.grades') }}" class="action-btn">Grade Students</a>
            <a href="{{ $schoolRoute('instructor.students.index') }}" class="action-btn">My Students</a>
        </div>
    </div>
</div>
@endsection
