@extends('layouts.app')

@section('title', 'My Schedule')

@section('content')
<?php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $instructorId = Auth::guard('instructor')->id();
    
    $primaryColor = $settings?->primary_color ?? '#0d6efd';
    $secondaryColor = $settings?->secondary_color ?? '#6c757d';
    $borderRadius = $settings?->border_radius ?? 8;
    
    // Today's date for comparison
    $todayDate = now()->toDateString();
    
    // Minimum notice days for removal requests
    $minimumNoticeDays = $school->instructor_removal_notice_days ?? 7;
    
    // Get instructor's qualified courses
    $instructor = Auth::guard('instructor')->user();
    $qualifiedCourseIds = $instructor->course_specializations ?? [];
    
    // Get all time slots for this school
    $allTimeSlots = App\Models\TimeSlot::where('school_id', $school->id)
        ->with(['instructors', 'bookings.student', 'bookings.course', 'course'])
        ->orderBy('date')
        ->orderBy('start_time')
        ->get();
    
    // My Slots - slots where this instructor is assigned
    $mySlots = $allTimeSlots->filter(function($slot) use ($instructorId) {
        return $slot->instructors->contains('id', $instructorId);
    });
    
    // Available Slots - open slots not yet at capacity, future dates only
    $availableTimeSlots = $allTimeSlots->filter(function($slot) use ($instructorId, $todayDate) {
        return $slot->status === 'open' 
            && !$slot->instructors->contains('id', $instructorId)
            && $slot->instructors->count() < ($slot->max_instructors ?? 1)
            && $slot->date->format('Y-m-d') >= $todayDate;
    });
    
    // Today's Slots - my slots for today with bookings
    $todaySlots = $mySlots->filter(function($slot) use ($todayDate) {
        return $slot->date->format('Y-m-d') === $todayDate;
    });
    
    // Group my slots by date
    $groupedMySlots = $mySlots->groupBy(function($slot) {
        return $slot->date->format('Y-m-d');
    })->sortKeys();
    
    // Group available slots by date
    $groupedAvailableSlots = $availableTimeSlots->groupBy(function($slot) {
        return $slot->date->format('Y-m-d');
    })->sortKeys();
    
    // Upcoming slots for sidebar (next 7 days)
    $upcomingSlots = $mySlots->filter(function($slot) use ($todayDate) {
        return $slot->date->format('Y-m-d') >= $todayDate 
            && $slot->date->format('Y-m-d') <= now()->addDays(7)->format('Y-m-d');
    })->sortBy('date')->take(5);
    
    // Get pending removal requests for this instructor
    $pendingRemovalRequests = App\Models\InstructorRemovalRequest::where('instructor_id', $instructorId)
        ->where('status', 'pending')
        ->pluck('time_slot_id')
        ->toArray();
    
    // Build instructor's current schedule for conflict checking (date => array of time ranges)
    $instructorSchedule = [];
    foreach ($mySlots as $slot) {
        $dateKey = $slot->date->format('Y-m-d');
        if (!isset($instructorSchedule[$dateKey])) {
            $instructorSchedule[$dateKey] = [];
        }
        $instructorSchedule[$dateKey][] = [
            'id' => $slot->id,
            'start' => \Carbon\Carbon::parse($slot->start_time)->format('H:i:s'),
            'end' => \Carbon\Carbon::parse($slot->end_time)->format('H:i:s'),
            'course' => $slot->course->title ?? 'General',
        ];
    }
?>

<style>
    .schedule-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .schedule-header {
        margin-bottom: 20px;
        border-bottom: 4px solid {{ $primaryColor }};
        padding-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .schedule-header h1 {
        font-size: 2rem;
        font-weight: 400;
        margin: 0;
        color: #1a202c;
    }
    
    .main-toggle {
        display: flex;
        gap: 0;
        background: white;
        border: 2px solid {{ $primaryColor }};
        border-radius: 8px;
        overflow: hidden;
        position: relative;
        z-index: 10;
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
        grid-template-columns: 1fr 400px;
        gap: 20px;
        margin-top: 20px;
        align-items: start;
    }
    
    @media (max-width: 992px) {
        .schedule-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .schedule-main {
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 0;
        width: 100%;
    }
    
    .collapse-btn {
        background: #212529;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 20px;
    }
    
    .collapse-btn:hover {
        background: #000;
    }
    
    .schedule-item {
        margin-bottom: 12px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .schedule-date-header {
        @if($settings?->use_gradient_header)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $settings?->secondary_color ?? '#0a58ca' }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        padding: 12px 16px;
        border-radius: 0;
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
    
    .schedule-bookings {
        padding-left: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        background: white;
    }
    
    .schedule-bookings.collapsed {
        max-height: 0 !important;
    }
    
    .slot-item {
        border-left: none;
        border-bottom: 1px solid #dee2e6;
        background: white;
        padding: 16px;
        margin: 0;
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
    }
    
    .slot-indicator.my-slot {
        background: #28a745;
    }
    
    .slot-indicator.available {
        background: {{ $primaryColor }};
    }
    
    .slot-indicator.admin-assigned {
        background: #ff9800;
    }
    
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
    }
    
    .slot-badge {
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .slot-badge.my-slot {
        background: #d4edda;
        color: #155724;
    }
    
    .slot-badge.available {
        background: #cce5ff;
        color: #004085;
    }
    
    .slot-badge.admin-assigned {
        background: #fff3cd;
        color: #856404;
    }
    
    .slot-badge.pending {
        background: #f8d7da;
        color: #721c24;
    }
    
    .slot-info {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 4px;
    }
    
    .slot-actions {
        margin-top: 8px;
    }
    
    .btn-slot {
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.3s;
    }
    
    .btn-select {
        background: {{ $primaryColor }};
        color: white;
    }
    
    .btn-select:hover {
        opacity: 0.9;
    }
    
    .btn-leave {
        background: #dc3545;
        color: white;
    }
    
    .btn-leave:hover {
        background: #c82333;
    }
    
    .btn-request {
        background: #ffc107;
        color: #000;
    }
    
    .btn-request:hover {
        background: #e0a800;
    }
    
    /* Sidebar */
    .schedule-sidebar {
        align-self: start;
        display: flex;
        flex-direction: column;
        gap: 20px;
        position: sticky;
        top: 20px;
        margin-top: 20px;
    }
    
    .sidebar-section {
        margin-bottom: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 16px;
    }
    
    .sidebar-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #000;
        margin: 0 0 12px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid {{ $primaryColor }};
    }
    
    .mini-schedule-card {
        background: white;
        border: 1px solid #dee2e6;
        border-left: 3px solid {{ $primaryColor }};
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 10px;
        font-size: 13px;
    }
    
    .mini-schedule-card:last-child {
        margin-bottom: 0;
    }
    
    .mini-schedule-date {
        font-weight: 600;
        color: #000;
        margin-bottom: 6px;
        font-size: 14px;
    }
    
    .mini-schedule-info {
        color: #6c757d;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    /* Today's Lesson Card */
    .today-lesson-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-left: 4px solid #28a745;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
    }
    
    .lesson-date {
        font-weight: 600;
        color: #000;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .lesson-time {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 12px;
    }
    
    .student-item {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 8px;
    }
    
    .student-item:last-child {
        margin-bottom: 0;
    }
    
    .student-name {
        font-weight: 600;
        color: #000;
        margin-bottom: 4px;
    }
    
    .student-course {
        color: #6c757d;
        font-size: 13px;
        margin-bottom: 8px;
    }
    
    .view-lesson-btn {
        background: {{ $primaryColor }};
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
    }
    
    .view-lesson-btn:hover {
        opacity: 0.9;
    }
    
    .no-lessons {
        text-align: center;
        color: #6c757d;
        padding: 20px;
        font-size: 14px;
    }
    
    /* Inline Calendar Wrapper */
    .inline-calendar-wrapper {
        display: block;
        margin-bottom: 20px;
        clear: both;
    }
    
    /* Calendar View Styles */
    .calendar-container {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: visible;
    }
    
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        background: {{ $primaryColor }};
        color: white;
    }
    
    .calendar-nav-btn {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
    }
    
    .calendar-nav-btn:hover {
        background: rgba(255,255,255,0.3);
    }
    
    .calendar-title {
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0 0 8px 8px;
    }
    
    .calendar-day-header {
        padding: 10px;
        text-align: center;
        font-weight: 600;
        color: #6c757d;
        font-size: 12px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .calendar-day {
        min-height: 80px;
        padding: 8px;
        border: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .calendar-day:hover {
        background: #f8f9fa;
    }
    
    .calendar-day.other-month {
        background: #fafafa;
        color: #ccc;
    }
    
    .calendar-day.today {
        background: #e3f2fd;
    }
    
    .calendar-day-number {
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .calendar-indicators {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 4px;
    }
    
    .calendar-indicator {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
        color: white;
    }
    
    .calendar-indicator.my-slot {
        background: #28a745;
    }
    
    .calendar-indicator.available {
        background: {{ $primaryColor }};
    }
    
    .calendar-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 2px;
    }
    
    .calendar-dot.my-slot {
        background: #28a745;
    }
    
    .calendar-dot.available {
        background: {{ $primaryColor }};
    }
    
    /* Tablet Responsiveness */
    @media (max-width: 992px) {
        .schedule-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .schedule-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .schedule-header h1 {
            font-size: 1.75rem;
        }
        
        .main-toggle {
            width: 100%;
        }
        
        .main-toggle-btn {
            flex: 1;
            padding: 12px 20px;
            font-size: 14px;
        }
        
        .schedule-sidebar {
            width: 100%;
            max-width: none;
            margin-top: 0;
        }
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .schedule-container {
            padding: 10px;
        }
        
        .schedule-header h1 {
            font-size: 1.2rem;
        }
        
        .main-toggle-btn {
            padding: 8px 12px;
            font-size: 0.75rem;
        }
        
        .schedule-date-header {
            padding: 10px 12px;
            font-size: 0.85rem;
        }
        
        .slot-item {
            padding: 12px;
        }
        
        .slot-time {
            font-size: 0.9rem;
        }
        
        .slot-info {
            font-size: 0.8rem;
        }
        
        .collapse-btn {
            padding: 8px 14px;
            font-size: 0.75rem;
        }
        
        .sidebar-section {
            padding: 12px;
        }
        
        .calendar-day {
            min-height: 60px;
            padding: 4px;
        }
    }
    
    /* Small Mobile */
    @media (max-width: 480px) {
        .schedule-container {
            padding: 8px;
        }
        
        .main-toggle-btn {
            padding: 6px 10px;
            font-size: 11px;
        }
        
        .schedule-sidebar {
            display: none;
        }
        
        .mobile-sidebar-btn {
            display: inline-block !important;
        }
    }
    
    /* Mobile Sidebar Button */
    .mobile-sidebar-btn {
        display: none;
    }
    
    /* Filter/Checkbox Container for Mobile */
    .schedule-filters {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    
    .filter-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 14px;
        color: #6c757d;
        white-space: nowrap;
    }
    
    .filter-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: {{ $primaryColor }};
    }
    
    @media (max-width: 768px) {
        .schedule-filters {
            gap: 8px;
            margin-top: 8px;
        }
        
        .filter-checkbox {
            font-size: 11px;
            gap: 4px;
            background: #f8f9fa;
            padding: 6px 10px;
            border-radius: 16px;
            border: 1px solid #dee2e6;
        }
        
        .filter-checkbox input[type="checkbox"] {
            width: 14px;
            height: 14px;
        }
        
        .filter-checkbox span {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }
    
    @media (max-width: 480px) {
        .schedule-header-controls {
            flex-direction: column;
            align-items: stretch !important;
            gap: 8px !important;
        }
        
        .schedule-filters {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
        }
        
        .filter-checkbox {
            font-size: 10px;
            padding: 5px 8px;
            justify-content: center;
        }
        
        .filter-checkbox input[type="checkbox"] {
            width: 12px;
            height: 12px;
        }
        
        .filter-checkbox span {
            max-width: none;
        }
    }
</style>

<div class="schedule-container">
    <div class="schedule-header">
        <h1>Schedule</h1>
        <div style="display: flex; align-items: center; gap: 12px;">
            <div class="main-toggle">
                <button class="main-toggle-btn active" id="toggle-my-slots" type="button" onclick="
                    document.getElementById('toggle-my-slots').classList.add('active');
                    document.getElementById('toggle-available-slots').classList.remove('active');
                    document.getElementById('my-slots-view').classList.add('active');
                    document.getElementById('available-slots-view').classList.remove('active');
                ">My Slots</button>
                <button class="main-toggle-btn" id="toggle-available-slots" type="button" onclick="
                    document.getElementById('toggle-available-slots').classList.add('active');
                    document.getElementById('toggle-my-slots').classList.remove('active');
                    document.getElementById('available-slots-view').classList.add('active');
                    document.getElementById('my-slots-view').classList.remove('active');
                ">Available Slots</button>
            </div>
        </div>
    </div>
    
    @if(session('success'))
        <div id="success-alert" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #155724; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    @endif
    
    @if(session('error'))
        <div id="error-alert" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #721c24; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    @endif
    
    @if($errors->any())
        <div id="error-alert" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;">
            <button onclick="this.parentElement.remove()" style="position: absolute; top: 8px; right: 8px; background: none; border: none; color: #721c24; font-size: 1.2rem; cursor: pointer;">&times;</button>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <!-- My Slots View -->
    <div id="my-slots-view" class="main-view-section active">
        <div class="schedule-grid">
            <div class="schedule-main">
                <!-- Inline Calendar (hidden by default) -->
                <div id="inline-calendar" class="inline-calendar-wrapper" style="display: none;">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <button class="calendar-nav-btn" onclick="changeMonth(-1)">‹</button>
                            <span class="calendar-title" id="currentMonth">{{ now()->format('F Y') }}</span>
                            <button class="calendar-nav-btn" onclick="changeMonth(1)">›</button>
                        </div>
                        <div class="calendar-grid" id="calendarGrid">
                            <!-- Calendar days rendered by JavaScript -->
                        </div>
                    </div>
                    <div style="display: flex; gap: 20px; margin-top: 12px; padding: 12px; background: white; border-radius: 8px; border: 1px solid #dee2e6;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span class="calendar-dot my-slot"></span>
                            <span style="font-size: 13px; color: #6c757d;">My Slots</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span class="calendar-dot available"></span>
                            <span style="font-size: 13px; color: #6c757d;">Available</span>
                        </div>
                    </div>
                </div>
                
                <div class="schedule-header-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; margin-top: 20px; flex-wrap: wrap; gap: 12px;">
                    <h3 style="margin: 0; color: #000; font-size: 1.2rem; font-weight: 600;">My Scheduled Slots</h3>
                    <div class="schedule-filters">
                        <label class="filter-checkbox">
                            <input type="checkbox" id="collapse-all-my" onchange="toggleCollapseAllMySlots(this)">
                            <span>Collapse All</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" id="show-past-my" onchange="toggleShowPastMySlots(this)">
                            <span>Show Past</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" id="show-calendar-my" onchange="toggleInlineCalendarMy()">
                            <span>Calendar</span>
                        </label>
                        <button class="collapse-btn mobile-sidebar-btn" onclick="toggleMobileSidebar()" style="padding: 6px 12px; font-size: 14px;">Today's Lesson</button>
                    </div>
                </div>
                
                @forelse($groupedMySlots as $date => $dateSlots)
                    @php
                        $isPast = \Carbon\Carbon::parse($date)->lt(now()->startOfDay());
                    @endphp
                    <div class="schedule-item" data-is-past="{{ $isPast ? 'true' : 'false' }}" style="{{ $isPast ? 'display: none;' : '' }}">
                        <div class="schedule-date-header" onclick="toggleDate(this)">
                            <span class="date-text">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="schedule-bookings" style="max-height: 800px;">
                            @foreach($dateSlots as $slot)
                                @php
                                    $instructor = $slot->instructors->firstWhere('id', $instructorId);
                                    $assignmentType = $instructor ? $instructor->pivot->assignment_type : 'self_selected';
                                    $hasPendingRequest = in_array($slot->id, $pendingRemovalRequests);
                                    $daysUntilSlot = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($slot->date)->startOfDay(), false);
                                    $canRequestRemoval = $daysUntilSlot >= $minimumNoticeDays;
                                    // Show only bookings assigned to this instructor
                                    $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                                @endphp
                                <div class="slot-item">
                                    <div class="slot-indicator {{ $assignmentType === 'admin_assigned' ? 'admin-assigned' : 'my-slot' }}"></div>
                                    <div class="slot-details">
                                        <div class="slot-time">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                            @if($hasPendingRequest)
                                                <span class="slot-badge pending">Removal Requested</span>
                                            @elseif($assignmentType === 'admin_assigned')
                                                <span class="slot-badge admin-assigned">Admin Assigned</span>
                                            @else
                                                <span class="slot-badge my-slot">My Slot</span>
                                            @endif
                                        </div>
                                        <div class="slot-info">
                                            {{ $slot->instructors->count() }} / {{ $slot->max_instructors ?? 1 }} instructors
                                            @if($slotBookings->count() > 0)
                                                • {{ $slotBookings->count() }} student(s) booked
                                            @endif
                                        </div>
                                        @if($slot->notes)
                                            <div class="slot-info">{{ $slot->notes }}</div>
                                        @endif
                                        
                                        @if(!$hasPendingRequest)
                                            <div class="slot-actions">
                                                @if($assignmentType === 'admin_assigned')
                                                    <button type="button" class="btn-slot btn-request" 
                                                            onclick="showRemovalRequestModal({{ $slot->id }}, {{ $canRequestRemoval ? 'true' : 'false' }}, {{ $minimumNoticeDays }}, {{ $daysUntilSlot }})"
                                                            {{ !$canRequestRemoval ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' }}>
                                                        Request Removal
                                                    </button>
                                                    @if(!$canRequestRemoval)
                                                        <span style="font-size: 11px; color: #dc3545; margin-left: 8px;">
                                                            (Minimum {{ $minimumNoticeDays }} days notice required)
                                                        </span>
                                                    @endif
                                                @else
                                                    <form method="POST" action="{{ route('schools.instructor.timeslots.toggle', ['school' => $school->slug, 'id' => $slot->id]) }}" style="display: inline;" data-no-ajax="true">
                                                        @csrf
                                                        <button type="submit" class="btn-slot btn-leave" onclick="return confirm('Are you sure you want to leave this slot?')">
                                                            Leave Slot
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p style="text-align: center; color: #6c757d; padding: 40px;">
                        No slots selected yet. Go to "Available Slots" to select time slots.
                    </p>
                @endforelse
            </div>
            
            <div class="schedule-sidebar">
                <!-- Today's Lesson Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-section-title">Today's Lesson</h3>
                    @if($todaySlots->isNotEmpty())
                        @foreach($todaySlots->sortBy('start_time') as $slot)
                            @php
                                // Show only bookings assigned to this instructor
                                $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                            @endphp
                            <div class="today-lesson-card">
                                <div class="lesson-date">
                                    <span>{{ \Carbon\Carbon::parse($slot->date)->format('l, F j, Y') }}</span>
                                </div>
                                <div class="lesson-time">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                </div>
                                
                                @if($slotBookings->isNotEmpty())
                                    @foreach($slotBookings as $booking)
                                        <div class="student-item">
                                            <div class="student-name">{{ $booking->student->name ?? 'Student' }}</div>
                                            <div class="student-course">{{ $booking->course->title ?? 'Course' }}</div>
                                            @if($booking->session_status)
                                                <span class="slot-badge {{ $booking->session_status === 'completed' ? 'my-slot' : 'pending' }}">
                                                    {{ ucfirst($booking->session_status) }}
                                                </span>
                                            @endif
                                            <button type="button" class="view-lesson-btn" onclick="openLessonModal({{ $booking->id }})">
                                                View Details
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="no-lessons" style="padding: 10px;">
                                        <p>No students booked for this slot</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="no-lessons">
                            <p>No lessons scheduled for today</p>
                        </div>
                    @endif
                </div>
                
                <!-- Upcoming Schedule Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-section-title">Upcoming This Week</h3>
                    @forelse($upcomingSlots as $slot)
                        @php
                            $instructor = $slot->instructors->firstWhere('id', $instructorId);
                            $assignmentType = $instructor ? $instructor->pivot->assignment_type : 'self_selected';
                        @endphp
                        <div class="mini-schedule-card" style="border-left-color: {{ $assignmentType === 'admin_assigned' ? '#ff9800' : '#28a745' }};">
                            <div class="mini-schedule-date">
                                {{ \Carbon\Carbon::parse($slot->date)->format('D, M d, Y') }}
                            </div>
                            <div class="mini-schedule-info">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            </div>
                            <span class="slot-badge {{ $assignmentType === 'admin_assigned' ? 'admin-assigned' : 'my-slot' }}" style="font-size: 10px; padding: 2px 6px;">
                                {{ $assignmentType === 'admin_assigned' ? 'Admin' : 'Self' }}
                            </span>
                        </div>
                    @empty
                        <p style="text-align: center; color: #6c757d; padding: 20px; font-size: 14px;">
                            No upcoming slots scheduled.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <!-- Available Slots View -->
    <div id="available-slots-view" class="main-view-section">
        <div class="schedule-grid">
            <div class="schedule-main">
                <!-- Inline Calendar for Available Slots (hidden by default) -->
                <div id="inline-calendar-available" class="inline-calendar-wrapper" style="display: none;">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <button class="calendar-nav-btn" onclick="changeMonth(-1)">‹</button>
                            <span class="calendar-title" id="currentMonthAvailable">{{ now()->format('F Y') }}</span>
                            <button class="calendar-nav-btn" onclick="changeMonth(1)">›</button>
                        </div>
                        <div class="calendar-grid" id="calendarGridAvailable">
                            <!-- Calendar days rendered by JavaScript -->
                        </div>
                    </div>
                    <div style="display: flex; gap: 20px; margin-top: 12px; padding: 12px; background: white; border-radius: 8px; border: 1px solid #dee2e6;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span class="calendar-dot my-slot"></span>
                            <span style="font-size: 13px; color: #6c757d;">My Slots</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span class="calendar-dot available"></span>
                            <span style="font-size: 13px; color: #6c757d;">Available</span>
                        </div>
                    </div>
                </div>
                
                <div class="schedule-header-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; margin-top: 20px; flex-wrap: wrap; gap: 12px;">
                    <h3 style="margin: 0; color: #000; font-size: 1.2rem; font-weight: 600;">Available Time Slots</h3>
                    <div class="schedule-filters">
                        <label class="filter-checkbox">
                            <input type="checkbox" id="collapse-all-available" onchange="toggleCollapseAllCheckbox(this)">
                            <span>Collapse All</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" id="show-past-available" onchange="toggleShowPastCheckbox(this)">
                            <span>Show Past</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" id="show-calendar-available" onchange="toggleInlineCalendar()">
                            <span>Calendar</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" id="show-all-courses" onchange="toggleShowAllCourses()">
                            <span>All Courses</span>
                        </label>
                    </div>
                </div>
                
                @forelse($groupedAvailableSlots as $date => $dateSlots)
                    @php
                        $isPast = \Carbon\Carbon::parse($date)->lt(now()->startOfDay());
                        // Check if any slots are visible (qualified) for this date
                        $hasVisibleSlots = $dateSlots->filter(function($slot) use ($qualifiedCourseIds) {
                            return empty($qualifiedCourseIds) || in_array($slot->course_id, $qualifiedCourseIds);
                        })->count() > 0;
                    @endphp
                    <div class="schedule-item" data-is-past="{{ $isPast ? 'true' : 'false' }}" data-has-visible="{{ $hasVisibleSlots ? 'true' : 'false' }}" style="{{ $isPast || !$hasVisibleSlots ? 'display: none;' : '' }}">
                        <div class="schedule-date-header" onclick="toggleDate(this)">
                            <span class="date-text">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="schedule-bookings" style="max-height: 800px;">
                            @foreach($dateSlots as $slot)
                                @php
                                    $spotsLeft = ($slot->max_instructors ?? 1) - $slot->instructors->count();
                                    $isQualifiedForCourse = empty($qualifiedCourseIds) || in_array($slot->course_id, $qualifiedCourseIds);
                                    $courseName = $slot->course->title ?? 'General';
                                @endphp
                                <div class="slot-item" data-qualified="{{ $isQualifiedForCourse ? 'true' : 'false' }}" style="{{ !$isQualifiedForCourse ? 'display: none;' : '' }}">
                                    <div class="slot-indicator available"></div>
                                    <div class="slot-details">
                                        <div class="slot-time">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                            <span class="slot-badge available">Available</span>
                                            @if($isQualifiedForCourse)
                                                <span class="slot-badge" style="background: #d4edda; color: #155724;">{{ $courseName }} ✓</span>
                                            @else
                                                <span class="slot-badge" style="background: #fff3cd; color: #856404;">{{ $courseName }}</span>
                                            @endif
                                        </div>
                                        <div class="slot-info">
                                            {{ $slot->instructors->count() }} / {{ $slot->max_instructors ?? 1 }} instructors
                                            • {{ $spotsLeft }} spot(s) left
                                            @if(!$isQualifiedForCourse)
                                                • <span style="color: #856404;">Not your specialty</span>
                                            @endif
                                        </div>
                                        @if($slot->notes)
                                            <div class="slot-info">{{ $slot->notes }}</div>
                                        @endif
                                        
                                        <div class="slot-actions">
                                            <form method="POST" action="{{ route('schools.instructor.timeslots.toggle', ['school' => $school->slug, 'id' => $slot->id]) }}" style="display: inline;" data-no-ajax="true">
                                                @csrf
                                                <button type="submit" class="btn-slot btn-select">
                                                    Select Slot
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p style="text-align: center; color: #6c757d; padding: 40px;">
                        No available time slots at the moment.
                    </p>
                @endforelse
            </div>
            
            <div class="schedule-sidebar">
                <!-- Today's Lesson Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-section-title">Today's Lesson</h3>
                    @if($todaySlots->isNotEmpty())
                        @foreach($todaySlots->sortBy('start_time') as $slot)
                            @php
                                // Show only bookings assigned to this instructor
                                $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                            @endphp
                            <div class="today-lesson-card">
                                <div class="lesson-date">
                                    <span>{{ \Carbon\Carbon::parse($slot->date)->format('l, F j, Y') }}</span>
                                </div>
                                <div class="lesson-time">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                </div>
                                
                                @if($slotBookings->isNotEmpty())
                                    @foreach($slotBookings as $booking)
                                        <div class="student-item">
                                            <div class="student-name">{{ $booking->student->name ?? 'Student' }}</div>
                                            <div class="student-course">{{ $booking->course->title ?? 'Course' }}</div>
                                            <button type="button" class="view-lesson-btn" onclick="openLessonModal({{ $booking->id }})">
                                                View Details
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="no-lessons" style="padding: 10px;">
                                        <p>No students booked</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="no-lessons">
                            <p>No lessons scheduled for today</p>
                        </div>
                    @endif
                </div>
                
                <!-- Your Schedule Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-section-title">Your Schedule</h3>
                    @forelse($upcomingSlots as $slot)
                        <div class="mini-schedule-card">
                            <div class="mini-schedule-date">
                                {{ \Carbon\Carbon::parse($slot->date)->format('D, M d, Y') }}
                            </div>
                            <div class="mini-schedule-info">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                            </div>
                        </div>
                    @empty
                        <p style="text-align: center; color: #6c757d; padding: 20px; font-size: 14px;">
                            No upcoming slots scheduled.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal scrollbar hiding styles -->
<style>
    .modal-body-scroll {
        overflow-y: auto;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .modal-body-scroll::-webkit-scrollbar {
        display: none;
    }
</style>

<!-- Day Details Modal (for calendar clicks) -->
<div class="day-modal" id="dayModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;" onclick="if(event.target === this) closeDayModal()">
    <div style="background: white; border-radius: 12px; max-width: 600px; width: 90%; max-height: 80vh; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background: {{ $primaryColor }}; color: white;">
            <div>
                <h2 style="margin: 0; font-size: 1.25rem;">Schedule Details</h2>
                <div id="modalDayDate" style="font-size: 0.9rem; opacity: 0.9;"></div>
            </div>
            <button onclick="closeDayModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div id="modalDayBody" class="modal-body-scroll" style="padding: 20px; max-height: calc(80vh - 80px);">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<!-- Removal Request Modal -->
<div class="day-modal" id="removalRequestModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;" onclick="if(event.target === this) closeRemovalRequestModal()">
    <div style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 80vh; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background: {{ $primaryColor }}; color: white;">
            <h2 style="margin: 0; font-size: 1.25rem;">Request Removal from Time Slot</h2>
            <button onclick="closeRemovalRequestModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div style="padding: 20px;">
            <form id="removalRequestForm" method="POST">
                @csrf
                <div id="removalWarning"></div>
                <div style="margin-bottom: 20px;">
                    <p style="color: #666; margin-bottom: 15px;">
                        Please provide a reason for requesting removal from this admin-assigned time slot. Your request will be reviewed by an administrator.
                    </p>
                    <label for="removal_reason" style="display: block; font-weight: 600; margin-bottom: 8px;">
                        Reason for Removal Request: <span style="color: #dc3545;">*</span>
                    </label>
                    <textarea 
                        id="removal_reason" 
                        name="reason" 
                        required 
                        maxlength="500"
                        style="width: 100%; min-height: 120px; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-family: inherit; font-size: 14px; resize: vertical;"
                        placeholder="E.g., I have a conflicting appointment, personal emergency, etc."
                    ></textarea>
                    <small style="color: #999; display: block; margin-top: 5px;">Maximum 500 characters</small>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeRemovalRequestModal()" style="background: #e0e0e0; color: #666; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" id="removalSubmitBtn" class="btn-slot btn-request">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lesson Details Modal -->
<div class="day-modal" id="lessonModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;" onclick="if(event.target === this) closeLessonModal()">
    <div style="background: white; border-radius: 12px; max-width: 700px; width: 95%; max-height: 90vh; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background: {{ $primaryColor }}; color: white;">
            <h2 style="margin: 0; font-size: 1.25rem;">Lesson Details</h2>
            <button onclick="closeLessonModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div id="lessonModalBody" class="modal-body-scroll" style="padding: 20px; max-height: calc(90vh - 80px);">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<!-- Mobile Sidebar Popup -->
<div id="mobileSidebarPopup" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;" onclick="if(event.target === this) toggleMobileSidebar()">
    <div style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 80vh; overflow: hidden; margin: 10vh auto;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">Today's Schedule</h3>
            <button onclick="toggleMobileSidebar()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body-scroll" style="padding: 16px; max-height: calc(80vh - 70px);">
            @if($todaySlots->isNotEmpty())
                @foreach($todaySlots->sortBy('start_time') as $slot)
                    @php
                        // Show only bookings assigned to this instructor
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
                                    <button type="button" class="view-lesson-btn" onclick="openLessonModal({{ $booking->id }}); toggleMobileSidebar();">
                                        View Details
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <p style="color: #6c757d; font-size: 13px;">No students booked</p>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="no-lessons">
                    <p>No lessons scheduled for today</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Hidden data for calendar JavaScript -->
<script>
    // Schedule data for calendar
    window.schedulesData = {};
    
    @foreach($allTimeSlots as $slot)
        @php
            $dateKey = $slot->date->format('Y-m-d');
            $isMySlot = $slot->instructors->contains('id', $instructorId);
            $instructor = $slot->instructors->firstWhere('id', $instructorId);
            $assignmentType = $instructor ? $instructor->pivot->assignment_type : null;
            $hasPendingRequest = in_array($slot->id, $pendingRemovalRequests);
            $daysUntilSlot = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($slot->date)->startOfDay(), false);
            $canRequestRemoval = $daysUntilSlot >= $minimumNoticeDays;
        @endphp
        if (!window.schedulesData["{{ $dateKey }}"]) {
            window.schedulesData["{{ $dateKey }}"] = [];
        }
        window.schedulesData["{{ $dateKey }}"].push({
            id: {{ $slot->id }},
            date: "{{ $dateKey }}",
            start_time: "{{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}",
            end_time: "{{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}",
            start_time_raw: "{{ $slot->start_time }}",
            end_time_raw: "{{ $slot->end_time }}",
            course_name: "{{ addslashes($slot->course->title ?? 'General') }}",
            is_my_slot: {{ $isMySlot ? 'true' : 'false' }},
            assignment_type: "{{ $assignmentType ?? 'self_selected' }}",
            has_pending_request: {{ $hasPendingRequest ? 'true' : 'false' }},
            status: "{{ $slot->status }}",
            instructors_count: {{ $slot->instructors->count() }},
            max_instructors: {{ $slot->max_instructors ?? 1 }},
            is_full: {{ $slot->instructors->count() >= ($slot->max_instructors ?? 1) ? 'true' : 'false' }},
            notes: "{{ addslashes($slot->notes ?? '') }}",
            toggle_url: "{{ route('schools.instructor.timeslots.toggle', ['school' => $school->slug, 'id' => $slot->id]) }}",
            can_request_removal: {{ $canRequestRemoval ? 'true' : 'false' }},
            minimum_notice_days: {{ $minimumNoticeDays }},
            days_until_slot: {{ $daysUntilSlot }}
        });
    @endforeach
    
    // Instructor's current schedule for conflict checking
    window.instructorSchedule = @json($instructorSchedule);
</script>

<!-- Conflict Warning Modal -->
<div class="day-modal" id="conflictModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; align-items: center; justify-content: center;" onclick="if(event.target === this) closeConflictModal()">
    <div style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 80vh; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; background: #ffc107; color: #000;">
            <div>
                <h2 style="margin: 0; font-size: 1.25rem; display: flex; align-items: center; gap: 8px;">
                    ⚠️ Schedule Conflict Warning
                </h2>
            </div>
            <button onclick="closeConflictModal()" style="background: none; border: none; color: #000; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div id="conflictModalBody" class="modal-body-scroll" style="padding: 20px; max-height: calc(80vh - 140px);">
            <!-- Content loaded dynamically -->
        </div>
        <div style="padding: 16px 20px; border-top: 1px solid #dee2e6; display: flex; gap: 10px; justify-content: flex-end; background: #f8f9fa;">
            <button onclick="closeConflictModal()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 500;">
                Cancel
            </button>
            <button id="confirmConflictBtn" onclick="confirmSlotSelection()" style="background: {{ $primaryColor }}; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 500;">
                Select Anyway
            </button>
        </div>
    </div>
</div>

<script>
    let inlineCalendarVisible = false;
    let pendingSlotFormId = null;
    
    // Select slot via AJAX without page reload
    function selectSlot(slotId, url, button) {
        const originalText = button.textContent;
        button.textContent = 'Selecting...';
        button.disabled = true;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success notification
                showSlotNotification(data.message, 'success');
                // Hide the slot item from available slots
                const slotItem = button.closest('.slot-item');
                if (slotItem) {
                    slotItem.style.transition = 'opacity 0.3s';
                    slotItem.style.opacity = '0';
                    setTimeout(() => {
                        slotItem.remove();
                        // Check if the date group is now empty
                        checkEmptyDateGroups();
                    }, 300);
                }
            } else {
                showSlotNotification(data.message || 'Failed to select slot', 'error');
                button.textContent = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSlotNotification('An error occurred. Please try again.', 'error');
            button.textContent = originalText;
            button.disabled = false;
        });
    }
    
    function showSlotNotification(message, type) {
        // Remove existing notifications
        const existing = document.querySelectorAll('.slot-notification');
        existing.forEach(n => n.remove());
        
        const notification = document.createElement('div');
        notification.className = 'slot-notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            animation: fadeIn 0.2s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background: ${type === 'success' ? '#28a745' : '#dc3545'};
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    function checkEmptyDateGroups() {
        // Check available slots view for empty date groups
        const dateGroups = document.querySelectorAll('#available-slots-view .schedule-item');
        dateGroups.forEach(group => {
            const visibleSlots = group.querySelectorAll('.slot-item:not([style*="display: none"])');
            if (visibleSlots.length === 0) {
                group.style.display = 'none';
            }
        });
    }
    
    // Conflict checking functions
    function timeToMinutes(timeStr) {
        // Convert time string like "09:00:00" or "9:00 AM" to minutes
        if (timeStr.includes(':')) {
            const parts = timeStr.split(':');
            let hours = parseInt(parts[0]);
            const minutes = parseInt(parts[1]);
            
            // Check if it's in AM/PM format
            if (timeStr.toLowerCase().includes('pm') && hours !== 12) {
                hours += 12;
            } else if (timeStr.toLowerCase().includes('am') && hours === 12) {
                hours = 0;
            }
            
            return hours * 60 + minutes;
        }
        return 0;
    }
    
    function formatTimeDisplay(timeStr) {
        // Convert 24-hour time to 12-hour format for display
        const parts = timeStr.split(':');
        let hours = parseInt(parts[0]);
        const minutes = parts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        return `${hours}:${minutes} ${ampm}`;
    }
    
    function checkTimeOverlap(start1, end1, start2, end2) {
        const s1 = timeToMinutes(start1);
        const e1 = timeToMinutes(end1);
        const s2 = timeToMinutes(start2);
        const e2 = timeToMinutes(end2);
        
        // Check if time ranges overlap
        return s1 < e2 && s2 < e1;
    }
    
    function checkConflictAndSelect(slotId, date, startTime, endTime, courseName) {
        const schedule = window.instructorSchedule[date] || [];
        const conflicts = [];
        
        for (const existing of schedule) {
            if (checkTimeOverlap(startTime, endTime, existing.start, existing.end)) {
                conflicts.push({
                    course: existing.course,
                    start: formatTimeDisplay(existing.start),
                    end: formatTimeDisplay(existing.end)
                });
            }
        }
        
        if (conflicts.length > 0) {
            showConflictModal(slotId, date, startTime, endTime, courseName, conflicts);
        } else {
            // No conflicts, submit the form
            document.getElementById('select-slot-form-' + slotId).submit();
        }
    }
    
    function showConflictModal(slotId, date, startTime, endTime, courseName, conflicts) {
        pendingSlotFormId = slotId;
        
        const dateObj = new Date(date + 'T00:00:00');
        const formattedDate = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        
        let conflictHtml = `
            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <p style="margin: 0 0 8px 0; font-weight: 600; color: #856404;">
                    You are trying to select:
                </p>
                <div style="background: white; border-radius: 6px; padding: 12px; margin-bottom: 0;">
                    <div style="font-weight: 600; color: #000;">${courseName}</div>
                    <div style="color: #666; font-size: 14px;">${formattedDate}</div>
                    <div style="color: #666; font-size: 14px;">${formatTimeDisplay(startTime)} - ${formatTimeDisplay(endTime)}</div>
                </div>
            </div>
            
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 16px;">
                <p style="margin: 0 0 12px 0; font-weight: 600; color: #721c24;">
                    ⚠️ This conflicts with ${conflicts.length} existing slot${conflicts.length > 1 ? 's' : ''}:
                </p>
        `;
        
        for (const conflict of conflicts) {
            conflictHtml += `
                <div style="background: white; border-radius: 6px; padding: 12px; margin-bottom: 8px; border-left: 4px solid #dc3545;">
                    <div style="font-weight: 600; color: #000;">${conflict.course}</div>
                    <div style="color: #666; font-size: 14px;">${conflict.start} - ${conflict.end}</div>
                </div>
            `;
        }
        
        conflictHtml += `
            </div>
            <p style="margin: 16px 0 0 0; color: #666; font-size: 14px;">
                <strong>Note:</strong> Selecting this slot means you will be assigned to multiple overlapping time slots. 
                Make sure you can handle both assignments or coordinate with the school admin.
            </p>
        `;
        
        document.getElementById('conflictModalBody').innerHTML = conflictHtml;
        document.getElementById('conflictModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function closeConflictModal() {
        document.getElementById('conflictModal').style.display = 'none';
        document.body.style.overflow = '';
        pendingSlotFormId = null;
    }
    
    function confirmSlotSelection() {
        if (pendingSlotFormId) {
            document.getElementById('select-slot-form-' + pendingSlotFormId).submit();
        } else if (pendingSlotUrl) {
            // Submit via POST for modal-based selection
            submitSlotSelection(pendingSlotUrl);
        }
        closeConflictModal();
    }
    
    let pendingSlotUrl = null;
    
    function checkConflictFromModal(slotId, date, startTime, endTime, courseName, toggleUrl) {
        const schedule = window.instructorSchedule[date] || [];
        const conflicts = [];
        
        for (const existing of schedule) {
            if (checkTimeOverlap(startTime, endTime, existing.start, existing.end)) {
                conflicts.push({
                    course: existing.course,
                    start: formatTimeDisplay(existing.start),
                    end: formatTimeDisplay(existing.end)
                });
            }
        }
        
        if (conflicts.length > 0) {
            closeDayModal(); // Close the day details modal first
            pendingSlotUrl = toggleUrl;
            pendingSlotFormId = null;
            showConflictModalFromCalendar(slotId, date, startTime, endTime, courseName, conflicts);
        } else {
            // No conflicts, submit directly
            submitSlotSelection(toggleUrl);
        }
    }
    
    function showConflictModalFromCalendar(slotId, date, startTime, endTime, courseName, conflicts) {
        const dateObj = new Date(date + 'T00:00:00');
        const formattedDate = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        
        let conflictHtml = `
            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <p style="margin: 0 0 8px 0; font-weight: 600; color: #856404;">
                    You are trying to select:
                </p>
                <div style="background: white; border-radius: 6px; padding: 12px; margin-bottom: 0;">
                    <div style="font-weight: 600; color: #000;">${courseName}</div>
                    <div style="color: #666; font-size: 14px;">${formattedDate}</div>
                    <div style="color: #666; font-size: 14px;">${formatTimeDisplay(startTime)} - ${formatTimeDisplay(endTime)}</div>
                </div>
            </div>
            
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 16px;">
                <p style="margin: 0 0 12px 0; font-weight: 600; color: #721c24;">
                    ⚠️ This conflicts with ${conflicts.length} existing slot${conflicts.length > 1 ? 's' : ''}:
                </p>
        `;
        
        for (const conflict of conflicts) {
            conflictHtml += `
                <div style="background: white; border-radius: 6px; padding: 12px; margin-bottom: 8px; border-left: 4px solid #dc3545;">
                    <div style="font-weight: 600; color: #000;">${conflict.course}</div>
                    <div style="color: #666; font-size: 14px;">${conflict.start} - ${conflict.end}</div>
                </div>
            `;
        }
        
        conflictHtml += `
            </div>
            <p style="margin: 16px 0 0 0; color: #666; font-size: 14px;">
                <strong>Note:</strong> Selecting this slot means you will be assigned to multiple overlapping time slots. 
                Make sure you can handle both assignments or coordinate with the school admin.
            </p>
        `;
        
        document.getElementById('conflictModalBody').innerHTML = conflictHtml;
        document.getElementById('conflictModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function submitSlotSelection(url) {
        // Create and submit a form to the toggle URL
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
    
    // View switching function
    function switchMainView(viewName) {
        // Remove active from all toggle buttons
        document.getElementById('toggle-my-slots').classList.remove('active');
        document.getElementById('toggle-available-slots').classList.remove('active');
        
        // Hide all views
        document.getElementById('my-slots-view').classList.remove('active');
        document.getElementById('available-slots-view').classList.remove('active');
        
        // Hide inline calendars when switching views
        document.getElementById('inline-calendar').style.display = 'none';
        document.getElementById('inline-calendar-available').style.display = 'none';
        inlineCalendarVisible = false;
        updateCalendarButtonText(false);
        
        // Show selected view
        if (viewName === 'my-slots') {
            document.getElementById('toggle-my-slots').classList.add('active');
            document.getElementById('my-slots-view').classList.add('active');
        } else if (viewName === 'available-slots') {
            document.getElementById('toggle-available-slots').classList.add('active');
            document.getElementById('available-slots-view').classList.add('active');
        }
    }
    
    function updateCalendarButtonText(showingCalendar) {
        const calendarBtn = document.getElementById('toggle-calendar');
        const calendarBtnAvailable = document.getElementById('toggle-calendar-available');
        
        if (showingCalendar) {
            if (calendarBtn) calendarBtn.textContent = 'Hide Calendar';
            if (calendarBtnAvailable) calendarBtnAvailable.textContent = 'Hide Calendar';
        } else {
            if (calendarBtn) calendarBtn.textContent = 'Show Calendar';
            if (calendarBtnAvailable) calendarBtnAvailable.textContent = 'Show Calendar';
        }
    }
    
    function toggleInlineCalendar() {
        const mySlotsView = document.getElementById('my-slots-view');
        const availableSlotsView = document.getElementById('available-slots-view');
        
        // Determine which view is active
        const isMySlots = mySlotsView.classList.contains('active');
        const calendarId = isMySlots ? 'inline-calendar' : 'inline-calendar-available';
        const calendar = document.getElementById(calendarId);
        const checkbox = document.getElementById('show-calendar-available');
        
        // Use checkbox state if available, otherwise toggle
        const shouldShow = checkbox ? checkbox.checked : !inlineCalendarVisible;
        
        if (shouldShow) {
            // Show calendar
            calendar.style.display = 'block';
            inlineCalendarVisible = true;
            updateCalendarButtonText(true);
            renderCalendar(isMySlots ? 'calendarGrid' : 'calendarGridAvailable', isMySlots ? 'currentMonth' : 'currentMonthAvailable');
        } else {
            // Hide calendar
            calendar.style.display = 'none';
            inlineCalendarVisible = false;
            updateCalendarButtonText(false);
        }
    }
    
    // Add event listeners when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle button event listeners
        document.getElementById('toggle-my-slots').addEventListener('click', function() {
            switchMainView('my-slots');
        });
        
        document.getElementById('toggle-available-slots').addEventListener('click', function() {
            switchMainView('available-slots');
        });
        
        // Initialize calendar
        renderCalendar();
    });
    
    function toggleDate(header) {
        const content = header.nextElementSibling;
        header.classList.toggle('collapsed');
        content.classList.toggle('collapsed');
    }
    
    function toggleCollapseAll(button) {
        const headers = document.querySelectorAll('.schedule-date-header');
        const contents = document.querySelectorAll('.schedule-bookings');
        const isCollapsed = button.textContent === 'Expand All';
        
        if (isCollapsed) {
            headers.forEach(header => header.classList.remove('collapsed'));
            contents.forEach(content => content.classList.remove('collapsed'));
            document.querySelectorAll('.collapse-btn').forEach(btn => {
                if (btn.textContent === 'Expand All') btn.textContent = 'Collapse All';
            });
        } else {
            headers.forEach(header => header.classList.add('collapsed'));
            contents.forEach(content => content.classList.add('collapsed'));
            document.querySelectorAll('.collapse-btn').forEach(btn => {
                if (btn.textContent === 'Collapse All') btn.textContent = 'Expand All';
            });
        }
    }
    
    function toggleCollapseAllCheckbox(checkbox) {
        const headers = document.querySelectorAll('#available-slots-view .schedule-date-header');
        const contents = document.querySelectorAll('#available-slots-view .schedule-bookings');
        
        if (checkbox.checked) {
            headers.forEach(header => header.classList.add('collapsed'));
            contents.forEach(content => content.classList.add('collapsed'));
        } else {
            headers.forEach(header => header.classList.remove('collapsed'));
            contents.forEach(content => content.classList.remove('collapsed'));
        }
    }
    
    function toggleCollapseAllMySlots(checkbox) {
        const headers = document.querySelectorAll('#my-slots-view .schedule-date-header');
        const contents = document.querySelectorAll('#my-slots-view .schedule-bookings');
        
        if (checkbox.checked) {
            headers.forEach(header => header.classList.add('collapsed'));
            contents.forEach(content => content.classList.add('collapsed'));
        } else {
            headers.forEach(header => header.classList.remove('collapsed'));
            contents.forEach(content => content.classList.remove('collapsed'));
        }
    }
    
    function toggleShowPastSchedules(button) {
        const isShowingPast = button.textContent === 'Hide Past Schedules';
        const pastItems = document.querySelectorAll('.schedule-item[data-is-past="true"]');
        
        if (isShowingPast) {
            pastItems.forEach(item => item.style.display = 'none');
            document.querySelectorAll('.collapse-btn').forEach(btn => {
                if (btn.textContent === 'Hide Past Schedules') btn.textContent = 'Show Past Schedules';
            });
        } else {
            pastItems.forEach(item => item.style.display = '');
            document.querySelectorAll('.collapse-btn').forEach(btn => {
                if (btn.textContent === 'Show Past Schedules') btn.textContent = 'Hide Past Schedules';
            });
        }
    }
    
    function toggleShowPastCheckbox(checkbox) {
        const pastItems = document.querySelectorAll('#available-slots-view .schedule-item[data-is-past="true"]');
        const showAllCourses = document.getElementById('show-all-courses')?.checked || false;
        
        if (checkbox.checked) {
            pastItems.forEach(item => {
                // Only show past items that have visible slots (or if showing all courses)
                const hasVisibleSlots = item.getAttribute('data-has-visible') === 'true';
                item.style.display = (hasVisibleSlots || showAllCourses) ? '' : 'none';
            });
        } else {
            pastItems.forEach(item => item.style.display = 'none');
        }
    }
    
    function toggleShowPastMySlots(checkbox) {
        const pastItems = document.querySelectorAll('#my-slots-view .schedule-item[data-is-past="true"]');
        
        if (checkbox.checked) {
            pastItems.forEach(item => item.style.display = '');
        } else {
            pastItems.forEach(item => item.style.display = 'none');
        }
    }
    
    function toggleInlineCalendarMy() {
        const calendar = document.getElementById('inline-calendar');
        const checkbox = document.getElementById('show-calendar-my');
        
        if (checkbox.checked) {
            calendar.style.display = 'block';
            renderCalendar('calendarGrid', 'currentMonth');
        } else {
            calendar.style.display = 'none';
        }
    }
    
    function toggleShowAllCourses() {
        const checkbox = document.getElementById('show-all-courses');
        const showAll = checkbox.checked;
        const nonQualifiedSlots = document.querySelectorAll('.slot-item[data-qualified="false"]');
        
        nonQualifiedSlots.forEach(slot => {
            slot.style.display = showAll ? '' : 'none';
        });
        
        // Show/hide date groups based on whether they have visible slots
        document.querySelectorAll('#available-slots-view .schedule-item').forEach(dateGroup => {
            const isPast = dateGroup.getAttribute('data-is-past') === 'true';
            const showPast = document.getElementById('show-past-available')?.checked || false;
            
            // If it's a past date and we're not showing past, keep it hidden
            if (isPast && !showPast) {
                dateGroup.style.display = 'none';
                return;
            }
            
            if (showAll) {
                // When showing all courses, show the date group if it's not past (or past is enabled)
                dateGroup.style.display = '';
            } else {
                // When filtering courses, check if any qualified slots are visible
                const hasVisibleSlots = dateGroup.getAttribute('data-has-visible') === 'true';
                dateGroup.style.display = hasVisibleSlots ? '' : 'none';
            }
        });
    }
    
    function toggleMobileSidebar() {
        const popup = document.getElementById('mobileSidebarPopup');
        if (popup.style.display === 'none') {
            popup.style.display = 'block';
            document.body.style.overflow = 'hidden';
        } else {
            popup.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
    
    // Calendar functions
    let currentMonthDate = new Date();
    
    function renderCalendar(gridId = 'calendarGrid', monthTitleId = 'currentMonth') {
        const grid = document.getElementById(gridId);
        const monthTitle = document.getElementById(monthTitleId);
        
        if (!grid || !monthTitle) return;
        
        const year = currentMonthDate.getFullYear();
        const month = currentMonthDate.getMonth();
        
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'];
        monthTitle.textContent = `${monthNames[month]} ${year}`;
        
        // Clear grid and recreate headers
        grid.innerHTML = '';
        
        // Recreate day headers
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayNames.forEach(dayName => {
            const headerDiv = document.createElement('div');
            headerDiv.className = 'calendar-day-header';
            headerDiv.textContent = dayName;
            grid.appendChild(headerDiv);
        });
        
        // Get first day of month and total days
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();
        
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        
        // Previous month days
        for (let i = firstDay - 1; i >= 0; i--) {
            const day = daysInPrevMonth - i;
            const div = document.createElement('div');
            div.className = 'calendar-day other-month';
            div.innerHTML = `<div class="calendar-day-number">${day}</div>`;
            grid.appendChild(div);
        }
        
        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const div = document.createElement('div');
            div.className = 'calendar-day';
            if (dateStr === todayStr) {
                div.classList.add('today');
            }
            
            // Count slots by type
            const daySchedules = window.schedulesData[dateStr] || [];
            const mySlotCount = daySchedules.filter(s => s.is_my_slot).length;
            const availableCount = daySchedules.filter(s => !s.is_my_slot && s.status === 'open' && !s.is_full).length;
            
            let indicatorsHtml = '<div class="calendar-indicators">';
            if (mySlotCount > 0) {
                indicatorsHtml += `<span class="calendar-indicator my-slot">${mySlotCount}</span>`;
            }
            if (availableCount > 0) {
                indicatorsHtml += `<span class="calendar-indicator available">${availableCount}</span>`;
            }
            indicatorsHtml += '</div>';
            
            div.innerHTML = `<div class="calendar-day-number">${day}</div>${indicatorsHtml}`;
            div.onclick = () => showDayModal(dateStr, `${monthNames[month]} ${day}, ${year}`);
            grid.appendChild(div);
        }
        
        // Next month days
        const totalCells = grid.querySelectorAll('.calendar-day').length + 7;
        const remaining = 42 - (totalCells - 7);
        for (let day = 1; day <= remaining; day++) {
            const div = document.createElement('div');
            div.className = 'calendar-day other-month';
            div.innerHTML = `<div class="calendar-day-number">${day}</div>`;
            grid.appendChild(div);
        }
    }
    
    function changeMonth(direction) {
        currentMonthDate.setMonth(currentMonthDate.getMonth() + direction);
        // Render both calendars if they exist
        renderCalendar('calendarGrid', 'currentMonth');
        renderCalendar('calendarGridAvailable', 'currentMonthAvailable');
    }
    
    function showDayModal(dateStr, formattedDate) {
        const modal = document.getElementById('dayModal');
        const dateEl = document.getElementById('modalDayDate');
        const body = document.getElementById('modalDayBody');
        
        dateEl.textContent = formattedDate;
        
        const daySchedules = window.schedulesData[dateStr] || [];
        
        if (daySchedules.length === 0) {
            body.innerHTML = '<p style="text-align: center; color: #6c757d; padding: 40px;">No schedules for this date.</p>';
        } else {
            let html = '';
            const mySlots = daySchedules.filter(s => s.is_my_slot);
            const availableSlots = daySchedules.filter(s => !s.is_my_slot && s.status === 'open' && !s.is_full);
            
            if (mySlots.length > 0) {
                html += '<h3 style="margin-bottom: 15px; color: #28a745; font-size: 1.1rem;">My Scheduled Slots</h3>';
                mySlots.forEach(slot => {
                    html += generateModalSlotCard(slot, true);
                });
            }
            
            if (availableSlots.length > 0) {
                html += `<h3 style="margin-top: ${mySlots.length > 0 ? '25px' : '0'}; margin-bottom: 15px; color: {{ $primaryColor }}; font-size: 1.1rem;">Available Slots</h3>`;
                availableSlots.forEach(slot => {
                    html += generateModalSlotCard(slot, false);
                });
            }
            
            body.innerHTML = html;
        }
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function generateModalSlotCard(slot, isMySlot) {
        let badgeClass = isMySlot ? (slot.assignment_type === 'admin_assigned' ? 'admin-assigned' : 'my-slot') : 'available';
        let badgeText = isMySlot ? (slot.has_pending_request ? 'Removal Requested' : (slot.assignment_type === 'admin_assigned' ? 'Admin Assigned' : 'My Slot')) : 'Available';
        
        if (slot.has_pending_request) badgeClass = 'pending';
        
        let actionHtml = '';
        if (isMySlot && !slot.has_pending_request) {
            if (slot.assignment_type === 'admin_assigned') {
                actionHtml = `
                    <button type="button" class="btn-slot btn-request" 
                            onclick="showRemovalRequestModal(${slot.id}, ${slot.can_request_removal}, ${slot.minimum_notice_days}, ${slot.days_until_slot}); closeDayModal();"
                            ${!slot.can_request_removal ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}>
                        Request Removal
                    </button>
                `;
            } else {
                actionHtml = `
                    <form method="POST" action="${slot.toggle_url}" style="display: inline;" data-no-ajax="true">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                        <button type="submit" class="btn-slot btn-leave" onclick="return confirm('Leave this slot?')">Leave Slot</button>
                    </form>
                `;
            }
        } else if (!isMySlot) {
            // Use conflict checking for available slots
            actionHtml = `
                <button type="button" class="btn-slot btn-select" onclick="checkConflictFromModal(${slot.id}, '${slot.date}', '${slot.start_time_raw}', '${slot.end_time_raw}', '${slot.course_name}', '${slot.toggle_url}')">
                    Select Slot
                </button>
            `;
        }
        }
        
        return `
            <div style="background: #f8f9fa; border-radius: 8px; padding: 16px; margin-bottom: 12px; border-left: 4px solid ${isMySlot ? (slot.assignment_type === 'admin_assigned' ? '#ff9800' : '#28a745') : '{{ $primaryColor }}'};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong>${slot.start_time} - ${slot.end_time}</strong>
                    <span class="slot-badge ${badgeClass}">${badgeText}</span>
                </div>
                <div style="color: #6c757d; font-size: 14px; margin-bottom: 8px;">
                    ${slot.instructors_count} / ${slot.max_instructors} instructors
                </div>
                ${slot.notes ? `<div style="color: #6c757d; font-size: 13px; margin-bottom: 8px;">${slot.notes}</div>` : ''}
                ${actionHtml}
            </div>
        `;
    }
    
    function closeDayModal() {
        document.getElementById('dayModal').style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // Removal Request Modal
    function showRemovalRequestModal(slotId, canRequest, minimumDays, daysRemaining) {
        const modal = document.getElementById('removalRequestModal');
        const form = document.getElementById('removalRequestForm');
        const textarea = document.getElementById('removal_reason');
        const submitBtn = document.getElementById('removalSubmitBtn');
        const warningDiv = document.getElementById('removalWarning');
        
        form.action = `{{ url($school->slug) }}/instructor/timeslots/${slotId}/request-removal`;
        textarea.value = '';
        
        if (!canRequest) {
            warningDiv.innerHTML = `
                <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                    <strong style="color: #721c24;">Cannot Submit Request</strong><br>
                    <span style="color: #666;">You must request removal at least ${minimumDays} days in advance. This slot is in ${daysRemaining} day(s).</span>
                </div>
            `;
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
        } else {
            warningDiv.innerHTML = `
                <div style="background: #cce5ff; border-left: 4px solid {{ $primaryColor }}; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                    <strong style="color: #004085;">Minimum Notice Period</strong><br>
                    <span style="color: #666;">Requests must be submitted at least ${minimumDays} days before the scheduled time slot.</span>
                </div>
            `;
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        if (canRequest) {
            setTimeout(() => textarea.focus(), 100);
        }
    }
    
    function closeRemovalRequestModal() {
        document.getElementById('removalRequestModal').style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // Lesson Details Modal
    function openLessonModal(bookingId) {
        const modal = document.getElementById('lessonModal');
        const modalBody = document.getElementById('lessonModalBody');
        
        modal.style.display = 'flex';
        modalBody.innerHTML = '<div style="text-align: center; padding: 40px;">Loading...</div>';
        
        fetch(`/{{ $school->slug }}/instructor/lessons/${bookingId}`)
            .then(response => response.json())
            .then(data => {
                modalBody.innerHTML = generateLessonForm(data);
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;">Error loading lesson details. Please try again.</div>';
            });
    }
    
    function closeLessonModal() {
        document.getElementById('lessonModal').style.display = 'none';
        document.body.style.overflow = '';
    }
    
    function generateLessonForm(data) {
        const booking = data.booking;
        const student = booking.student;
        const course = booking.course;
        const timeSlot = booking.time_slot;
        
        const availableSkills = [
            'Basic Vehicle Control',
            'Parking (90° / Angled)',
            'Parallel Parking',
            'Lane Changing',
            'Turns & Intersections',
            'Highway Driving',
            'Reverse Driving',
            'Night Driving',
            'Weather Conditions',
            'Emergency Procedures'
        ];
        
        const selectedSkills = booking.skills_practiced || [];
        
        return `
            <form onsubmit="saveLessonDetails(event, ${booking.id})">
                <!-- Session Information -->
                <div style="background: #f8f9fa; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div><strong>Student:</strong> ${student.name}</div>
                        <div><strong>Course:</strong> ${course.title}</div>
                        <div><strong>Date:</strong> ${timeSlot ? new Date(timeSlot.date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A'}</div>
                        <div><strong>Time:</strong> ${timeSlot ? formatTime(timeSlot.start_time) + ' - ' + formatTime(timeSlot.end_time) : 'N/A'}</div>
                    </div>
                </div>
                
                <!-- Session Status -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 12px 0; color: #333;">Session Status</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 6px;">Attendance</label>
                            <select name="attendance_status" required style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 6px;">
                                <option value="">Select attendance</option>
                                <option value="attended" ${booking.attendance_status === 'attended' ? 'selected' : ''}>Attended</option>
                                <option value="late" ${booking.attendance_status === 'late' ? 'selected' : ''}>Late</option>
                                <option value="absent" ${booking.attendance_status === 'absent' ? 'selected' : ''}>Absent</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 6px;">Session Status</label>
                            <select name="session_status" required style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 6px;">
                                <option value="">Select status</option>
                                <option value="completed" ${booking.session_status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="cancelled" ${booking.session_status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                <option value="rescheduled" ${booking.session_status === 'rescheduled' ? 'selected' : ''}>Rescheduled</option>
                                <option value="no-show" ${booking.session_status === 'no-show' ? 'selected' : ''}>No-Show</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Performance -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 12px 0; color: #333;">Performance</h4>
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 6px;">Session Grade (0-100)</label>
                        <input type="number" name="session_grade" min="0" max="100" step="0.01" value="${booking.session_grade || ''}" 
                               placeholder="Enter grade (optional)" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 6px;">
                    </div>
                </div>
                
                <!-- Skills Practiced -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 12px 0; color: #333;">Skills Practiced</h4>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        ${availableSkills.map(skill => `
                            <label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: #f8f9fa; border-radius: 6px; cursor: pointer;">
                                <input type="checkbox" name="skills_practiced[]" value="${skill}" ${selectedSkills.includes(skill) ? 'checked' : ''}>
                                <span style="font-size: 13px;">${skill}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>
                
                <!-- Feedback -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 12px 0; color: #333;">Feedback</h4>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-weight: 500; margin-bottom: 6px;">Instructor Feedback</label>
                        <textarea name="instructor_feedback" rows="3" placeholder="Enter your feedback about the session..." 
                                  style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 6px; resize: vertical;">${booking.instructor_feedback || ''}</textarea>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 6px;">Student Feedback</label>
                        <textarea name="student_feedback" rows="3" placeholder="Enter student's feedback about the session..." 
                                  style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 6px; resize: vertical;">${booking.student_feedback || ''}</textarea>
                    </div>
                </div>
                
                <!-- Actions -->
                <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #dee2e6;">
                    <button type="button" onclick="closeLessonModal()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" class="btn-save-lesson" style="background: {{ $primaryColor }}; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                        Save Lesson Report
                    </button>
                </div>
            </form>
        `;
    }
    
    function formatTime(timeString) {
        if (!timeString) return '';
        const date = new Date(`2000-01-01 ${timeString}`);
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }
    
    function saveLessonDetails(event, bookingId) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        
        const skills = [];
        formData.getAll('skills_practiced[]').forEach(skill => skills.push(skill));
        
        const data = {
            attendance_status: formData.get('attendance_status'),
            session_status: formData.get('session_status'),
            session_grade: formData.get('session_grade') || null,
            instructor_feedback: formData.get('instructor_feedback'),
            student_feedback: formData.get('student_feedback'),
            skills_practiced: skills
        };
        
        const submitBtn = form.querySelector('.btn-save-lesson');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Saving...';
        submitBtn.disabled = true;
        
        fetch(`/{{ $school->slug }}/instructor/lessons/${bookingId}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Lesson details saved successfully!');
                closeLessonModal();
                window.location.reload();
            } else {
                alert('Error: ' + (result.message || 'Failed to save lesson details'));
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving. Please try again.');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }
    
    // Close modals on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDayModal();
            closeRemovalRequestModal();
            closeLessonModal();
            if (document.getElementById('mobileSidebarPopup').style.display !== 'none') {
                toggleMobileSidebar();
            }
        }
    });
</script>

@endsection
