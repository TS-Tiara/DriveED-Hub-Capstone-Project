@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Instructor Dashboard')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school->schoolSetting;
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
        border-bottom: 4px solid {{ $settings->primary_color ?? '#667eea' }};
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
        color: {{ $settings->primary_color ?? '#667eea' }};
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
        border-bottom: 2px solid {{ $settings->primary_color ?? '#667eea' }};
        padding-bottom: 10px;
    }

    .next-lesson {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid {{ $settings->primary_color ?? '#667eea' }};
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

    .metrics-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .metric-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: background 0.2s;
    }

    .metric-item:hover {
        background: #e9ecef;
    }

    .metric-label {
        font-size: 14px;
        color: #666;
        font-weight: 500;
    }

    .metric-value {
        font-size: 20px;
        font-weight: 700;
        color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    .bookings-list,
    .progress-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .booking-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 3px solid {{ $school->schoolSetting->primary_color ?? '#667eea' }};
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

    .progress-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
    }

    .progress-student {
        font-size: 15px;
        font-weight: 600;
        color: #333;
    }

    .progress-percent {
        font-size: 16px;
        font-weight: 700;
        color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    .progress-course {
        font-size: 13px;
        color: #666;
        margin-bottom: 10px;
    }

    .progress-bar {
        background: #e0e0e0;
        border-radius: 10px;
        height: 8px;
        overflow: hidden;
        margin-bottom: 8px;
    }

    .progress-fill {
        @if($school->schoolSetting->use_gradient_header)
            background: linear-gradient(90deg, {{ $school->schoolSetting->primary_color }} 0%, {{ $school->schoolSetting->secondary_color }} 100%);
        @else
            background: {{ $school->schoolSetting->primary_color }};
        @endif
        height: 100%;
        transition: width 0.3s ease;
    }

    .progress-notes {
        font-size: 12px;
        color: #777;
        font-style: italic;
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
        @if($school->schoolSetting->use_gradient_header)
            background: linear-gradient(135deg, {{ $school->schoolSetting->primary_color }} 0%, {{ $school->schoolSetting->secondary_color }} 100%);
        @else
            background: {{ $school->schoolSetting->primary_color }};
        @endif
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }

    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .action-btn .badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #f44336;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .instructor-dashboard {
            padding: 15px;
            margin: 0 auto;
            width: 100%;
        }

        .dashboard-header {
            padding: 20px;
        }

        .dashboard-header h1 {
            font-size: 18px;
        }

        .dashboard-header p {
            font-size: 13px;
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

        .metric-item {
            padding: 10px 12px;
        }

        .metric-label {
            font-size: 12px;
        }

        .metric-value {
            font-size: 16px;
        }

        .booking-student,
        .progress-student {
            font-size: 13px;
        }

        .booking-course,
        .progress-course {
            font-size: 11px;
        }

        .booking-datetime {
            font-size: 11px;
        }

        .booking-status {
            font-size: 9px;
            padding: 2px 8px;
        }

        .progress-percent {
            font-size: 13px;
        }

        .progress-notes {
            font-size: 10px;
        }

        .actions-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .action-btn {
            padding: 10px 14px;
            font-size: 12px;
        }

        .action-btn .badge {
            width: 18px;
            height: 18px;
            font-size: 10px;
        }
    }

    @media (max-width: 480px) {
        .instructor-dashboard {
            padding: 10px;
            margin: 0 auto;
            width: 100%;
        }

        .dashboard-header {
            padding: 15px;
        }

        .dashboard-header h1 {
            font-size: 16px;
        }

        .dashboard-header p {
            font-size: 12px;
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

        .metrics-list {
            gap: 10px;
        }

        .metric-item {
            padding: 8px 10px;
        }

        .metric-label {
            font-size: 11px;
        }

        .metric-value {
            font-size: 14px;
        }

        .bookings-list,
        .progress-list {
            gap: 10px;
        }

        .booking-item,
        .progress-item {
            padding: 10px;
        }

        .booking-student,
        .progress-student {
            font-size: 12px;
        }

        .booking-course,
        .progress-course {
            font-size: 10px;
        }

        .booking-datetime {
            font-size: 10px;
        }

        .progress-notes {
            font-size: 9px;
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

        .action-btn .badge {
            width: 16px;
            height: 16px;
            font-size: 9px;
            top: -5px;
            right: -5px;
        }
    }
</style>

<div class="instructor-dashboard">
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
    </div>
    
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

    <div class="dashboard-grid">
        <!-- Schedule Overview -->
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

        <!-- Student & Bookings Stats -->
        <div class="dashboard-card">
            <h2>Student & Bookings</h2>
            <div class="metrics-list">
                <div class="metric-item">
                    <span class="metric-label">Total Completed</span>
                    <span class="metric-value">{{ $totalCompleted }}</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">This Month</span>
                    <span class="metric-value">{{ $monthlyBookings }}</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Completed This Month</span>
                    <span class="metric-value">{{ $completedThisMonth }}</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Pending</span>
                    <span class="metric-value">{{ $pendingBookings }}</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Active Students</span>
                    <span class="metric-value">{{ $activeStudents }}</span>
                </div>
            </div>
        </div>

        <!-- Upcoming Bookings -->
        <div class="dashboard-card">
            <h2>Upcoming Bookings</h2>
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

        <!-- Recent Progress -->
        <div class="dashboard-card">
            <h2>Recent Progress Updates</h2>
            @if($recentProgress->count() > 0)
                <div class="progress-list">
                    @foreach($recentProgress as $progress)
                        <div class="progress-item">
                            <div class="progress-header">
                                <span class="progress-student">{{ $progress->student->name }}</span>
                                <span class="progress-percent">{{ $progress->completion_percent }}%</span>
                            </div>
                            <div class="progress-course">{{ $progress->course->title }}</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ $progress->completion_percent }}%"></div>
                            </div>
                            @if($progress->notes)
                                <div class="progress-notes">{{ $progress->notes }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="no-data">No recent progress updates</p>
            @endif
        </div>
    </div>
</div>
@endsection
