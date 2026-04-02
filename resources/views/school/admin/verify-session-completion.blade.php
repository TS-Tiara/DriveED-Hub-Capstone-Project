@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Verify Session Completion')

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
        position: relative;
        overflow: hidden;
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

    .bookings-container {
        padding: 20px;
        margin: 0 auto;
        max-width: 1200px;
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


    <!-- Page Header -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div class="page-header-left">
            <h1 class="page-title">Verify Session Completion</h1>
            <p class="page-subtitle">Verify instructor-logged sessions and manage training completion logs for {{ $schoolName }}</p>
        </div>
        {{-- Unified Export Dropdown --}}
        <div class="export-dropdown" id="bookingExport">
            <button type="button" class="btn-export-trigger" onclick="this.parentElement.classList.toggle('open')">
                <i class="bi bi-download"></i>
                Export Report
                <i class="bi bi-chevron-down chevron"></i>
            </button>
            <div class="export-dropdown-menu">
                <div class="dropdown-header">Export Options</div>
                <a href="{{ route('schools.admin.exports.verify-session-completion.excel', ['school' => $school->slug]) }}">
                    <i class="bi bi-file-earmark-excel-fill" style="color: #10b981;"></i> Full Export (Excel)
                </a>
                <div class="dropdown-header" style="font-size: 0.65rem; color: #94a3b8;">Format help</div>
                <div style="padding: 10px 16px; font-size: 0.8rem; color: #64748b; line-height: 1.4;">
                    Export contains all verified and pending completions for the current branch.
                </div>
            </div>
        </div>
    </div>



    <!-- Statistics Cards (Consolidated 4-card View) -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Awaiting Verification (Priority Focus) -->
        <div class="stat-card stat-card-clickable" id="card-done" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); cursor: pointer;" onclick="filterBookings('done', this)">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Awaiting Verification</div>
                    <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $awaitingVerificationCount }}</div>
                    <div style="color: #94a3b8; font-size: 0.8rem; margin-top: 5px;">Marked as Done by Instructor</div>
                </div>
                <div style="background: #f1f5f9; color: #64748b; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>

        <!-- Verified Sessions (Success State) -->
        <div class="stat-card stat-card-clickable" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); cursor: pointer;" onclick="filterBookings('completed', this)">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Verified Sessions</div>
                    <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $verifiedSessionsCount }}</div>
                    <div style="color: #94a3b8; font-size: 0.8rem; margin-top: 5px;">Successfully finalized logs</div>
                </div>
                <div style="background: #f1f5f9; color: #64748b; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
            </div>
        </div>

        <!-- Flagged Issues (Error/Warning State) -->
        <div class="stat-card stat-card-clickable" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); cursor: pointer;" onclick="filterBookings('flagged', this)">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Flagged Issues</div>
                    <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $flaggedIssuesCount }}</div>
                    <div style="color: #94a3b8; font-size: 0.8rem; margin-top: 5px;">Cancelled or no-show logs</div>
                </div>
                <div style="background: #f1f5f9; color: #64748b; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <!-- Booking Requests (Initialization State) -->
        <div class="stat-card stat-card-clickable" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); cursor: pointer;" onclick="filterBookings('pending', this)">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Booking Requests</div>
                    <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $pendingRequestsCount }}</div>
                    <div style="color: #94a3b8; font-size: 0.8rem; margin-top: 5px;">New schedule approvals pending</div>
                </div>
                <div style="background: #f1f5f9; color: #64748b; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="bi bi-envelope-paper"></i>
                </div>
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
                    <span class="badge badge-{{ $booking->status === 'completed' ? 'success' : ($booking->status === 'done' ? 'warning' : ($booking->status === 'cancelled' ? 'danger' : ($booking->status === 'pending' ? 'warning' : 'info'))) }}">
                        {{ $booking->status === 'done' ? 'Awaiting Verification' : ucfirst($booking->status) }}
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
                    <option value="done" {{ $booking->status == 'done' ? 'selected' : '' }}>Marked as Done</option>
                    <optgroup label="Final Audit">
                        <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Verify & Log Session</option>
                    </optgroup>
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
        <div class="admin-pagination-wrapper">
            {{ $bookings->links('vendor.pagination.drivingapp') }}
        </div>
    </div>
</div>

<script>
const schoolSlug = '{{ $school->slug }}';

function filterBookings(status, el) {
    // Quiet UI: No persistent border/shadow after click per user request
    // We only perform the list filtering logic
    const cards = document.querySelectorAll('.booking-card');
    
    cards.forEach(card => {
        const cardStatus = card.dataset.status;
        
        let match = false;
        if (status === 'all') {
            match = true;
        } else if (status === 'flagged') {
            // Flagged covers both cancelled and no-show
            match = (cardStatus === 'cancelled' || cardStatus === 'no-show');
        } else {
            match = (cardStatus === status);
        }

        card.style.display = match ? 'block' : 'none';
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
            fetch(`/${schoolSlug}/admin/verify-session-completion/${bookingId}/status`, {
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
