@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Schedule')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
@endphp

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: white;
    padding: 20px;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px;
}

.page-header h1 {
    font-size: 2rem;
    color: #1f2937;
    margin-bottom: 30px;
}

.schedule-calendar {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #667eea;
}

.calendar-header h2 {
    font-size: 1.5rem;
    color: #1f2937;
    margin: 0;
}

.calendar-nav {
    display: flex;
    gap: 10px;
}

.calendar-nav button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: transform 0.2s;
}

.calendar-nav button:hover {
    transform: translateY(-2px);
}

.upcoming-sessions {
    display: grid;
    gap: 20px;
}

.session-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.session-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.session-header h3 {
    font-size: 1.3rem;
    margin: 0;
}

.session-badge {
    background: rgba(255, 255, 255, 0.3);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.session-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
}

.detail-item strong {
    opacity: 0.9;
}

.no-sessions {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.no-sessions-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.no-sessions p {
    font-size: 1.1rem;
    margin: 10px 0;
}

.upcoming-bookings-section {
    margin-top: 40px;
}

.upcoming-bookings-section h2 {
    font-size: 1.8rem;
    color: #1f2937;
    margin-bottom: 20px;
}

.booking-list {
    display: grid;
    gap: 15px;
}

.booking-item {
    background: #f8f9fa;
    border-left: 4px solid #667eea;
    padding: 20px;
    border-radius: 8px;
}

.booking-item h4 {
    color: #1f2937;
    margin: 0 0 10px 0;
}

.booking-item p {
    margin: 5px 0;
    color: #6c757d;
}

@media (max-width: 768px) {
    .session-details {
        grid-template-columns: 1fr;
    }
    
    .calendar-header {
        flex-direction: column;
        gap: 15px;
    }
}
</style>

<div class="container">
    <div class="page-header">
        <h1>📅 My Schedule - {{ $schoolName }}</h1>
    </div>

    <div class="schedule-calendar">
        <div class="calendar-header">
            <h2>Upcoming Sessions</h2>
            <div class="calendar-nav">
                <button onclick="previousWeek()">← Previous</button>
                <button onclick="nextWeek()">Next →</button>
            </div>
        </div>

        <div class="upcoming-sessions" id="sessionsContainer">
            @forelse($bookings ?? [] as $booking)
                @if($booking->schedule && $booking->schedule->date >= now()->format('Y-m-d'))
                <div class="session-card">
                    <div class="session-header">
                        <h3>{{ $booking->course->title ?? 'Driving Lesson' }}</h3>
                        <span class="session-badge">{{ ucfirst($booking->status) }}</span>
                    </div>
                    <div class="session-details">
                        <div class="detail-item">
                            <span>📅</span>
                            <strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->schedule->date)->format('M d, Y') }}
                        </div>
                        <div class="detail-item">
                            <span>⏰</span>
                            <strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->schedule->end_time)->format('g:i A') }}
                        </div>
                        @if($booking->schedule->instructor)
                        <div class="detail-item">
                            <span>👨‍🏫</span>
                            <strong>Instructor:</strong> {{ $booking->schedule->instructor->name }}
                        </div>
                        @endif
                        <div class="detail-item">
                            <span>📍</span>
                            <strong>Status:</strong> {{ ucfirst($booking->status) }}
                        </div>
                    </div>
                </div>
                @endif
            @empty
            <div class="no-sessions">
                <div class="no-sessions-icon">📅</div>
                <p><strong>No upcoming sessions scheduled</strong></p>
                <p>Book a lesson to see your schedule here!</p>
            </div>
            @endforelse
        </div>
    </div>

    @if(isset($bookings) && count($bookings) > 0)
    <div class="upcoming-bookings-section">
        <h2>All My Bookings</h2>
        <div class="booking-list">
            @foreach($bookings as $booking)
            <div class="booking-item">
                <h4>{{ $booking->course->title ?? 'Driving Lesson' }}</h4>
                <p><strong>Status:</strong> {{ ucfirst($booking->status) }}</p>
                @if($booking->schedule)
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->schedule->date)->format('M d, Y') }}</p>
                <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->schedule->end_time)->format('g:i A') }}</p>
                @endif
                @if($booking->schedule && $booking->schedule->instructor)
                <p><strong>Instructor:</strong> {{ $booking->schedule->instructor->name }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
function previousWeek() {
    alert('Previous week navigation - Feature coming soon!');
}

function nextWeek() {
    alert('Next week navigation - Feature coming soon!');
}
</script>
@endsection
