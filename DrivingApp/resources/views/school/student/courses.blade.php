@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Browse Courses')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
@endphp

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: white;
    min-height: 100vh;
    padding: 20px;
    margin: 0;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    background: white;
    border-radius: 15px;
    padding: 30px;
}

.page-header h1 {
    font-size: 2rem;
    color: #1f2937;
    margin-bottom: 30px;
}

.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
}

.course-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    border: 2px solid transparent;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.course-card h3 {
    font-size: 1.4rem;
    color: #333;
    margin-bottom: 15px;
}

.course-description {
    color: #666;
    line-height: 1.7;
    margin-bottom: 20px;
    min-height: 60px;
}

.course-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    color: white;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 500;
}

.detail-item .value {
    font-weight: 700;
    font-size: 1.1rem;
}

.course-type {
    display: inline-block;
    padding: 6px 16px;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 15px;
}

.btn-book {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-book:hover {
    transform: scale(1.02);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn-book:disabled {
    background: #9ca3af !important;
    cursor: not-allowed !important;
    transform: none !important;
}

.enrollment-warning {
    background: #fef3c7;
    color: #92400e;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    margin-bottom: 10px;
    text-align: center;
    font-weight: 600;
}

@media (max-width: 768px) {
    .courses-grid { grid-template-columns: 1fr; }
}
</style>

<div class="container">
    <div class="page-header">
        <h1>📚 Available Courses - {{ $schoolName }}</h1>
    </div>

    <div class="courses-grid">
        @forelse($courses->where('status', 'active') as $course)
        <div class="course-card">
            <span class="course-type">{{ ucfirst($course->type ?? 'standard') }}</span>
            <h3>{{ $course->title }}</h3>
            <p class="course-description">{{ $course->description }}</p>
            
            <div class="course-details">
                <div class="detail-item">
                    <span>⏱️ Duration</span>
                    <span class="value">{{ $course->duration_hours }} hours</span>
                </div>
                <div class="detail-item">
                    <span>💰 Price</span>
                    <span class="value">₱{{ number_format($course->price, 2) }}</span>
                </div>
                @if($course->max_students)
                    <div class="detail-item">
                        <span>👥 Enrollment</span>
                        <span class="value">{{ $course->enrolledStudentsCount() }}/{{ $course->max_students }}</span>
                    </div>
                @endif
            </div>

            @if($course->isFull())
                <div class="enrollment-warning">⚠️ Course is currently full</div>
                <button class="btn-book" disabled style="background: #6b7280; cursor: not-allowed;">
                    Course Full
                </button>
            @elseif($course->max_students && $course->availableSlots() <= 3 && $course->availableSlots() > 0)
                <div class="enrollment-warning">⏰ Only {{ $course->availableSlots() }} {{ $course->availableSlots() == 1 ? 'slot' : 'slots' }} remaining!</div>
                <button class="btn-book" onclick="bookCourse({{ $course->id }})">
                    Book This Course
                </button>
            @else
                <button class="btn-book" onclick="bookCourse({{ $course->id }})">
                    Book This Course
                </button>
            @endif
        </div>
        @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #9ca3af;">
            <p style="font-size: 1.2rem;">No courses available at the moment</p>
        </div>
        @endforelse
    </div>
</div>

<script>
const schoolSlug = '{{ $school->slug }}';

function bookCourse(courseId) {
    window.location.href = `/${schoolSlug}/student/bookings/create?course_id=${courseId}`;
}
</script>
@endsection
