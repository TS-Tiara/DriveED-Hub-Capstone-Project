@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Manage Bookings')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
@endphp

<style>
.bookings-container {
    padding: 20px;
    margin: 20px auto;
    max-width: 1600px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid {{ $school->schoolSetting->primary_color ?? '#667eea' }};
}

.page-title {
    font-size: 2rem;
    color: #333;
    margin: 0;
}

.page-subtitle {
    color: #666;
    font-size: 0.95rem;
    margin-top: 5px;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    @if($school->schoolSetting->use_gradient_header)
        background: linear-gradient(135deg, {{ $school->schoolSetting->primary_color }} 0%, {{ $school->schoolSetting->secondary_color }} 100%);
    @else
        background: {{ $school->schoolSetting->primary_color }};
    @endif
    color: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card.scheduled {
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
}

.stat-card.completed {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.stat-card.cancelled {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.stat-card.pending {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.bookings-filters {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 600;
}

.filter-btn.active {
    @if($school->schoolSetting->use_gradient_header)
        background: linear-gradient(135deg, {{ $school->schoolSetting->primary_color }} 0%, {{ $school->schoolSetting->secondary_color }} 100%);
    @else
        background: {{ $school->schoolSetting->primary_color }};
    @endif
    color: white;
    border-color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
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
    transition: transform 0.3s, box-shadow 0.3s;
}

.booking-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.booking-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.booking-info h3 {
    font-size: 1.3rem;
    color: #333;
    margin-bottom: 5px;
}

.booking-date {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #666;
}

.time {
    font-weight: 600;
    color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
}

.booking-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
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

.booking-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-scheduled { background: #dbeafe; color: #1e40af; }
.badge-confirmed { background: #d1fae5; color: #065f46; }
.badge-completed { background: #d1fae5; color: #065f46; }
.badge-cancelled { background: #fee2e2; color: #991b1b; }
.badge-pending { background: #fef3c7; color: #92400e; }
.badge-no-show { background: #fef3c7; color: #92400e; }
.badge-paid { background: #d1fae5; color: #065f46; }
.badge-partial { background: #fef3c7; color: #92400e; }
.badge-refunded { background: #fee2e2; color: #991b1b; }

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9rem;
}

.btn-primary { background: var(--btn-primary-bg); color: var(--btn-primary-text); }
.btn-secondary { background: var(--btn-secondary-bg); color: var(--btn-secondary-text); }
.btn-success { background: var(--btn-success-bg); color: var(--btn-success-text); }
.btn-danger { background: var(--btn-danger-bg); color: var(--btn-danger-text); }
.btn-sm { padding: 8px 16px; font-size: 0.85rem; }

.form-select {
    padding: 8px 15px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .booking-details { grid-template-columns: 1fr; }
    .booking-actions { flex-direction: column; width: 100%; }
    .booking-actions .btn, .booking-actions .form-select { width: 100%; }
}
</style>

<div class="bookings-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Bookings Management</h1>
            <p class="page-subtitle">Manage and track all driving session bookings for {{ $schoolName }}</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card scheduled">
            <div class="stat-number">{{ $bookings->where('status', 'scheduled')->count() }}</div>
            <div class="stat-label">Scheduled Bookings</div>
        </div>
        <div class="stat-card completed">
            <div class="stat-number">{{ $bookings->where('status', 'completed')->count() }}</div>
            <div class="stat-label">Completed Sessions</div>
        </div>
        <div class="stat-card cancelled">
            <div class="stat-number">{{ $bookings->where('status', 'cancelled')->count() }}</div>
            <div class="stat-label">Cancelled Bookings</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-number">{{ $bookings->where('status', 'pending')->count() }}</div>
            <div class="stat-label">Pending Bookings</div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="bookings-filters">
        <button class="filter-btn active" data-filter="all" onclick="filterBookings('all')">All Bookings ({{ $bookings->count() }})</button>
        <button class="filter-btn" data-filter="scheduled" onclick="filterBookings('scheduled')">Scheduled ({{ $bookings->where('status', 'scheduled')->count() }})</button>
        <button class="filter-btn" data-filter="completed" onclick="filterBookings('completed')">Completed ({{ $bookings->where('status', 'completed')->count() }})</button>
        <button class="filter-btn" data-filter="cancelled" onclick="filterBookings('cancelled')">Cancelled ({{ $bookings->where('status', 'cancelled')->count() }})</button>
        <button class="filter-btn" data-filter="pending" onclick="filterBookings('pending')">Pending ({{ $bookings->where('status', 'pending')->count() }})</button>
    </div>

    <!-- Bookings List -->
    <div class="bookings-list" id="bookingsList">
            @forelse($bookings as $booking)
            <div class="booking-card" data-status="{{ $booking->status }}">
                <div class="booking-header">
                    <div class="booking-info">
                        <h3>{{ $booking->course->title ?? 'N/A' }}</h3>
                        <span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                </div>
                <div class="booking-date">
                    @if($booking->timeSlot)
                        📅 <span>{{ \Carbon\Carbon::parse($booking->timeSlot->date)->format('M d, Y') }}</span>
                        <span class="time">{{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('h:i A') }}</span>
                    @elseif($booking->scheduled_at)
                        📅 <span>{{ $booking->scheduled_at->format('M d, Y') }}</span>
                        <span class="time">{{ $booking->scheduled_at->format('h:i A') }}</span>
                    @else
                        📅 <span>{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : 'Not scheduled' }}</span>
                    @endif
                </div>
            </div>

            <div class="booking-details">
                <div class="detail-row">
                    <span class="label">Student</span>
                    <span class="value">{{ $booking->student->name ?? 'N/A' }}</span>
                </div>
                @if($booking->instructor)
                <div class="detail-row">
                    <span class="label">Instructor</span>
                    <span class="value">{{ $booking->instructor->name }}</span>
                </div>
                @endif
                @if($booking->package)
                <div class="detail-row">
                    <span class="label">Package</span>
                    <span class="value">{{ $booking->package->name }} - {{ $booking->package->transmission_type }}</span>
                </div>
                @endif
                @if($booking->package && $booking->package->training_hours)
                <div class="detail-row">
                    <span class="label">Duration</span>
                    <span class="value">{{ $booking->package->training_hours }} hours</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="label">Price</span>
                    <span class="value">₱{{ number_format($booking->total_amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Payment</span>
                    <span class="value">
                        <span class="badge badge-{{ $booking->payment_status }}">{{ ucfirst($booking->payment_status) }}</span>
                    </span>
                </div>
                @if($booking->notes)
                <div class="detail-row">
                    <span class="label">Notes</span>
                    <span class="value">{{ $booking->notes }}</span>
                </div>
                @endif
            </div>

            <div class="booking-actions">
                <select class="form-select" onchange="updateStatus({{ $booking->id }}, this.value)" style="max-width: 200px;">
                    <option value="">Change Status</option>
                    <option value="scheduled" {{ $booking->status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="no-show" {{ $booking->status == 'no-show' ? 'selected' : '' }}>No Show</option>
                </select>
                
                @if(!$booking->payment)
                <button class="btn btn-sm btn-success" onclick="createPayment({{ $booking->id }})">Record Payment</button>
                @else
                <span style="color: #10b981; font-weight: 600;">✓ Paid</span>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 60px 20px; color: #9ca3af;">
            <p style="font-size: 1.2rem;">No bookings found</p>
        </div>
        @endforelse
    </div>
</div>

<script>
const schoolSlug = '{{ $school->slug }}';

function filterBookings(status) {
    const cards = document.querySelectorAll('.booking-card');
    const buttons = document.querySelectorAll('.filter-btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    cards.forEach(card => {
        const cardStatus = card.dataset.status;
        if (status === 'all' || cardStatus === status) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function updateStatus(bookingId, status) {
    if (!status) return;
    
    if (!confirm(`Are you sure you want to change the booking status to "${status}"?`)) {
        location.reload();
        return;
    }
    
    fetch(`/${schoolSlug}/admin/bookings/${bookingId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status updated successfully!');
            location.reload();
        }
    })
    .catch(error => {
        alert('Error updating status');
        console.error(error);
    });
}

function createPayment(bookingId) {
    window.location.href = `/${schoolSlug}/admin/payments/create?booking_id=${bookingId}`;
}
</script>
@endsection
