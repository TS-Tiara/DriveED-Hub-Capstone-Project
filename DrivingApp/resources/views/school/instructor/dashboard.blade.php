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

@include('school.admin.partials.admin-styles')

<style>
    /* ── Dashboard sections ── */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .dashboard-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .dashboard-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 16px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dashboard-card-title svg {
        color: {{ $primaryColor }};
    }

    /* ── Next lesson card ── */
    .next-lesson {
        padding: 16px;
        border-radius: 10px;
        border-left: 4px solid {{ $primaryColor }};
        background: #f9fafb;
    }

    .lesson-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .lesson-date {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .lesson-time {
        font-size: 0.9rem;
        color: #4b5563;
        margin-bottom: 8px;
    }

    .lesson-status {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-scheduled { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    /* ── Bookings list ── */
    .bookings-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .booking-item {
        padding: 12px 14px;
        background: #f9fafb;
        border-radius: 8px;
        border-left: 3px solid {{ $primaryColor }};
    }

    .booking-student {
        font-size: 0.92rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .booking-course {
        font-size: 0.82rem;
        color: #6b7280;
        margin-bottom: 2px;
    }

    .booking-datetime {
        font-size: 0.78rem;
        color: #9ca3af;
        margin-bottom: 4px;
    }

    .booking-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .no-data {
        text-align: center;
        color: #9ca3af;
        font-size: 0.9rem;
        padding: 24px;
    }

    /* ── Quick actions ── */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-top: 20px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 16px;
        @if($useGradient)
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
        background: {{ $primaryColor }};
        @endif
        color: white;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 500;
        font-size: 0.9rem;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.18);
        color: white;
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .dashboard-grid { grid-template-columns: 1fr; }
        .actions-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 480px) {
        .actions-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back — here's your overview for today</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card active">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Today's Lessons</div>
                        <div class="stat-value">{{ $todaysLessons }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Scheduled for today</div>
            </div>
        </div>
        <div class="stat-card students">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">This Week</div>
                        <div class="stat-value">{{ $weeklyLessons }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Sessions this week</div>
            </div>
        </div>
        <div class="stat-card growth">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active Students</div>
                        <div class="stat-value">{{ $activeStudents }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Currently assigned to you</div>
            </div>
        </div>
        <div class="stat-card inactive">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending Schedules</div>
                        <div class="stat-value">{{ $pendingBookings }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Awaiting confirmation</div>
            </div>
        </div>
    </div>

    <!-- Schedule Overview + Upcoming -->
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h2 class="dashboard-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Schedule Overview
            </h2>
            @if($nextLesson)
                <div class="next-lesson">
                    <div class="lesson-label">Next Lesson</div>
                    <div class="lesson-date">{{ \Carbon\Carbon::parse($nextLesson->date)->format('l, F j, Y') }}</div>
                    <div class="lesson-time">{{ \Carbon\Carbon::parse($nextLesson->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($nextLesson->end_time)->format('g:i A') }}</div>
                    <div class="lesson-status status-{{ $nextLesson->status }}">{{ ucfirst($nextLesson->status) }}</div>
                </div>
            @else
                <div class="empty-state" style="padding: 24px 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="empty-state-title">No upcoming lessons</p>
                    <p class="empty-state-text">Scheduled lessons will appear here</p>
                </div>
            @endif
        </div>

        <div class="dashboard-card">
            <h2 class="dashboard-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Upcoming Schedules
            </h2>
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
                <div class="empty-state" style="padding: 24px 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="empty-state-title">No upcoming schedules</p>
                    <p class="empty-state-text">Your schedule will appear here</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="actions-grid">
        <a href="{{ $schoolRoute('instructor.schedule') }}" class="action-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            My Schedule
        </a>
        <a href="{{ $schoolRoute('instructor.sessions.create') }}" class="action-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Log Session
        </a>
        <a href="{{ $schoolRoute('instructor.grades') }}" class="action-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Grade Students
        </a>
        <a href="{{ $schoolRoute('instructor.students.index') }}" class="action-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            My Students
        </a>
    </div>
</div>
@endsection
