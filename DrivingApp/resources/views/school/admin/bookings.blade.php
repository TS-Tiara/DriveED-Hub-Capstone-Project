@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Sessions')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    /* Booking Cards - Using shared content-card styles */
    .bookings-list {
        display: grid;
        gap: 20px;
    }

    .booking-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border-left: 4px solid {{ $primaryColor }};
        position: relative;
        overflow: hidden;
    }

    .booking-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 120px;
        height: 120px;
        background: {{ $primaryColor }};
        border-radius: 50%;
        opacity: 0.05;
        transition: all 0.3s ease;
    }

    .booking-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
        position: relative;
        z-index: 1;
    }

    .booking-info h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 8px 0;
    }

    .booking-date {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .booking-time {
        font-weight: 600;
        color: {{ $primaryColor }};
    }

    .booking-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
        padding: 20px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .detail-label {
        font-size: 0.8rem;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .detail-value {
        color: #1f2937;
        font-weight: 500;
    }

    .booking-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }

    .status-select {
        padding: 8px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        background: white;
        cursor: pointer;
        min-width: 160px;
    }

    .status-select:focus {
        outline: none;
        border-color: {{ $primaryColor }};
    }

    .paid-indicator {
        color: #059669;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .stat-card-clickable {
        cursor: pointer;
    }

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .detail-value-amount {
        color: #059669;
        font-weight: 600;
    }

    .detail-item-full {
        grid-column: 1 / -1;
    }

    @media (max-width: 768px) {
        .booking-details { 
            grid-template-columns: 1fr 1fr; 
        }
        .booking-actions { 
            flex-direction: column; 
            width: 100%; 
        }
        .booking-actions .btn, 
        .booking-actions .status-select { 
            width: 100%; 
        }
    }
</style>

<div class="admin-container">
    <!-- Flash Messages -->
    @if(session('success'))
    <div class="flash-message success">
        <div class="flash-icon">✓</div>
        <div class="flash-content">
            <div class="flash-title">Success!</div>
            <div class="flash-text">{{ session('success') }}</div>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif
    
    @if(session('error'))
    <div class="flash-message error">
        <div class="flash-icon">✕</div>
        <div class="flash-content">
            <div class="flash-title">Error!</div>
            <div class="flash-text">{{ session('error') }}</div>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Student Sessions</h1>
            <p class="page-subtitle">Manage and track all driving session schedules for {{ $schoolName }}</p>
        </div>
    </div>

    <!-- Statistics Cards (clickable - serve as filters) -->
    <div class="stats-grid">
        <div class="stat-card total stat-card-clickable" onclick="filterBookings('all')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">All Sessions</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">Total schedule records</div>
            </div>
        </div>
        <div class="stat-card active stat-card-clickable" onclick="filterBookings('scheduled')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Scheduled</div>
                        <div class="stat-value">{{ $stats['scheduled'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">Upcoming sessions</div>
            </div>
        </div>
        <div class="stat-card growth stat-card-clickable" onclick="filterBookings('completed')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Completed</div>
                        <div class="stat-value">{{ $stats['completed'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">Finished sessions</div>
            </div>
        </div>
        <div class="stat-card danger stat-card-clickable" onclick="filterBookings('cancelled')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Cancelled</div>
                        <div class="stat-value">{{ $stats['cancelled'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">Cancelled schedules</div>
            </div>
        </div>
        <div class="stat-card inactive stat-card-clickable" onclick="filterBookings('pending')">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending</div>
                        <div class="stat-value">{{ $stats['pending'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">Awaiting confirmation</div>
            </div>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="bookings-list" id="bookingsList">
        @forelse($bookings as $booking)
        <div class="booking-card" data-status="{{ $booking->status }}">
            <div class="booking-header">
                <div class="booking-info">
                    <h3>{{ $booking->course->title ?? 'N/A' }}</h3>
                    <span class="badge badge-{{ $booking->status === 'completed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : ($booking->status === 'pending' ? 'warning' : 'info')) }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                <div class="booking-date">
                    @if($booking->timeSlot)
                        <span>{{ \Carbon\Carbon::parse($booking->timeSlot->date)->format('M d, Y') }}</span>
                        <span class="booking-time">{{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('h:i A') }}</span>
                    @elseif($booking->scheduled_at)
                        <span>{{ $booking->scheduled_at->format('M d, Y') }}</span>
                        <span class="booking-time">{{ $booking->scheduled_at->format('h:i A') }}</span>
                    @else
                        <span>{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : 'Not scheduled' }}</span>
                    @endif
                </div>
            </div>

            <div class="booking-details">
                <div class="detail-item">
                    <span class="detail-label">Student</span>
                    <span class="detail-value">{{ $booking->student->name ?? 'N/A' }}</span>
                </div>
                @if($booking->instructor)
                <div class="detail-item">
                    <span class="detail-label">Instructor</span>
                    <span class="detail-value">{{ $booking->instructor->name }}</span>
                </div>
                @endif
                @if($booking->package)
                <div class="detail-item">
                    <span class="detail-label">Package</span>
                    <span class="detail-value">{{ $booking->package->name }} - {{ $booking->package->transmission_type }}</span>
                </div>
                @endif
                @if($booking->package && $booking->package->training_hours)
                <div class="detail-item">
                    <span class="detail-label">Duration</span>
                    <span class="detail-value">{{ $booking->package->training_hours }} hours</span>
                </div>
                @endif
                <div class="detail-item">
                    <span class="detail-label">Price</span>
                    <span class="detail-value detail-value-amount">₱{{ number_format($booking->total_amount, 2) }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment</span>
                    <span class="detail-value">
                        <span class="badge badge-{{ $booking->payment_status === 'paid' ? 'success' : ($booking->payment_status === 'partial' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </span>
                </div>
                @if($booking->notes)
                <div class="detail-item detail-item-full">
                    <span class="detail-label">Notes</span>
                    <span class="detail-value">{{ $booking->notes }}</span>
                </div>
                @endif
            </div>

            <div class="booking-actions">
                <select class="status-select" onchange="updateStatus({{ $booking->id }}, this.value)">
                    <option value="">Change Status</option>
                    <option value="scheduled" {{ $booking->status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="no-show" {{ $booking->status == 'no-show' ? 'selected' : '' }}>No Show</option>
                </select>
                
                @if(!$booking->payment)
                <button class="btn btn-success btn-sm" onclick="createPayment({{ $booking->id }})">Record Payment</button>
                @else
                <span class="paid-indicator">✓ Paid</span>
                @endif
            </div>
        </div>
        @empty
        <div class="content-card">
            <div class="content-card-body">
                <div class="empty-state">
                    <div class="empty-state-title">No schedules found</div>
                    <div class="empty-state-text">Schedule records will appear here once students make reservations.</div>
                </div>
            </div>
        </div>
        @endforelse
    </div>
    <div class="mt-4">
        {{ $bookings->links() }}
    </div>
</div>

<script>
const schoolSlug = '{{ $school->slug }}';

function filterBookings(status) {
    const cards = document.querySelectorAll('.booking-card');
    
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
    
    showConfirm({
        type: 'warning',
        title: 'Change Schedule Status',
        message: `Are you sure you want to change this schedule status to "${status}"?`,
        confirmText: 'Yes, Update Status',
        onConfirm: () => {
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
                    Toast.success('Schedule status has been updated successfully.', 'Status Updated!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Toast.error(data.message || 'Failed to update schedule status.', 'Update Failed');
                }
            })
            .catch(error => {
                Toast.error('An error occurred while updating the status.', 'Error');
                console.error(error);
            });
        }
    });
}

function createPayment(bookingId) {
    window.location.href = `/${schoolSlug}/admin/payments?booking_id=${bookingId}`;
}
</script>
@endsection
