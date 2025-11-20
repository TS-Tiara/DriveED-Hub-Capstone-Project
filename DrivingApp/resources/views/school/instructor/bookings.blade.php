@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Student Bookings')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
    
    // Group bookings by status
    $scheduledBookings = $bookings->where('status', 'scheduled');
    $completedBookings = $bookings->where('status', 'completed');
    $cancelledBookings = $bookings->whereIn('status', ['cancelled', 'no-show']);
@endphp

<style>
    .bookings-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
        box-sizing: border-box;
    }

    .bookings-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .bookings-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
    }

    .bookings-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 16px;
    }

    .bookings-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-box {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
        transition: transform 0.2s;
    }

    .stat-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .stat-box .stat-label {
        font-size: 13px;
        color: #666;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .stat-box .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #667eea;
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 12px 24px;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
        color: #6b7280;
    }

    .filter-tab:hover {
        border-color: #667eea;
        color: #667eea;
    }

    .filter-tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
    }

    .bookings-grid {
        display: grid;
        gap: 20px;
    }

    .booking-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-left: 4px solid #667eea;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .booking-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .booking-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        gap: 15px;
        flex-wrap: wrap;
    }

    .booking-student-info h3 {
        margin: 0 0 8px 0;
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
    }

    .booking-course {
        color: #667eea;
        font-weight: 600;
        font-size: 15px;
        margin-bottom: 8px;
    }

    .booking-datetime {
        display: flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 12px 18px;
        border-radius: 8px;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .booking-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        padding: 20px;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .info-label {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 15px;
        color: #1f2937;
        font-weight: 500;
    }

    .booking-notes {
        background: #fffbeb;
        border-left: 3px solid #f59e0b;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .booking-notes .notes-label {
        font-size: 12px;
        color: #92400e;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .booking-notes .notes-text {
        color: #78350f;
        font-size: 14px;
    }

    .booking-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-scheduled {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-cancelled,
    .status-no-show {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-select {
        padding: 10px 18px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 180px;
    }

    .status-select:hover {
        border-color: #667eea;
    }

    .status-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 20px;
        color: #6b7280;
        margin: 0 0 10px 0;
    }

    .empty-state p {
        color: #9ca3af;
        font-size: 15px;
        margin: 0;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    @media (max-width: 1024px) {
        .bookings-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .bookings-container {
            padding: 15px;
            margin: 0 auto;
            width: 100%;
        }

        .bookings-header {
            padding: 20px;
        }

        .bookings-header h1 {
            font-size: 18px;
        }

        .bookings-header p {
            font-size: 13px;
        }

        .bookings-stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .stat-box {
            padding: 12px;
        }

        .stat-box .stat-label {
            font-size: 10px;
            margin-bottom: 6px;
        }

        .stat-box .stat-number {
            font-size: 20px;
        }

        .filter-tabs {
            gap: 6px;
        }

        .filter-tab {
            padding: 8px 14px;
            font-size: 12px;
        }

        .booking-card {
            padding: 15px;
        }

        .booking-top {
            gap: 10px;
        }

        .booking-student-info h3 {
            font-size: 15px;
        }

        .booking-course {
            font-size: 12px;
        }

        .booking-datetime {
            padding: 8px 12px;
            font-size: 11px;
        }

        .booking-info-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            padding: 12px;
        }

        .info-label {
            font-size: 9px;
        }

        .info-value {
            font-size: 12px;
        }

        .status-badge {
            padding: 5px 10px;
            font-size: 10px;
        }

        .status-select {
            padding: 7px 12px;
            font-size: 12px;
            min-width: 140px;
        }
    }

    @media (max-width: 480px) {
        .bookings-container {
            padding: 10px;
            margin: 0 auto;
            width: 100%;
        }

        .bookings-header {
            padding: 15px;
        }

        .bookings-header h1 {
            font-size: 16px;
        }

        .bookings-header p {
            font-size: 12px;
        }

        .bookings-stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .stat-box {
            padding: 10px;
        }

        .stat-box .stat-label {
            font-size: 9px;
            margin-bottom: 5px;
        }

        .stat-box .stat-number {
            font-size: 18px;
        }

        .filter-tabs {
            gap: 5px;
        }

        .filter-tab {
            padding: 7px 12px;
            font-size: 11px;
        }

        .booking-card {
            padding: 12px;
        }

        .booking-student-info h3 {
            font-size: 14px;
        }

        .booking-course {
            font-size: 11px;
        }

        .booking-datetime {
            padding: 7px 10px;
            font-size: 10px;
        }

        .booking-info-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            padding: 10px;
        }

        .info-label {
            font-size: 8px;
        }

        .info-value {
            font-size: 11px;
        }

        .booking-notes {
            padding: 10px;
        }

        .booking-notes .notes-label {
            font-size: 9px;
        }

        .booking-notes .notes-text {
            font-size: 11px;
        }

        .status-badge {
            padding: 4px 9px;
            font-size: 9px;
        }

        .status-select {
            padding: 6px 10px;
            font-size: 11px;
            min-width: 130px;
        }

        .empty-state {
            padding: 40px 15px;
        }

        .empty-state-icon {
            font-size: 42px;
        }

        .empty-state h3 {
            font-size: 15px;
        }

        .empty-state p {
            font-size: 12px;
        }
    }
</style>

<div class="bookings-container">
    <div class="bookings-header">
        <h1>My Student Bookings</h1>
        <p>Manage and track your scheduled lessons with students</p>
    </div>

    <div class="bookings-stats">
        <div class="stat-box">
            <div class="stat-label">Total Bookings</div>
            <div class="stat-number">{{ $bookings->count() }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Scheduled</div>
            <div class="stat-number">{{ $scheduledBookings->count() }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Completed</div>
            <div class="stat-number">{{ $completedBookings->count() }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Cancelled</div>
            <div class="stat-number">{{ $cancelledBookings->count() }}</div>
        </div>
    </div>

    <div class="filter-tabs">
        <button class="filter-tab active" onclick="showTab('all')">All Bookings</button>
        <button class="filter-tab" onclick="showTab('scheduled')">Scheduled</button>
        <button class="filter-tab" onclick="showTab('completed')">Completed</button>
        <button class="filter-tab" onclick="showTab('cancelled')">Cancelled/No-Show</button>
    </div>

    <!-- All Bookings Tab -->
    <div id="tab-all" class="tab-content active">
        <div class="bookings-grid">
            @forelse($bookings as $booking)
            <div class="booking-card">
                <div class="booking-top">
                    <div class="booking-student-info">
                        <h3>{{ $booking->student->name }}</h3>
                        <div class="booking-course">{{ $booking->course->title }}</div>
                        <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                    </div>
                    <div class="booking-datetime">
                        <span>{{ $booking->scheduled_at->format('M d, Y') }}</span>
                        <span>•</span>
                        <span>{{ $booking->scheduled_at->format('g:i A') }}</span>
                    </div>
                </div>

                <div class="booking-info-grid">
                    <div class="info-item">
                        <div class="info-label">Student Contact</div>
                        <div class="info-value">{{ $booking->student->contact ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Course Duration</div>
                        <div class="info-value">{{ $booking->course->duration_hours }} hours</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Course Type</div>
                        <div class="info-value">{{ ucfirst($booking->course->type ?? 'Standard') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Booking ID</div>
                        <div class="info-value">#{{ $booking->id }}</div>
                    </div>
                </div>

                @if($booking->notes)
                <div class="booking-notes">
                    <div class="notes-label">Notes</div>
                    <div class="notes-text">{{ $booking->notes }}</div>
                </div>
                @endif

                <div class="booking-footer">
                    <select class="status-select" onchange="updateStatus({{ $booking->id }}, this.value)">
                        <option value="">Change Status...</option>
                        <option value="scheduled" {{ $booking->status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="no-show" {{ $booking->status == 'no-show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-state-icon">📚</div>
                <h3>No bookings yet</h3>
                <p>Your student bookings will appear here</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Scheduled Bookings Tab -->
    <div id="tab-scheduled" class="tab-content">
        <div class="bookings-grid">
            @forelse($scheduledBookings as $booking)
            <div class="booking-card">
                <div class="booking-top">
                    <div class="booking-student-info">
                        <h3>{{ $booking->student->name }}</h3>
                        <div class="booking-course">{{ $booking->course->title }}</div>
                        <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                    </div>
                    <div class="booking-datetime">
                        <span>{{ $booking->scheduled_at->format('M d, Y') }}</span>
                        <span>•</span>
                        <span>{{ $booking->scheduled_at->format('g:i A') }}</span>
                    </div>
                </div>

                <div class="booking-info-grid">
                    <div class="info-item">
                        <div class="info-label">Student Contact</div>
                        <div class="info-value">{{ $booking->student->contact ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Course Duration</div>
                        <div class="info-value">{{ $booking->course->duration_hours }} hours</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Course Type</div>
                        <div class="info-value">{{ ucfirst($booking->course->type ?? 'Standard') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Booking ID</div>
                        <div class="info-value">#{{ $booking->id }}</div>
                    </div>
                </div>

                @if($booking->notes)
                <div class="booking-notes">
                    <div class="notes-label">Notes</div>
                    <div class="notes-text">{{ $booking->notes }}</div>
                </div>
                @endif

                <div class="booking-footer">
                    <select class="status-select" onchange="updateStatus({{ $booking->id }}, this.value)">
                        <option value="">Change Status...</option>
                        <option value="scheduled" selected>Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="no-show">No Show</option>
                    </select>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-state-icon">📅</div>
                <h3>No scheduled bookings</h3>
                <p>You don't have any scheduled lessons at the moment</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Completed Bookings Tab -->
    <div id="tab-completed" class="tab-content">
        <div class="bookings-grid">
            @forelse($completedBookings as $booking)
            <div class="booking-card">
                <div class="booking-top">
                    <div class="booking-student-info">
                        <h3>{{ $booking->student->name }}</h3>
                        <div class="booking-course">{{ $booking->course->title }}</div>
                        <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                    </div>
                    <div class="booking-datetime">
                        <span>{{ $booking->scheduled_at->format('M d, Y') }}</span>
                        <span>•</span>
                        <span>{{ $booking->scheduled_at->format('g:i A') }}</span>
                    </div>
                </div>

                <div class="booking-info-grid">
                    <div class="info-item">
                        <div class="info-label">Student Contact</div>
                        <div class="info-value">{{ $booking->student->contact ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Course Duration</div>
                        <div class="info-value">{{ $booking->course->duration_hours }} hours</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Course Type</div>
                        <div class="info-value">{{ ucfirst($booking->course->type ?? 'Standard') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Booking ID</div>
                        <div class="info-value">#{{ $booking->id }}</div>
                    </div>
                </div>

                @if($booking->notes)
                <div class="booking-notes">
                    <div class="notes-label">Notes</div>
                    <div class="notes-text">{{ $booking->notes }}</div>
                </div>
                @endif

                <div class="booking-footer">
                    <select class="status-select" onchange="updateStatus({{ $booking->id }}, this.value)">
                        <option value="">Change Status...</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed" selected>Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="no-show">No Show</option>
                    </select>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-state-icon">✅</div>
                <h3>No completed bookings</h3>
                <p>Completed lessons will appear here</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Cancelled Bookings Tab -->
    <div id="tab-cancelled" class="tab-content">
        <div class="bookings-grid">
            @forelse($cancelledBookings as $booking)
            <div class="booking-card">
                <div class="booking-top">
                    <div class="booking-student-info">
                        <h3>{{ $booking->student->name }}</h3>
                        <div class="booking-course">{{ $booking->course->title }}</div>
                        <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                    </div>
                    <div class="booking-datetime">
                        <span>{{ $booking->scheduled_at->format('M d, Y') }}</span>
                        <span>•</span>
                        <span>{{ $booking->scheduled_at->format('g:i A') }}</span>
                    </div>
                </div>

                <div class="booking-info-grid">
                    <div class="info-item">
                        <div class="info-label">Student Contact</div>
                        <div class="info-value">{{ $booking->student->contact ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Course Duration</div>
                        <div class="info-value">{{ $booking->course->duration_hours }} hours</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Course Type</div>
                        <div class="info-value">{{ ucfirst($booking->course->type ?? 'Standard') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Booking ID</div>
                        <div class="info-value">#{{ $booking->id }}</div>
                    </div>
                </div>

                @if($booking->notes)
                <div class="booking-notes">
                    <div class="notes-label">Notes</div>
                    <div class="notes-text">{{ $booking->notes }}</div>
                </div>
                @endif

                <div class="booking-footer">
                    <select class="status-select" onchange="updateStatus({{ $booking->id }}, this.value)">
                        <option value="">Change Status...</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="no-show" {{ $booking->status == 'no-show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-state-icon">❌</div>
                <h3>No cancelled bookings</h3>
                <p>Cancelled or no-show lessons will appear here</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
const schoolSlug = '{{ $school->slug }}';

function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById('tab-' + tabName).classList.add('active');
    
    // Add active class to clicked filter tab
    event.target.classList.add('active');
}

function updateStatus(bookingId, status) {
    if (!status) return;
    
    const statusText = status.replace('-', ' ');
    if (!confirm(`Are you sure you want to change the status to "${statusText}"?`)) {
        location.reload();
        return;
    }
    
    fetch(`/${schoolSlug}/instructor/bookings/${bookingId}/status`, {
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
        } else {
            alert('Error: ' + (data.message || 'Failed to update status'));
            location.reload();
        }
    })
    .catch(error => {
        alert('Error updating status. Please try again.');
        console.error(error);
        location.reload();
    });
}
</script>
@endsection
