@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Enrolled Course')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school->schoolSetting;
@endphp

<style>
.my-course-container {
    padding: 20px;
    max-width: 1200px;
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

.course-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 25px;
}

.course-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
}

.course-title {
    font-size: 1.5rem;
    color: #333;
    margin: 0;
}

.course-type-badge {
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

.course-type-theoretical {
    background: #dbeafe;
    color: #1e40af;
}

.course-type-practical {
    background: #dcfce7;
    color: #166534;
}

.progress-section {
    margin: 25px 0;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-weight: 500;
}

.progress-bar-container {
    width: 100%;
    height: 25px;
    background: #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: {{ $settings->primary_color ?? '#667eea' }};
    display: flex;
    align-items: center;
    justify-content: center;
    transition: width 0.5s ease;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.course-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.stat-box {
    background: #f9fafb;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: {{ $settings->primary_color ?? '#667eea' }};
}

.stat-label {
    font-size: 0.85rem;
    color: #666;
    margin-top: 5px;
}

/* Module/Content Section */
.modules-section {
    margin-top: 30px;
}

.section-title {
    font-size: 1.3rem;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e5e7eb;
}

.module-item {
    background: #f9fafb;
    border-radius: 10px;
    margin-bottom: 12px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.module-header {
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: background 0.2s;
}

.module-header:hover {
    background: #f0f1f3;
}

.module-info h4 {
    margin: 0 0 5px 0;
    font-size: 1rem;
    color: #333;
}

.module-info p {
    margin: 0;
    font-size: 0.85rem;
    color: #666;
}

.module-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.lesson-count {
    background: {{ $settings->primary_color ?? '#667eea' }};
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
}

.module-toggle {
    font-size: 0.8rem;
    color: #999;
    transition: transform 0.3s;
}

.module-toggle.expanded {
    transform: rotate(180deg);
}

.lesson-list {
    display: none;
    border-top: 1px solid #e5e7eb;
    padding: 0;
    margin: 0;
    list-style: none;
}

.lesson-list.show {
    display: block;
}

.lesson-item {
    padding: 12px 20px 12px 35px;
    border-bottom: 1px solid #f0f1f3;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 0.9rem;
    color: #555;
}

.lesson-item:last-child {
    border-bottom: none;
}

.lesson-number {
    background: {{ $settings->primary_color ?? '#667eea' }}20;
    color: {{ $settings->primary_color ?? '#667eea' }};
    min-width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
}

.lesson-title {
    font-weight: 500;
    color: #333;
}

/* Session History */
.sessions-section {
    margin-top: 30px;
}

.session-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
}

.session-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.session-date {
    font-weight: 600;
    color: #333;
}

.session-hours {
    background: #dcfce7;
    color: #166534;
    padding: 4px 10px;
    border-radius: 10px;
    font-size: 0.85rem;
}

.session-details {
    font-size: 0.9rem;
    color: #666;
}

/* No Course State */
.no-course-card {
    text-align: center;
    padding: 50px 30px;
}

.no-course-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.no-course-title {
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 10px;
}

.no-course-text {
    color: #666;
    margin-bottom: 25px;
}

/* Pending Requests */
.pending-section {
    margin-top: 25px;
}

.pending-item {
    background: #fef3c7;
    border: 1px solid #f59e0b;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pending-badge {
    background: #f59e0b;
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
}

/* Available Courses */
.available-courses {
    display: grid;
    gap: 15px;
    margin-top: 20px;
}

.available-course-item {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.available-course-item:hover {
    border-color: {{ $settings->primary_color ?? '#667eea' }};
}

.course-info h4 {
    margin: 0 0 5px 0;
    color: #333;
}

.course-info p {
    margin: 0;
    font-size: 0.9rem;
    color: #666;
}

.enroll-btn {
    background: {{ $settings->primary_color ?? '#667eea' }};
    color: white;
    padding: 10px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
}

.enroll-btn:hover {
    opacity: 0.9;
}

@media (max-width: 768px) {
    .course-header {
        flex-direction: column;
    }
    
    .course-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="my-course-container">
    <div class="page-header">
        <h1 class="page-title">📚 Enrolled Course</h1>
    </div>

    @if($course)
        {{-- Active Course Card --}}
        <div class="course-card">
            <div class="course-header">
                <h2 class="course-title">{{ $course->title }}</h2>
                <span class="course-type-badge course-type-{{ $course->course_type ?? 'theoretical' }}">
                    {{ ucfirst($course->course_type ?? 'Theoretical') }}
                </span>
            </div>

            @if($course->description)
                <p style="color: #666; margin-bottom: 20px;">{{ $course->description }}</p>
            @endif

            {{-- Progress Bar --}}
            <div class="progress-section">
                <div class="progress-label">
                    <span>Progress</span>
                    <span>{{ $hoursCompleted }} / {{ $hoursRequired }} hours</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: {{ $progressPercentage }}%">
                        @if($progressPercentage > 10)
                            {{ $progressPercentage }}%
                        @endif
                    </div>
                </div>
            </div>

            {{-- Course Stats --}}
            <div class="course-stats">
                <div class="stat-box">
                    <div class="stat-value">{{ $hoursCompleted }}</div>
                    <div class="stat-label">Hours Completed</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $hoursRequired }}</div>
                    <div class="stat-label">Hours Required</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $sessionCompletions->count() }}</div>
                    <div class="stat-label">Sessions Logged</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $hoursRequired - $hoursCompleted }}</div>
                    <div class="stat-label">Hours Remaining</div>
                </div>
            </div>
        </div>

        {{-- Course Materials (Reference Only) --}}
        @if($modules->count() > 0)
            <div class="course-card">
                <h3 class="section-title">Course Materials</h3>
                <p style="color: #666; font-size: 0.9rem; margin-bottom: 15px;">
                    These materials are provided as reference. Click a module to view its lessons.
                </p>
                
                <div class="modules-section">
                    @foreach($modules->sortBy('sort_order') as $module)
                        <div class="module-item">
                            <div class="module-header" onclick="toggleModule(this)">
                                <div class="module-info">
                                    <h4>{{ $module->title }}</h4>
                                    @if($module->description)
                                        <p>{{ Str::limit($module->description, 80) }}</p>
                                    @endif
                                </div>
                                <div class="module-meta">
                                    @if($module->lessons && $module->lessons->count() > 0)
                                        <span class="lesson-count">{{ $module->lessons->count() }} {{ Str::plural('lesson', $module->lessons->count()) }}</span>
                                    @endif
                                    <span class="module-toggle">&#9660;</span>
                                </div>
                            </div>
                            @if($module->lessons && $module->lessons->count() > 0)
                                <ul class="lesson-list">
                                    @foreach($module->lessons->sortBy('sort_order') as $index => $lesson)
                                        <li class="lesson-item">
                                            <span class="lesson-number">{{ $index + 1 }}</span>
                                            <span class="lesson-title">{{ $lesson->title }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Session History --}}
        @if($sessionCompletions->count() > 0)
            <div class="course-card">
                <h3 class="section-title">📝 Session History</h3>
                
                <div class="sessions-section">
                    @foreach($sessionCompletions->sortByDesc('session_date')->take(10) as $session)
                        <div class="session-item">
                            <div class="session-header">
                                <span class="session-date">
                                    {{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}
                                </span>
                                <span class="session-hours">{{ $session->hours_completed ?? 1 }} hour(s)</span>
                            </div>
                            <div class="session-details">
                                @if($session->instructor)
                                    <strong>Instructor:</strong> {{ $session->instructor->name ?? 'N/A' }}<br>
                                @endif
                                @if($session->feedback)
                                    <strong>Feedback:</strong> {{ $session->feedback }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @else
        {{-- No Active Course --}}
        <div class="course-card no-course-card">
            <div class="no-course-icon">📚</div>
            <h2 class="no-course-title">No Active Course</h2>
            <p class="no-course-text">
                You are not currently enrolled in any course. 
                Browse available courses below and submit an enrollment request to get started.
            </p>
        </div>

        {{-- Pending Enrollment Requests --}}
        @if($pendingRequests->count() > 0)
            <div class="course-card">
                <h3 class="section-title">⏳ Pending Enrollment Requests</h3>
                <div class="pending-section">
                    @foreach($pendingRequests as $request)
                        <div class="pending-item">
                            <div>
                                <strong>{{ $request->course->title ?? 'Unknown Course' }}</strong>
                                <br>
                                <small style="color: #666;">Submitted: {{ $request->created_at->format('M d, Y') }}</small>
                            </div>
                            <span class="pending-badge">Pending Review</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Available Courses --}}
        @if($availableCourses->count() > 0)
            <div class="course-card">
                <h3 class="section-title">📋 Available Courses</h3>
                <div class="available-courses">
                    @foreach($availableCourses as $availableCourse)
                        <div class="available-course-item">
                            <div class="course-info">
                                <h4>{{ $availableCourse->title }}</h4>
                                <p>
                                    <span class="course-type-badge course-type-{{ $availableCourse->course_type ?? 'theoretical' }}" style="font-size: 0.7rem; padding: 3px 8px;">
                                        {{ ucfirst($availableCourse->course_type ?? 'Theoretical') }}
                                    </span>
                                    &nbsp;•&nbsp;
                                    {{ $availableCourse->duration_hours ?? $availableCourse->hours_required ?? '?' }} hours
                                    @if($availableCourse->price)
                                        &nbsp;•&nbsp; ₱{{ number_format($availableCourse->price, 2) }}
                                    @endif
                                </p>
                            </div>
                            <a href="{{ $schoolRoute('student.courses.show', ['course' => $availableCourse->id]) }}" class="enroll-btn">
                                View Details
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>

<script>
function toggleModule(header) {
    const moduleItem = header.closest('.module-item');
    const lessonList = moduleItem.querySelector('.lesson-list');
    const toggle = header.querySelector('.module-toggle');
    
    if (lessonList) {
        lessonList.classList.toggle('show');
        toggle.classList.toggle('expanded');
    }
}
</script>
@endsection
