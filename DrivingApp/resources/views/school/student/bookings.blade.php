@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Bookings')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
@endphp

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: white;
    padding: 20px;
    margin: 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px;
}

.page-header h1 {
    font-size: 2rem;
    color: #1f2937;
    margin-bottom: 30px;
}

.bookings-list {
    display: grid;
    gap: 20px;
}

.booking-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.booking-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.booking-header h3 {
    font-size: 1.3rem;
    color: #333;
}

.booking-date {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #667eea;
    font-weight: 600;
}

.booking-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.detail-row {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-row .label {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 600;
}

.detail-row .value {
    color: #1f2937;
    font-weight: 500;
}

.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-scheduled { background: #dbeafe; color: #1e40af; }
.badge-completed { background: #d1fae5; color: #065f46; }
.badge-cancelled { background: #fee2e2; color: #991b1b; }

@media (max-width: 768px) {
    .booking-details { grid-template-columns: 1fr; }
}
</style>

<div class="container">
    <div class="page-header">
        <h1>📅 My Bookings - {{ $schoolName }}</h1>
    </div>

    <div class="bookings-list">
        @forelse($bookings as $booking)
        <div class="booking-card">
            <div class="booking-header">
                <div>
                    <h3>{{ $booking->course->title }}</h3>
                    <span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                </div>
                <div class="booking-date">
                    📅 {{ $booking->scheduled_at->format('M d, Y - h:i A') }}
                </div>
            </div>

            <div class="booking-details">
                @if($booking->instructor)
                <div class="detail-row">
                    <span class="label">Instructor</span>
                    <span class="value">{{ $booking->instructor->name }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="label">Duration</span>
                    <span class="value">{{ $booking->course->duration_hours }} hours</span>
                </div>
                <div class="detail-row">
                    <span class="label">Price</span>
                    <span class="value">₱{{ number_format($booking->course->price, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Payment Status</span>
                    <span class="value">
                        @if($booking->payment)
                            <span style="color: #10b981; font-weight: 700;">✓ Paid</span>
                        @else
                            <span style="color: #f59e0b; font-weight: 700;">⚠ Unpaid</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 60px 20px; color: #9ca3af;">
            <p style="font-size: 1.2rem;">You don't have any bookings yet</p>
            <p>Browse courses to make your first booking!</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
