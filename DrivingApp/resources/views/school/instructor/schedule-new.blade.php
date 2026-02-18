@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Schedule')

@section('content')
@php
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .schedule-header {
        margin-bottom: 20px;
        border-bottom: 3px solid {{ $primaryColor }};
        padding-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .schedule-header h1 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        color: #1f2937;
    }
    
    .main-toggle {
        display: flex;
        gap: 0;
        background: white;
        border: 2px solid {{ $primaryColor }};
        border-radius: 8px;
        overflow: hidden;
    }
    
    .main-toggle-btn {
        padding: 12px 28px;
        background: white;
        color: #495057;
        border: none;
        cursor: pointer;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .main-toggle-btn.active {
        background: {{ $primaryColor }};
        color: white;
    }
    
    .main-toggle-btn:hover:not(.active) {
        background: #f8f9fa;
    }
    
    .main-view-section {
        display: none;
    }
    
    .main-view-section.active {
        display: block;
    }
    
    .schedule-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 20px;
        align-items: start;
    }
    
    @media (max-width: 992px) {
        .schedule-grid {
            grid-template-columns: 1fr;
        }
        .schedule-sidebar {
            order: -1;
        }
    }
    
    .schedule-item {
        margin-bottom: 12px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .schedule-date-header {
        background: {{ $primaryColor }};
        color: white;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .schedule-date-header:hover {
        opacity: 0.9;
    }
    
    .schedule-date-header .date-text {
        flex: 1;
        font-weight: 500;
    }
    
    .schedule-date-header .toggle-icon {
        transition: transform 0.3s;
    }
    
    .schedule-date-header.collapsed .toggle-icon {
        transform: rotate(-90deg);
    }
    
    .schedule-slots {
        background: white;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    
    .schedule-slots.collapsed {
        max-height: 0 !important;
        padding: 0;
    }
    
    .slot-item {
        border-bottom: 1px solid #dee2e6;
        padding: 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .slot-item:last-child {
        border-bottom: none;
    }
    
    .slot-indicator {
        width: 4px;
        border-radius: 2px;
        flex-shrink: 0;
        align-self: stretch;
        min-height: 60px;
    }
    
    .slot-indicator.my-slot { background: #28a745; }
    .slot-indicator.available { background: {{ $primaryColor }}; }
    .slot-indicator.admin-assigned { background: #ff9800; }
    
    .slot-details {
        flex: 1;
    }
    
    .slot-time {
        font-weight: 600;
        color: #000;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .slot-badge {
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .badge-my-slot { background: #d4edda; color: #155724; }
    .badge-admin { background: #fff3cd; color: #856404; }
    .badge-available { background: #cce5ff; color: #004085; }
    .badge-pending { background: #f8d7da; color: #721c24; }
    .badge-qualified { background: #d4edda; color: #155724; }
    .badge-not-qualified { background: #fff3cd; color: #856404; }
    
    .slot-info {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 4px;
    }
    
    .slot-actions {
        margin-top: 8px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .btn-leave {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        background: #dc3545;
        color: white;
        border: none;
    }
    
    .btn-leave:hover { background: #c82333; }
    
    .btn-select {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        background: {{ $primaryColor }};
        color: white;
        border: none;
    }
    
    .btn-select:hover { opacity: 0.9; }
    
    .btn-request {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        background: #ffc107;
        color: #000;
        border: none;
    }
    
    .btn-request:hover { background: #e0a800; }
    
    .filter-bar {
        margin-bottom: 15px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .filter-bar label {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 14px;
        color: #495057;
    }
    
    .filter-bar input[type="checkbox"] {
        width: 16px;
        height: 16px;
    }
    
    .empty-state {
        text-align: center;
        color: #6c757d;
        padding: 40px;
    }
    
    /* Sidebar */
    .schedule-sidebar {
        display: flex;
        flex-direction: column;
        gap: 16px;
        position: sticky;
        top: 20px;
    }
    
    .sidebar-section {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 16px;
    }
    
    .sidebar-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #000;
        margin: 0 0 12px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid {{ $primaryColor }};
    }
    
    .today-lesson-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-left: 4px solid #28a745;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
    }
    
    .today-lesson-card:last-child { margin-bottom: 0; }
    
    .lesson-time {
        font-weight: 600;
        color: #000;
        margin-bottom: 8px;
    }
    
    .student-item {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
    }
    
    .student-item:last-child { margin-bottom: 0; }
    
    .student-name {
        font-weight: 600;
        color: #000;
        margin-bottom: 4px;
    }
    
    .student-course {
        color: #6c757d;
        font-size: 13px;
    }
    
    .mini-schedule-card {
        background: white;
        border: 1px solid #dee2e6;
        border-left: 3px solid {{ $primaryColor }};
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
        font-size: 13px;
    }
    
    .mini-schedule-card:last-child { margin-bottom: 0; }
    
    .mini-schedule-date {
        font-weight: 600;
        color: #000;
        margin-bottom: 4px;
    }
    
    .mini-schedule-info {
        color: #6c757d;
    }
    
    .no-lessons {
        text-align: center;
        color: #6c757d;
        padding: 20px;
        font-size: 14px;
    }
    
    /* Alert Messages */
    .alert {
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .alert-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    
    .alert-close {
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        color: inherit;
    }
    
    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow: hidden;
    }
    
    .modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: {{ $primaryColor }};
        color: white;
    }
    
    .modal-header h2 {
        margin: 0;
        font-size: 1.25rem;
    }
    
    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
    }
    
    .modal-body {
        padding: 20px;
        max-height: calc(80vh - 80px);
        overflow-y: auto;
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .admin-container { padding: 10px; }
        .schedule-header h1 { font-size: 1.3rem; }
        .main-toggle-btn { padding: 10px 16px; font-size: 13px; }
        .slot-item { padding: 12px; }
        .schedule-sidebar { display: none; }
        .mobile-sidebar-btn { display: inline-block !important; }
    }
    
    .mobile-sidebar-btn { display: none; }
</style>

<div class="admin-container">
    <!-- Header -->
    <div class="schedule-header">
        <h1>My Schedule</h1>
        <div class="main-toggle">
            <button type="button" class="main-toggle-btn active" onclick="switchMainView('my-slots')">My Slots</button>
            <button type="button" class="main-toggle-btn" onclick="switchMainView('available')">Available Slots</button>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">
            <span>{{ session('success') }}</span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-error">
            <span>{{ session('error') }}</span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif
    
    <!-- My Slots View -->
    <div id="my-slots-view" class="main-view-section active">
        <div class="schedule-grid">
            <div class="schedule-main">
                <div class="filter-bar">
                    <label>
                        <input type="checkbox" id="show-past-my" onchange="toggleShowPastMy(this)"> Show Past Slots
                    </label>
                    <label>
                        <input type="checkbox" id="collapse-all-my" onchange="toggleCollapseAllMy(this)"> Collapse All
                    </label>
                    <button type="button" class="btn-select mobile-sidebar-btn" onclick="toggleMobileSidebar()" style="padding: 6px 12px;">Today's Lessons</button>
                </div>
                
                @forelse($groupedMySlots as $date => $dateSlots)
                    @php $isPast = $date < $todayDate; @endphp
                    <div class="schedule-item" data-is-past="{{ $isPast ? 'true' : 'false' }}" style="{{ $isPast ? 'display: none;' : '' }}">
                        <div class="schedule-date-header" onclick="toggleDate(this)">
                            <span class="date-text">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="schedule-slots">
                            @foreach($dateSlots as $slot)
                                @php
                                    $instructor = $slot->instructors->firstWhere('id', $instructorId);
                                    $isAdminAssigned = $instructor && $instructor->pivot->assignment_type === 'admin_assigned';
                                    $hasPendingRequest = in_array($slot->id, $pendingRemovalRequests);
                                    $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                                    $daysUntilSlot = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($slot->date)->startOfDay(), false);
                                    $canRequestRemoval = $daysUntilSlot >= $minimumNoticeDays;
                                @endphp
                                <div class="slot-item">
                                    <div class="slot-indicator {{ $isAdminAssigned ? 'admin-assigned' : 'my-slot' }}"></div>
                                    <div class="slot-details">
                                        <div class="slot-time">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                            @if($hasPendingRequest)
                                                <span class="slot-badge badge-pending">Removal Requested</span>
                                            @elseif($isAdminAssigned)
                                                <span class="slot-badge badge-admin">Admin Assigned</span>
                                            @else
                                                <span class="slot-badge badge-my-slot">My Slot</span>
                                            @endif
                                        </div>
                                        <div class="slot-info">
                                            {{ $slot->course->title ?? 'General' }} • {{ $slot->instructors->count() }}/{{ $slot->max_instructors ?? 1 }} instructors
                                            @if($slotBookings->count() > 0)
                                                • {{ $slotBookings->count() }} student(s) scheduled
                                            @endif
                                        </div>
                                        @if($slot->notes)
                                            <div class="slot-info">{{ $slot->notes }}</div>
                                        @endif
                                        
                                        @if(!$hasPendingRequest)
                                        <div class="slot-actions">
                                            @if($isAdminAssigned)
                                                <button type="button" class="btn-request" onclick="showRemovalModal({{ $slot->id }})" {{ !$canRequestRemoval ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' }}>
                                                    Request Removal
                                                </button>
                                                @if(!$canRequestRemoval)
                                                    <span style="font-size: 11px; color: #dc3545;">(Min {{ $minimumNoticeDays }} days notice)</span>
                                                @endif
                                            @else
                                                <button type="button" class="btn-leave" onclick="leaveSlot({{ $slot->id }}, this)">Leave Slot</button>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <p>No slots selected yet. Go to "Available Slots" to select time slots.</p>
                    </div>
                @endforelse
            </div>
            
            <!-- Sidebar -->
            <div class="schedule-sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Today's Lessons</h3>
                    @if($todaySlots->isNotEmpty())
                        @foreach($todaySlots->sortBy('start_time') as $slot)
                            @php
                                $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                            @endphp
                            <div class="today-lesson-card">
                                <div class="lesson-time">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                </div>
                                @if($slotBookings->isNotEmpty())
                                    @foreach($slotBookings as $booking)
                                        <div class="student-item">
                                            <div class="student-name">{{ $booking->student->name ?? 'Student' }}</div>
                                            <div class="student-course">{{ $booking->course->title ?? 'Course' }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <p style="color: #6c757d; font-size: 13px; margin: 0;">No students scheduled</p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="no-lessons">No lessons scheduled for today</div>
                    @endif
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Upcoming This Week</h3>
                    @forelse($upcomingSlots as $slot)
                        @php
                            $instructor = $slot->instructors->firstWhere('id', $instructorId);
                            $isAdmin = $instructor && $instructor->pivot->assignment_type === 'admin_assigned';
                        @endphp
                        <div class="mini-schedule-card" style="border-left-color: {{ $isAdmin ? '#ff9800' : '#28a745' }};">
                            <div class="mini-schedule-date">{{ \Carbon\Carbon::parse($slot->date)->format('D, M d') }}</div>
                            <div class="mini-schedule-info">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            </div>
                        </div>
                    @empty
                        <div class="no-lessons">No upcoming slots</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <!-- Available Slots View -->
    <div id="available-view" class="main-view-section">
        <div class="schedule-grid">
            <div class="schedule-main">
                <div class="filter-bar">
                    <label>
                        <input type="checkbox" id="show-past-available" onchange="toggleShowPastAvailable(this)"> Show Past Slots
                    </label>
                    <label>
                        <input type="checkbox" id="collapse-all-available" onchange="toggleCollapseAllAvailable(this)"> Collapse All
                    </label>
                    <label>
                        <input type="checkbox" id="show-all-courses" onchange="toggleShowAllCourses(this)"> Show All Courses
                    </label>
                </div>
                
                @forelse($groupedAvailableSlots as $date => $dateSlots)
                    @php 
                        $isPast = $date < $todayDate;
                        $hasVisibleSlots = $dateSlots->filter(function($slot) use ($qualifiedCourseIds) {
                            return empty($qualifiedCourseIds) || in_array($slot->course_id, $qualifiedCourseIds);
                        })->count() > 0;
                    @endphp
                    <div class="schedule-item" data-is-past="{{ $isPast ? 'true' : 'false' }}" data-has-visible="{{ $hasVisibleSlots ? 'true' : 'false' }}" style="{{ $isPast || !$hasVisibleSlots ? 'display: none;' : '' }}">
                        <div class="schedule-date-header" onclick="toggleDate(this)">
                            <span class="date-text">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="schedule-slots">
                            @foreach($dateSlots as $slot)
                                @php
                                    $isQualified = empty($qualifiedCourseIds) || in_array($slot->course_id, $qualifiedCourseIds);
                                    $spotsLeft = ($slot->max_instructors ?? 1) - $slot->instructors->count();
                                @endphp
                                <div class="slot-item" data-qualified="{{ $isQualified ? 'true' : 'false' }}" style="{{ !$isQualified ? 'display: none;' : '' }}">
                                    <div class="slot-indicator available"></div>
                                    <div class="slot-details">
                                        <div class="slot-time">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                            <span class="slot-badge badge-available">Available</span>
                                            @if($isQualified)
                                                <span class="slot-badge badge-qualified">{{ $slot->course->title ?? 'General' }} ✓</span>
                                            @else
                                                <span class="slot-badge badge-not-qualified">{{ $slot->course->title ?? 'General' }}</span>
                                            @endif
                                        </div>
                                        <div class="slot-info">
                                            {{ $slot->instructors->count() }}/{{ $slot->max_instructors ?? 1 }} instructors • {{ $spotsLeft }} spot(s) left
                                            @if(!$isQualified)
                                                • <span style="color: #856404;">Not your specialty</span>
                                            @endif
                                        </div>
                                        @if($slot->notes)
                                            <div class="slot-info">{{ $slot->notes }}</div>
                                        @endif
                                        <div class="slot-actions">
                                            <button type="button" class="btn-select" onclick="selectSlot({{ $slot->id }}, this)">Select Slot</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <p>No available time slots at the moment.</p>
                    </div>
                @endforelse
            </div>
            
            <!-- Sidebar (same as My Slots) -->
            <div class="schedule-sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Today's Lessons</h3>
                    @if($todaySlots->isNotEmpty())
                        @foreach($todaySlots->sortBy('start_time') as $slot)
                            @php
                                $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                            @endphp
                            <div class="today-lesson-card">
                                <div class="lesson-time">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                </div>
                                @if($slotBookings->isNotEmpty())
                                    @foreach($slotBookings as $booking)
                                        <div class="student-item">
                                            <div class="student-name">{{ $booking->student->name ?? 'Student' }}</div>
                                            <div class="student-course">{{ $booking->course->title ?? 'Course' }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <p style="color: #6c757d; font-size: 13px; margin: 0;">No students scheduled</p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="no-lessons">No lessons scheduled for today</div>
                    @endif
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Your Schedule</h3>
                    @forelse($upcomingSlots as $slot)
                        <div class="mini-schedule-card">
                            <div class="mini-schedule-date">{{ \Carbon\Carbon::parse($slot->date)->format('D, M d') }}</div>
                            <div class="mini-schedule-info">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            </div>
                        </div>
                    @empty
                        <div class="no-lessons">No upcoming slots</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Removal Request Modal -->
<div class="modal-overlay" id="removalModal" onclick="if(event.target === this) closeRemovalModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Request Removal</h2>
            <button class="modal-close" onclick="closeRemovalModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="removalForm" method="POST">
                @csrf
                <p style="color: #666; margin-bottom: 15px;">
                    Please provide a reason for requesting removal from this admin-assigned time slot.
                </p>
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                    Reason: <span style="color: #dc3545;">*</span>
                </label>
                <textarea name="reason" required maxlength="500" style="width: 100%; min-height: 100px; padding: 10px; border: 1px solid #dee2e6; border-radius: 6px; font-family: inherit;" placeholder="E.g., conflicting appointment, personal emergency..."></textarea>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px;">
                    <button type="button" onclick="closeRemovalModal()" style="background: #e0e0e0; color: #666; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">Cancel</button>
                    <button type="submit" class="btn-request">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mobile Sidebar Popup -->
<div class="modal-overlay" id="mobileSidebar" onclick="if(event.target === this) toggleMobileSidebar()">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Today's Lessons</h2>
            <button class="modal-close" onclick="toggleMobileSidebar()">&times;</button>
        </div>
        <div class="modal-body">
            @if($todaySlots->isNotEmpty())
                @foreach($todaySlots->sortBy('start_time') as $slot)
                    @php
                        $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                    @endphp
                    <div class="today-lesson-card">
                        <div class="lesson-time">
                            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                        </div>
                        @if($slotBookings->isNotEmpty())
                            @foreach($slotBookings as $booking)
                                <div class="student-item">
                                    <div class="student-name">{{ $booking->student->name ?? 'Student' }}</div>
                                    <div class="student-course">{{ $booking->course->title ?? 'Course' }}</div>
                                </div>
                            @endforeach
                        @else
                            <p style="color: #6c757d; font-size: 13px; margin: 0;">No students scheduled</p>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="no-lessons">No lessons scheduled for today</div>
            @endif
        </div>
    </div>
</div>

<script>
    // Toast notification system
    function showToast(message, type) {
        type = type || 'success';
        var toast = document.createElement('div');
        toast.className = 'schedule-toast schedule-toast-' + type;
        toast.textContent = message;
        toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:10000;padding:14px 24px;border-radius:8px;color:white;font-weight:500;font-size:0.9rem;box-shadow:0 4px 12px rgba(0,0,0,0.15);transform:translateX(120%);transition:transform 0.3s ease;max-width:400px;';
        toast.style.background = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#f59e0b';
        document.body.appendChild(toast);
        requestAnimationFrame(function() { toast.style.transform = 'translateX(0)'; });
        setTimeout(function() {
            toast.style.transform = 'translateX(120%)';
            setTimeout(function() { toast.remove(); }, 300);
        }, 3000);
    }

    // Show pending toast from previous page action
    (function() {
        var msg = sessionStorage.getItem('scheduleToast');
        if (msg) {
            var data = JSON.parse(msg);
            sessionStorage.removeItem('scheduleToast');
            showToast(data.message, data.type);
        }
    })();

    // Tab switching
    function switchMainView(viewName) {
        document.querySelectorAll('.main-toggle-btn').forEach(function(btn) {
            btn.classList.remove('active');
        });
        document.querySelectorAll('.main-view-section').forEach(function(section) {
            section.classList.remove('active');
        });
        
        if (viewName === 'my-slots') {
            document.querySelectorAll('.main-toggle-btn')[0].classList.add('active');
            document.getElementById('my-slots-view').classList.add('active');
        } else {
            document.querySelectorAll('.main-toggle-btn')[1].classList.add('active');
            document.getElementById('available-view').classList.add('active');
        }
    }
    
    // Toggle date collapse
    function toggleDate(header) {
        var slots = header.nextElementSibling;
        header.classList.toggle('collapsed');
        slots.classList.toggle('collapsed');
    }
    
    // Show/hide past slots - My Slots view
    function toggleShowPastMy(checkbox) {
        var pastItems = document.querySelectorAll('#my-slots-view .schedule-item[data-is-past="true"]');
        pastItems.forEach(function(item) {
            item.style.display = checkbox.checked ? '' : 'none';
        });
    }
    
    // Show/hide past slots - Available view
    function toggleShowPastAvailable(checkbox) {
        var pastItems = document.querySelectorAll('#available-view .schedule-item[data-is-past="true"]');
        pastItems.forEach(function(item) {
            if (checkbox.checked) {
                // Check if it has visible slots when showing past
                var hasVisible = item.getAttribute('data-has-visible') === 'true';
                var showAllCourses = document.getElementById('show-all-courses').checked;
                item.style.display = (hasVisible || showAllCourses) ? '' : 'none';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    // Collapse all - My Slots view
    function toggleCollapseAllMy(checkbox) {
        var headers = document.querySelectorAll('#my-slots-view .schedule-date-header');
        var slots = document.querySelectorAll('#my-slots-view .schedule-slots');
        
        if (checkbox.checked) {
            headers.forEach(function(h) { h.classList.add('collapsed'); });
            slots.forEach(function(s) { s.classList.add('collapsed'); });
        } else {
            headers.forEach(function(h) { h.classList.remove('collapsed'); });
            slots.forEach(function(s) { s.classList.remove('collapsed'); });
        }
    }
    
    // Collapse all - Available view
    function toggleCollapseAllAvailable(checkbox) {
        var headers = document.querySelectorAll('#available-view .schedule-date-header');
        var slots = document.querySelectorAll('#available-view .schedule-slots');
        
        if (checkbox.checked) {
            headers.forEach(function(h) { h.classList.add('collapsed'); });
            slots.forEach(function(s) { s.classList.add('collapsed'); });
        } else {
            headers.forEach(function(h) { h.classList.remove('collapsed'); });
            slots.forEach(function(s) { s.classList.remove('collapsed'); });
        }
    }
    
    // Show all courses (including non-qualified)
    function toggleShowAllCourses(checkbox) {
        var items = document.querySelectorAll('#available-view .slot-item[data-qualified="false"]');
        items.forEach(function(item) {
            item.style.display = checkbox.checked ? '' : 'none';
        });
        
        // Also show/hide date groups that only have non-qualified slots
        var dateGroups = document.querySelectorAll('#available-view .schedule-item');
        dateGroups.forEach(function(group) {
            var hasVisible = group.getAttribute('data-has-visible') === 'true';
            var isPast = group.getAttribute('data-is-past') === 'true';
            var showPast = document.getElementById('show-past-available').checked;
            
            if (!isPast || showPast) {
                group.style.display = (hasVisible || checkbox.checked) ? '' : 'none';
            }
        });
    }
    
    // Leave slot
    function leaveSlot(slotId, btn) {
        showConfirm({
            title: 'Leave Slot',
            message: 'Are you sure you want to leave this slot?',
            type: 'warning',
            onConfirm: () => {
                btn.textContent = 'Leaving...';
                btn.disabled = true;
                
                leaveSlotExecute(slotId, btn);
            }
        });
    }

    function leaveSlotExecute(slotId, btn) {
        fetch('{{ url($school->slug) }}/instructor/timeslots/' + slotId + '/toggle', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                sessionStorage.setItem('scheduleToast', JSON.stringify({message: data.message || 'Successfully left the slot!', type: 'success'}));
                window.location.reload();
            } else {
                showToast(data.message || 'Failed to leave slot', 'error');
                btn.textContent = 'Leave Slot';
                btn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
            btn.textContent = 'Leave Slot';
            btn.disabled = false;
        });
    }
    
    // Select slot
    function selectSlot(slotId, btn) {
        btn.textContent = 'Selecting...';
        btn.disabled = true;
        
        fetch('{{ url($school->slug) }}/instructor/timeslots/' + slotId + '/toggle', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                sessionStorage.setItem('scheduleToast', JSON.stringify({message: data.message || 'Successfully selected the slot!', type: 'success'}));
                window.location.reload();
            } else {
                showToast(data.message || 'Failed to select slot', 'error');
                btn.textContent = 'Select Slot';
                btn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
            btn.textContent = 'Select Slot';
            btn.disabled = false;
        });
    }
    
    // Removal request modal
    function showRemovalModal(slotId) {
        var modal = document.getElementById('removalModal');
        var form = document.getElementById('removalForm');
        form.action = '{{ url($school->slug) }}/instructor/timeslots/' + slotId + '/request-removal';
        modal.style.display = 'flex';
    }
    
    function closeRemovalModal() {
        document.getElementById('removalModal').style.display = 'none';
    }
    
    // Mobile sidebar toggle
    function toggleMobileSidebar() {
        var sidebar = document.getElementById('mobileSidebar');
        sidebar.style.display = sidebar.style.display === 'flex' ? 'none' : 'flex';
    }
    
    // Handle removal form submission
    document.getElementById('removalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var form = this;
        var formData = new FormData(form);
        var submitBtn = form.querySelector('button[type="submit"]');
        
        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                sessionStorage.setItem('scheduleToast', JSON.stringify({message: data.message || 'Removal request submitted!', type: 'success'}));
                window.location.reload();
            } else {
                showToast(data.message || 'Failed to submit request', 'error');
                submitBtn.textContent = 'Submit Request';
                submitBtn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
            submitBtn.textContent = 'Submit Request';
            submitBtn.disabled = false;
        });
    });
</script>
@endsection
