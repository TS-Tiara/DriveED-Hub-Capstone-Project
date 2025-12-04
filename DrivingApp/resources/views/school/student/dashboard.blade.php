@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Dashboard')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school->schoolSetting;
@endphp

<style>
    .student-dashboard {
        padding: 20px;
        margin: 20px auto;
        max-width: 1600px;
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

    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-card {
        background: white;
        border-radius: {{ $settings->border_radius ?? 8 }}px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .card-header {
        background: {{ $settings->primary_color }};
        color: white;
        padding: 16px 20px;
        font-weight: 600;
        font-size: 1rem;
    }

    .card-body {
        padding: 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.9375rem;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #6b7280;
    }

    .info-value {
        color: #111827;
        font-weight: 600;
    }

    .progress-bar-wrapper {
        margin-top: 8px;
    }

    .progress-bar-container {
        width: 100%;
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: #10b981;
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    .quick-actions-section {
        background: {{ $settings->primary_color }};
        border-radius: {{ $settings->border_radius ?? 8 }}px;
        padding: 24px;
        margin-bottom: 30px;
    }

    .quick-actions-title {
        color: white;
        font-size: 1.125rem;
        font-weight: 600;
        margin: 0 0 20px 0;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
    }

    .action-btn {
        background: white;
        color: {{ $settings->primary_color }};
        padding: 12px 16px;
        border-radius: {{ $settings->border_radius ?? 8 }}px;
        text-align: center;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9375rem;
        transition: all 0.3s ease;
        display: block;
        border: 2px solid white;
    }

    .action-btn:hover {
        background: {{ $settings->secondary_color ?? $settings->primary_color }};
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        text-decoration: none;
    }

    .next-lesson-section {
        background: {{ $settings->primary_color }};
        border-radius: {{ $settings->border_radius ?? 8 }}px;
        padding: 20px 24px;
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .next-lesson-icon {
        font-size: 1.5rem;
    }

    .next-lesson-title {
        font-weight: 600;
        font-size: 1rem;
        margin: 0;
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: #9ca3af;
        background: white;
        border-radius: {{ $settings->border_radius ?? 8 }}px;
        border: 1px solid #e5e7eb;
    }

    .empty-state-icon {
        font-size: 3rem;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    .empty-state-text {
        margin: 0 0 20px 0;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #6b7280;
    }

    @media (max-width: 1200px) {
        .dashboard-cards {
            grid-template-columns: 1fr;
        }

        .quick-actions-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .student-dashboard {
            padding: 16px;
        }
        
        .page-title {
            font-size: 1.5rem;
        }

        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="student-dashboard">
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
    </div>

    <div class="dashboard-cards">
        <div class="info-card">
            <div class="card-header">Learning Progress</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Lesson Completed</span>
                    <span class="info-value">{{ $totalLessons }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Hours Driven</span>
                    <span class="info-value">{{ $hoursDriven }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Current Level</span>
                    <span class="info-value">Intermediate</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value">{{ $progressPercentage }}%</span>
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="card-header">Upcoming Lessons</div>
            <div class="card-body">
                @if($nextLessons && count($nextLessons) > 0)
                    @php $nextLesson = $nextLessons->first(); @endphp
                    <div class="info-row">
                        <span class="info-label">Next Lesson</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($nextLesson->date)->format('M d, Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">{{ $nextLesson->instructor->name ?? 'TBA' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Lesson Type</span>
                        <span class="info-value">{{ $nextLesson->course->title ?? 'Driving Lesson' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">This Week</span>
                        <span class="info-value">{{ $upcomingLessons }} lessons</span>
                    </div>
                @else
                    <div class="info-row">
                        <span class="info-label">Next Lesson</span>
                        <span class="info-value">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Lesson Type</span>
                        <span class="info-value">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">This Week</span>
                        <span class="info-value">0 lessons</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="info-card">
            <div class="card-header">Goals & Achievements</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Test Readiness</span>
                    <span class="info-value">{{ $testReadiness }}%</span>
                </div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: {{ $testReadiness }}%"></div>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Skills Mastered</span>
                    <span class="info-value">{{ min(10, floor($totalLessons / 2)) }}/10</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Practice Hours</span>
                    <span class="info-value">{{ $hoursDriven }}/{{ $requiredHours }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Est. Test Date</span>
                    <span class="info-value">{{ $progressPercentage >= 80 ? \Carbon\Carbon::now()->addWeeks(2)->format('M d, Y') : 'TBD' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="quick-actions-section">
        <h2 class="quick-actions-title">Quick Actions</h2>
        <div class="quick-actions-grid">
            <a href="{{ $schoolRoute('student.courses.index') }}" class="action-btn" onclick="loadContent(this.href); return false;">
                Browse Courses
            </a>
            <a href="{{ $schoolRoute('student.schedule') }}" class="action-btn" onclick="loadContent(this.href); return false;">
                My Schedule
            </a>
            <a href="{{ $schoolRoute('student.payments.index') }}" class="action-btn" onclick="loadContent(this.href); return false;">
                Payments
            </a>
            <a href="{{ $schoolRoute('student.progress.index') }}" class="action-btn" onclick="loadContent(this.href); return false;">
                My Progress
            </a>
            <a href="{{ $schoolRoute('student.profile') }}" class="action-btn" onclick="loadContent(this.href); return false;">
                Update Profile
            </a>
        </div>
    </div>

    <div class="next-lesson-section">
        @if($nextLessons && count($nextLessons) > 0)
            <h3 class="next-lesson-title">Next Lesson</h3>
        @else
            <h3 class="next-lesson-title">No Upcoming Lesson</h3>
        @endif
    </div>

    @if(!$nextLessons || count($nextLessons) == 0)
    <div class="empty-state">
        <p class="empty-state-text">No Upcoming Lesson</p>
    </div>
    @endif
</div>
@endsection
