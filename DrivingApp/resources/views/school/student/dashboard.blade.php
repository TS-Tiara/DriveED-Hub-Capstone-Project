@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Dashboard')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#1e3a5f';
    $secondaryColor = $settings->secondary_color ?? '#c5a028';
    $borderRadius = $settings->border_radius ?? 8;
@endphp

<style>
    .student-dashboard {
        padding: 20px;
        margin: 0 auto;
        max-width: 1600px;
    }

    .page-header {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 4px solid {{ $primaryColor }};
    }

    .page-title {
        font-size: 2rem;
        color: #111827;
        margin: 0;
        font-weight: 400;
    }

    /* Desktop Layout - 3 column grid */
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    /* Mobile layouts - hidden by default on desktop */
    .mobile-two-col,
    .mobile-upcoming {
        display: none;
    }

    .info-card {
        background: white;
        border-radius: {{ $borderRadius }}px;
        border: 2px solid {{ $primaryColor }};
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .card-header {
        background: {{ $primaryColor }};
        color: white;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 0.9rem;
        text-align: center;
    }

    .card-body {
        padding: 16px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.875rem;
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

    /* Quick Actions Section */
    .quick-actions-section {
        background: {{ $primaryColor }};
        border-radius: {{ $borderRadius }}px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .quick-actions-title {
        color: white;
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 16px 0;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .action-btn {
        background: white;
        color: {{ $primaryColor }};
        padding: 14px 16px;
        border-radius: 20px;
        text-align: center;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        display: block;
        border: 2px solid white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .action-btn:hover {
        background: {{ $secondaryColor }};
        color: {{ $primaryColor }};
        border-color: {{ $secondaryColor }};
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        text-decoration: none;
    }

    /* Today's Lesson Section */
    .todays-lesson-section {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $primaryColor }}dd 100%);
        border-radius: {{ $borderRadius }}px {{ $borderRadius }}px 0 0;
        padding: 18px 20px;
        color: white;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .todays-lesson-icon {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .todays-lesson-icon svg {
        width: 26px;
        height: 26px;
        fill: {{ $primaryColor }};
    }

    .todays-lesson-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin: 0;
        letter-spacing: 0.3px;
    }

    .todays-lesson-container {
        background: white;
        border-radius: 0 0 {{ $borderRadius }}px {{ $borderRadius }}px;
        border: 2px solid {{ $primaryColor }};
        border-top: none;
        overflow: hidden;
    }

    .empty-state {
        text-align: center;
        padding: 40px 24px;
        color: #9ca3af;
        background: white;
    }

    .empty-state-icon {
        margin-bottom: 12px;
    }

    .empty-state-icon svg {
        width: 48px;
        height: 48px;
        fill: #d1d5db;
    }

    .empty-state-text {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 500;
        color: #6b7280;
    }

    /* Lesson Card Item Styles */
    .lesson-card-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s ease;
    }

    .lesson-card-item:last-child {
        border-bottom: none;
    }

    .lesson-card-item:hover {
        background: #fafafa;
    }

    .lesson-time-badge {
        background: {{ $primaryColor }};
        color: white;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
        min-width: 80px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .lesson-details {
        flex: 1;
    }

    .lesson-course-name {
        font-weight: 600;
        font-size: 1rem;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .lesson-instructor-name {
        display: flex;
        align-items: center;
        font-size: 0.85rem;
        color: #6b7280;
    }

    /* ============================================
       MOBILE RESPONSIVE STYLES 
       ============================================ */
    
    /* Tablets and small desktops - keep desktop layout but adjust grid */
    @media screen and (max-width: 1200px) {
        .dashboard-cards {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* Mobile devices - switch to mobile layout */
    @media screen and (max-width: 768px) {
        .student-dashboard {
            padding: 12px;
        }
        
        .page-header {
            margin-bottom: 16px;
            padding-bottom: 8px;
        }
        
        .page-title {
            font-size: 1.5rem;
        }

        /* HIDE desktop 3-column grid */
        .dashboard-cards {
            display: none !important;
        }

        /* SHOW mobile Upcoming Lessons (full width) */
        .mobile-upcoming {
            display: block !important;
            margin-bottom: 12px;
        }

        /* SHOW mobile two-column layout */
        .mobile-two-col {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 12px !important;
            margin-bottom: 16px;
        }

        .info-card {
            border-width: 2px;
        }

        .card-header {
            padding: 10px 12px;
            font-size: 0.75rem;
        }

        .card-body {
            padding: 12px;
        }

        .info-row {
            padding: 6px 0;
            font-size: 0.7rem;
        }

        .quick-actions-section {
            padding: 16px;
            margin-bottom: 16px;
        }

        .quick-actions-title {
            font-size: 0.9rem;
            margin-bottom: 12px;
        }

        /* Quick Actions - 2x2 grid on mobile */
        .quick-actions-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 10px !important;
        }

        .action-btn {
            padding: 12px 8px;
            font-size: 0.75rem;
            border-radius: 20px;
        }

        .todays-lesson-section {
            padding: 14px 16px;
        }

        .todays-lesson-title {
            font-size: 0.9rem;
        }

        .lesson-card-item {
            padding: 12px 16px;
            gap: 12px;
        }

        .lesson-time-badge {
            padding: 6px 10px;
            font-size: 0.75rem;
            min-width: 70px;
        }

        .lesson-course-name {
            font-size: 0.9rem;
        }

        .lesson-instructor-name {
            font-size: 0.8rem;
        }

        .empty-state {
            padding: 30px 16px;
        }

        .empty-state-text {
            font-size: 0.85rem;
        }
    }

    /* Very small mobile devices */
    @media screen and (max-width: 480px) {
        .student-dashboard {
            padding: 10px;
        }

        .page-title {
            font-size: 1.25rem;
        }

        .card-header {
            font-size: 0.7rem;
            padding: 8px 10px;
        }

        .info-row {
            font-size: 0.65rem;
            padding: 5px 0;
        }

        .card-body {
            padding: 10px;
        }

        .action-btn {
            padding: 10px 6px;
            font-size: 0.7rem;
        }
    }
</style>

<div class="student-dashboard">
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
    </div>

    <!-- Desktop: 3 column layout -->
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

    <!-- Mobile: Upcoming Lessons full width first -->
    <div class="mobile-upcoming">
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
    </div>

    <!-- Mobile: Learning Progress & Goals side by side -->
    <div class="mobile-two-col">
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
            <div class="card-header">Goals & Achievements</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Test Readiness</span>
                    <span class="info-value">{{ $testReadiness }}%</span>
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

    <!-- Quick Actions -->
    <div class="quick-actions-section">
        <h2 class="quick-actions-title">Quick Actions</h2>
        <div class="quick-actions-grid">
            <a href="{{ $schoolRoute('student.courses.index') }}" class="action-btn" onclick="loadContent(this.href); return false;">
                Browse Courses
            </a>
            <a href="{{ $schoolRoute('student.schedule') }}" class="action-btn" onclick="loadContent(this.href); return false;">
                My Bookings
            </a>
            <a href="{{ $schoolRoute('student.progress.index') }}" class="action-btn" onclick="loadContent(this.href); return false;">
                My Progress
            </a>
            <a href="{{ $schoolRoute('student.profile') }}" class="action-btn" onclick="loadContent(this.href); return false;">
                Update Profile
            </a>
        </div>
    </div>

    <!-- Today's Lesson Section -->
    <div class="todays-lesson-section">
        <div class="todays-lesson-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm-8 4H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/>
            </svg>
        </div>
        <h3 class="todays-lesson-title">Today's Lesson</h3>
    </div>
    <div class="todays-lesson-container">
        @php
            $todayLessons = $nextLessons ? $nextLessons->filter(function($lesson) {
                return \Carbon\Carbon::parse($lesson->date)->isToday();
            }) : collect([]);
        @endphp

        @if($todayLessons->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm-8 4H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/>
                </svg>
            </div>
            <p class="empty-state-text">No Lessons Today</p>
        </div>
        @else
            @foreach($todayLessons as $lesson)
            <div class="lesson-card-item">
                <div class="lesson-time-badge">
                    @if($lesson->timeSlot)
                        {{ \Carbon\Carbon::parse($lesson->timeSlot->start_time)->format('g:i A') }}
                    @else
                        TBD
                    @endif
                </div>
                <div class="lesson-details">
                    <div class="lesson-course-name">{{ $lesson->course->title ?? 'Driving Lesson' }}</div>
                    <div class="lesson-instructor-name">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" style="fill: currentColor; margin-right: 4px;">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        {{ $lesson->instructor->name ?? 'Instructor TBA' }}
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
