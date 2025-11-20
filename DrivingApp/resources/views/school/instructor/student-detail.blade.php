@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Details')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
@endphp

<style>
    .student-detail-container {
        padding: 0;
        max-width: 1400px;
        margin: 0 auto;
    }

    .back-btn {
        background: #f3f4f6;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        color: #667eea;
        margin-bottom: 20px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .back-btn:hover {
        background: #667eea;
        color: white;
    }

    .student-header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 30px;
        border-radius: 12px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .student-header-content {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 30px;
    }

    .student-main-info h1 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 12px 0;
    }

    .student-main-info p {
        opacity: 0.9;
        margin: 6px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .progress-summary {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        background: rgba(255,255,255,0.15);
        padding: 20px;
        border-radius: 8px;
    }

    .progress-item {
        text-align: center;
    }

    .progress-value {
        font-size: 2rem;
        font-weight: 700;
        display: block;
    }

    .progress-label {
        font-size: 0.85rem;
        opacity: 0.9;
        display: block;
        margin-top: 4px;
    }

    .content-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        background: white;
        padding: 8px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .tab-btn {
        flex: 1;
        padding: 12px 20px;
        background: transparent;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .tab-btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .tab-btn:hover:not(.active) {
        background: #f3f4f6;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Progress Tab */
    .progress-grid {
        display: grid;
        gap: 20px;
    }

    .course-progress-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    .course-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .course-name {
        font-size: 1.3rem;
        font-weight: 700;
        color: #333;
    }

    .course-percent {
        font-size: 1.8rem;
        font-weight: 700;
        color: #667eea;
    }

    .progress-bar {
        width: 100%;
        height: 24px;
        background: #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 15px;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: width 0.5s ease;
    }

    .course-notes {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        font-size: 0.95rem;
        color: #666;
        font-style: italic;
    }

    /* Sessions Tab */
    .sessions-list {
        display: grid;
        gap: 15px;
    }

    .session-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid #667eea;
    }

    .session-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .session-date {
        font-size: 1.1rem;
        font-weight: 700;
        color: #333;
    }

    .session-course {
        font-size: 0.9rem;
        color: #667eea;
        margin-top: 4px;
    }

    .session-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
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

    .session-instructor {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .session-notes {
        padding: 12px;
        background: #fffbeb;
        border-left: 3px solid #f59e0b;
        border-radius: 4px;
        font-size: 0.9rem;
        color: #78350f;
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
        font-size: 3rem;
        color: #ddd;
        margin-bottom: 15px;
    }

    .empty-state p {
        color: #999;
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .student-header-card {
            padding: 20px;
        }

        .student-header-content {
            flex-direction: column;
            gap: 20px;
        }

        .student-main-info h1 {
            font-size: 1.6rem;
        }

        .progress-summary {
            grid-template-columns: repeat(2, 1fr);
            padding: 15px;
        }

        .progress-value {
            font-size: 1.5rem;
        }

        .content-tabs {
            flex-direction: column;
        }

        .tab-btn {
            padding: 10px 15px;
        }

        .course-progress-card {
            padding: 18px;
        }

        .course-name {
            font-size: 1.1rem;
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
                <p><i class="fas fa-envelope"></i> {{ $student->email }}</p>
                @if($student->contact)
                    <p><i class="fas fa-phone"></i> {{ $student->contact }}</p>
                @endif
                @if($student->address)
                    <p><i class="fas fa-map-marker-alt"></i> {{ $student->address }}</p>
                @endif
                <p><i class="fas fa-calendar"></i> Enrolled: {{ \Carbon\Carbon::parse($student->enrollment_date)->format('M d, Y') }}</p>
            </div>

            <div class="progress-summary">
                <div class="progress-item">
                    <span class="progress-value">{{ $student->progresses->avg('completion_percent') ? number_format($student->progresses->avg('completion_percent'), 0) : 0 }}%</span>
                    <span class="progress-label">Overall Progress</span>
                </div>
                <div class="progress-item">
                    <span class="progress-value">{{ $sessions->where('status', 'completed')->count() }}</span>
                    <span class="progress-label">Completed Sessions</span>
                </div>
                <div class="progress-item">
                    <span class="progress-value">{{ $student->progresses->count() }}</span>
                    <span class="progress-label">Courses Enrolled</span>
                </div>
                <div class="progress-item">
                    <span class="progress-value" style="color: {{ $student->status === 'active' ? '#10b981' : '#ef4444' }}">{{ ucfirst($student->status) }}</span>
                    <span class="progress-label">Status</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="content-tabs">
        <button class="tab-btn active" onclick="switchTab(event, 'progress')">Course Progress</button>
        <button class="tab-btn" onclick="switchTab(event, 'sessions')">Session History</button>
    </div>

    <!-- Progress Tab -->
    <div id="progress-tab" class="tab-content active">
        @if($student->progresses->count() > 0)
            <div class="progress-grid">
                @foreach($student->progresses as $progress)
                    <div class="course-progress-card">
                        <div class="course-header">
                            <h3 class="course-name">{{ $progress->course->name ?? 'Unknown Course' }}</h3>
                            <span class="course-percent">{{ number_format($progress->completion_percent, 0) }}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-bar-fill" style="width: {{ $progress->completion_percent }}%"></div>
                        </div>
                        @if($progress->notes)
                            <div class="course-notes">
                                <strong>Notes:</strong> {{ $progress->notes }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-chart-line"></i>
                <p>No course progress records found</p>
            </div>
        @endif
    </div>

    <!-- Sessions Tab -->
    <div id="sessions-tab" class="tab-content">
        @if($sessions->count() > 0)
            <div class="sessions-list">
                @foreach($sessions as $session)
                    <div class="session-card">
                        <div class="session-header">
                            <div>
                                <div class="session-date">{{ \Carbon\Carbon::parse($session['date'])->format('l, M d, Y - g:i A') }}</div>
                                <div class="session-course">{{ $session['course'] }}</div>
                            </div>
                            <span class="session-status status-{{ $session['status'] }}">{{ $session['status'] }}</span>
                        </div>
                        <div class="session-instructor">
                            <i class="fas fa-user-tie"></i>
                            Instructor: {{ $session['instructor_name'] }}
                        </div>
                        @if($session['notes'])
                            <div class="session-notes">
                                <strong><i class="fas fa-sticky-note"></i> Session Notes:</strong><br>
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
                <p>No session history found</p>
            </div>
        @endif
    </div>
</div>

<script>
function switchTab(event, tabName) {
    // Prevent default behavior
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked button
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }

    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Show selected tab
    const selectedTab = document.getElementById(tabName + '-tab');
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
}

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
