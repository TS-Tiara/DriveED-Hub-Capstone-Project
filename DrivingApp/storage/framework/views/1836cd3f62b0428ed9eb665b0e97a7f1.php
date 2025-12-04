<?php $__env->startSection('title', 'My Schedule'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $studentId = Auth::guard('student')->id();
    
    $primaryColor = $settings?->primary_color ?? '#0d6efd';
    $secondaryColor = $settings?->secondary_color ?? '#6c757d';
    $borderRadius = $settings?->border_radius ?? 8;
    
    // Get student's enrolled course IDs (approved enrollment requests)
    $student = Auth::guard('student')->user();
    $enrolledCourseIds = App\Models\EnrollmentRequest::where('learner_id', $studentId)
        ->where('status', 'approved')
        ->pluck('course_id')
        ->toArray();
    
    // Get all bookings (both past and future - JS will filter)
    $allBookings = App\Models\Booking::where('student_id', $studentId)
        ->with(['course', 'instructor', 'timeSlot'])
        ->orderBy('booking_date')
        ->get();
    
    // Separate confirmed/scheduled bookings from cancelled ones
    $confirmedBookings = $allBookings->whereIn('status', ['confirmed', 'scheduled', 'completed']);
    $cancelledBookings = $allBookings->where('status', 'cancelled');
        
    $groupedBookings = $confirmedBookings->groupBy(function($booking) {
        return \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d');
    });
    
    $groupedCancelledBookings = $cancelledBookings->groupBy(function($booking) {
        return \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d');
    });
    
    // Get enrollment requests
    $enrollmentRequests = App\Models\EnrollmentRequest::where('learner_id', $studentId)
        ->with(['course'])
        ->orderBy('created_at', 'desc')
        ->get();
    
    // Get all available time slots (both past and future - JS will filter)
    $availableTimeSlots = App\Models\TimeSlot::where('school_id', $school->id)
        ->where('status', 'open')
        ->with(['instructors', 'course'])
        ->orderBy('date')
        ->orderBy('start_time')
        ->get();
    
    // Today's date for comparison
    $todayDate = now()->toDateString();
        
    $groupedAvailableSchedules = $availableTimeSlots->groupBy(function($timeSlot) {
        return \Carbon\Carbon::parse($timeSlot->date)->format('Y-m-d');
    });
?>

<style>
    .schedule-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .schedule-header {
        margin-bottom: 20px;
        border-bottom: 4px solid <?php echo e($primaryColor); ?>;
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
        border: 2px solid <?php echo e($primaryColor); ?>;
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
        background: <?php echo e($primaryColor); ?>;
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
        <?php if($settings?->use_gradient_header): ?>
            background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($settings?->secondary_color ?? '#0a58ca'); ?> 100%);
        <?php else: ?>
            background: <?php echo e($primaryColor); ?>;
        <?php endif; ?>
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
    
    .schedule-date-header.cancelled-header {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    
    .schedule-date-header .icon {
        font-size: 18px;
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
    
    .booking-item {
        border-left: none;
        border-bottom: 1px solid #dee2e6;
        background: white;
        padding: 16px;
        margin: 0;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .booking-item:last-child {
        border-bottom: none;
    }
    
    .booking-icon {
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    
    .booking-details {
        flex: 1;
    }
    
    .booking-instructor {
        font-weight: 600;
        color: #000;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .course-badge {
        background: #17a2b8;
        color: white;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .booking-time {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .booking-status {
        color: #6c757d;
        font-size: 13px;
    }
    
    /* Sidebar */
    .requests-sidebar {
        align-self: start;
        display: flex;
        flex-direction: column;
        gap: 20px;
        position: sticky;
        top: 20px;
        margin-top: 68px;
    }
    
    .sidebar-title {
        font-size: 1.5rem;
        font-weight: 400;
        margin: 0 0 20px 0;
        color: #000;
    }
    
    .request-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
    }
    
    .request-card:last-child {
        margin-bottom: 0;
    }
    
    .request-date {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: #000;
    }
    
    .request-date .icon {
        font-size: 18px;
        color: #6c757d;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-badge.approved {
        background: #d4edda;
        color: #155724;
    }
    
    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .request-time {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 8px 0;
        color: #000;
        font-size: 14px;
    }
    
    .request-instructor {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 8px 0;
        color: #000;
        font-size: 14px;
    }
    
    .request-status {
        margin-top: 12px;
        padding: 10px;
        border-radius: 4px;
        font-size: 13px;
    }
    
    .request-status.confirmed {
        background: #d4edda;
        color: #155724;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .request-status.waiting {
        background: #fff3cd;
        color: #856404;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Available Schedules */
    .available-schedule-card {
        border-left: none;
        border-bottom: 1px solid #dee2e6;
        background: white;
        padding: 16px;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .available-schedule-card:last-child {
        border-bottom: none;
    }
    
    .available-icon {
        display: none;
    }
    
    .available-details {
        flex: 1;
        width: 100%;
    }
    
    .book-now-btn {
        background: <?php echo e($primaryColor); ?>;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
        margin-top: 8px;
    }
    
    .book-now-btn:hover {
        background: #0b5ed7;
        transform: translateY(-1px);
    }
    
    .instructor-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-size: 14px;
        margin-top: 8px;
        background: white;
        cursor: pointer;
    }
    
    .instructor-select:focus {
        outline: none;
        border-color: <?php echo e($primaryColor); ?>;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }
    
    .select-label {
        font-size: 13px;
        color: #6c757d;
        margin-top: 8px;
        margin-bottom: 4px;
        font-weight: 500;
    }
    
    /* Collapsible Sidebar */
    .sidebar-collapsible {
        margin-bottom: 16px;
    }
    
    .sidebar-section-header {
        background: #f8f9fa;
        padding: 12px 16px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s;
        border: 1px solid #dee2e6;
    }
    
    .sidebar-section-header:hover {
        background: #e9ecef;
    }
    
    .sidebar-section-title {
        font-weight: 600;
        color: #000;
        font-size: 1rem;
        margin: 0;
    }
    
    .sidebar-toggle-icon {
        transition: transform 0.3s;
        font-size: 14px;
    }
    
    .sidebar-section-header.collapsed .sidebar-toggle-icon {
        transform: rotate(-90deg);
    }
    
    .sidebar-section-content {
        max-height: 600px;
        overflow: hidden;
        transition: max-height 0.3s ease;
        margin-top: 8px;
    }
    
    .sidebar-section-content.collapsed {
        max-height: 0 !important;
        margin-top: 0;
    }
    
    .mini-booking-card {
        background: white;
        border: 1px solid #dee2e6;
        border-left: 3px solid <?php echo e($primaryColor); ?>;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 10px;
        font-size: 13px;
    }
    
    .mini-booking-date {
        font-weight: 600;
        color: #000;
        margin-bottom: 6px;
        font-size: 14px;
    }
    
    .mini-booking-info {
        color: #6c757d;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .calendar-placeholder {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 40px 20px;
        text-align: center;
        color: #6c757d;
    }
    
    .sidebar-section {
        margin-bottom: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 16px;
    }
    
    .sidebar-section:last-child {
        margin-bottom: 0;
    }
    
    .sidebar-section-title-simple {
        font-size: 1.1rem;
        font-weight: 600;
        color: #000;
        margin: 0 0 12px 0;
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
        
        .requests-sidebar {
            width: 100%;
            max-width: none;
        }
        
        .sidebar-section {
            margin-bottom: 15px;
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
            padding: 6px 12px;
            font-size: 0.75rem;
        }
        
        .schedule-main,
        .requests-sidebar {
            padding: 8px;
        }
        
        .schedule-date-header {
            padding: 10px 12px;
            font-size: 0.75rem;
        }
        
        .schedule-date-header .date-text {
            font-size: 0.75rem;
        }
        
        .toggle-icon {
            font-size: 0.9rem;
        }
        
        .booking-item {
            padding: 10px;
        }
        
        .booking-card {
            padding: 8px;
            margin-bottom: 6px;
        }
        
        .booking-info p {
            font-size: 0.7rem;
            margin: 2px 0;
            line-height: 1.2;
        }
        
        .booking-instructor {
            font-size: 0.75rem;
            margin-bottom: 4px;
        }
        
        .booking-time {
            font-size: 0.7rem;
            margin-left: -6px;
        }
        
        .booking-status {
            font-size: 0.65rem;
            margin-top: 4px;
        }
        
        .course-badge {
            font-size: 0.6rem;
            padding: 2px 6px;
        }
        
        .mini-booking-card {
            padding: 6px 8px;
            margin-bottom: 6px;
        }
        
        .mini-booking-card .mini-date {
            font-size: 0.65rem;
        }
        
        .mini-booking-card .mini-course {
            font-size: 0.7rem;
        }
        
        .collapse-btn {
            padding: 6px 12px;
            font-size: 0.75rem;
            margin-bottom: 8px;
        }
        
        .available-schedule-card {
            padding: 12px;
            margin: 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .available-schedule-card:last-child {
            border-bottom: none;
        }
        
        .course-name {
            font-size: 0.75rem;
        }
        
        .time-info {
            font-size: 0.7rem;
        }
        
        .booking-details .booking-status {
            font-size: 0.65rem;
        }
        
        .instructor-select {
            font-size: 0.7rem;
            padding: 6px 8px;
        }
        
        .book-now-btn {
            padding: 8px 12px;
            font-size: 0.75rem;
        }
        
        .sidebar-section {
            padding: 8px;
            margin-bottom: 8px;
        }
        
        .sidebar-section h3 {
            font-size: 0.85rem;
            margin-bottom: 8px;
        }
    }
    
    /* Small Mobile Responsiveness */
    @media (max-width: 480px) {
        .schedule-container {
            padding: 8px;
        }
        
        .schedule-header {
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .schedule-header h1 {
            font-size: 1.1rem;
            margin: 0;
        }
        
        .main-toggle {
            gap: 6px;
        }
        
        .main-toggle-btn {
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .schedule-main,
        .requests-sidebar {
            padding: 8px;
            border-radius: 6px;
        }
        
        .schedule-date-header {
            padding: 8px 10px;
            font-size: 0.8rem;
        }
        
        .schedule-date-header .icon {
            font-size: 0.9rem;
            display: none; /* Hide emoji on small screens */
        }
        
        .toggle-icon {
            font-size: 0.7rem;
        }
        
        .booking-card {
            padding: 10px;
            margin-bottom: 8px;
        }
        
        .booking-info p {
            font-size: 0.75rem;
            margin: 3px 0;
            line-height: 1.3;
        }
        
        .booking-time {
            font-size: 0.75rem;
            padding: 4px 8px;
            margin-top: 6px;
        }
        
        .mini-booking-card {
            padding: 6px 8px;
            margin-bottom: 6px;
        }
        
        .mini-date {
            font-size: 0.7rem;
        }
        
        .mini-course {
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-badge {
            font-size: 0.65rem;
            padding: 2px 6px;
        }
        
        .sidebar-section {
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .sidebar-section h3 {
            font-size: 0.9rem;
            margin: 0 0 8px 0;
        }
        
        .collapse-btn {
            padding: 6px 12px;
            font-size: 0.75rem;
            margin-bottom: 10px;
        }
        
        .available-schedule-card {
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .course-name {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        
        .time-info,
        .spots-info {
            font-size: 0.75rem;
            margin-bottom: 4px;
        }
        
        .select-label {
            font-size: 0.75rem;
            margin-bottom: 4px;
        }
        
        .instructor-select {
            font-size: 0.75rem;
            padding: 6px 8px;
            margin-bottom: 6px;
        }
        
        .book-now-btn {
            padding: 6px 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        /* Make cards more compact */
        .schedule-grid {
            gap: 10px;
        }
        
        .sidebar-section {
            padding: 8px;
            margin-bottom: 8px;
        }
        
        /* Hide "Manual" badge on very small screens */
        .available-schedule-card .status-badge {
            display: none;
        }
        
        /* Simplify available schedule cards */
        .available-schedule-card {
            padding: 8px;
        }
        
        /* Show mobile queue button */
        .mobile-queue-btn {
            display: inline-block !important;
        }
        
        /* Hide sidebar on mobile */
        .requests-sidebar {
            display: none;
        }
    }
    
    /* Queue Popup Styles */
    .queue-popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        overflow-y: auto;
    }
    
    .queue-popup.active {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .queue-popup-content {
        background: white;
        border-radius: 12px;
        max-width: 500px;
        width: 100%;
        max-height: 80vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .queue-popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .queue-popup-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .queue-close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6c757d;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .queue-close-btn:hover {
        color: #000;
    }
    
    .queue-popup-body {
        padding: 16px 20px;
        overflow-y: auto;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .queue-popup-body::-webkit-scrollbar {
        display: none;
    }
</style>

<div class="schedule-container">
    <div class="schedule-header">
        <h1>Schedule</h1>
        <div class="main-toggle">
            <button class="main-toggle-btn active" onclick="switchMainView('my-schedule')">My Schedule</button>
            <button class="main-toggle-btn" onclick="switchMainView('available-schedules')">Available Schedules</button>
        </div>
    </div>
    
    <?php if(session('success')): ?>
        <div id="success-alert" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; position: relative; display: flex; align-items: center; justify-content: space-between;">
            <span><?php echo e(session('success')); ?></span>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #155724; font-size: 1.2rem; cursor: pointer; padding: 0 4px; line-height: 1;">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if($errors->any()): ?>
        <div id="error-alert" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; position: relative;">
            <button onclick="this.parentElement.remove()" style="position: absolute; top: 8px; right: 8px; background: none; border: none; color: #721c24; font-size: 1.2rem; cursor: pointer; padding: 0 4px; line-height: 1;">&times;</button>
            <ul style="margin: 0; padding-left: 20px;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <!-- My Schedule View -->
    <div id="my-schedule-view" class="main-view-section active">
    <div class="schedule-grid">
        <!-- Left: Schedule List -->
        <div class="schedule-main">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; margin-top: 20px; flex-wrap: wrap; gap: 12px;">
                <h3 style="margin: 0; color: #000; font-size: 1.5rem; font-weight: 600;">My Confirmed Schedule</h3>
                <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: #6c757d;">
                        <input type="checkbox" id="collapse-all-my" onchange="toggleCollapseAllMySchedule(this)" style="cursor: pointer;">
                        <span>Collapse All</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: #6c757d;">
                        <input type="checkbox" id="show-past-my" onchange="toggleShowPastMySchedule(this)" style="cursor: pointer;">
                        <span>Show Past Schedules</span>
                    </label>
                    <button class="collapse-btn mobile-queue-btn" onclick="toggleQueuePopup()" style="display: none; padding: 6px 12px; font-size: 14px;">Booked Schedule</button>
                </div>
            </div>
            
            <?php $__empty_1 = true; $__currentLoopData = $groupedBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $dateBookings): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $isPast = \Carbon\Carbon::parse($date)->lt(now()->startOfDay());
                ?>
                <div class="schedule-item" data-is-past="<?php echo e($isPast ? 'true' : 'false'); ?>" style="<?php echo e($isPast ? 'display: none;' : ''); ?>">
                    <div class="schedule-date-header" onclick="toggleDate(this)">
                        <span class="date-text"><?php echo e(\Carbon\Carbon::parse($date)->format('l, F d, Y')); ?></span>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="schedule-bookings" style="max-height: 800px;">
                        <?php $__currentLoopData = $dateBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="booking-item">
                                <div style="width: 4px; background: <?php echo e($booking->status === 'completed' ? '#28a745' : $secondaryColor); ?>; border-radius: 2px; flex-shrink: 0; align-self: stretch;"></div>
                                <div class="booking-details">
                                    <div class="booking-instructor">
                                        <?php echo e($booking->instructor->name ?? 'Instructor\'s Name'); ?>

                                        <?php if($booking->course): ?>
                                            <span class="course-badge"><?php echo e($booking->course->title ?? 'Course'); ?></span>
                                            <span class="course-badge"><?php echo e($booking->course->vehicle_type ?? 'Manual'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="booking-time">
                                        <?php if($booking->timeSlot): ?>
                                            <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->end_time)->format('g:i A')); ?>

                                        <?php elseif($booking->scheduled_at): ?>
                                            <?php echo e(\Carbon\Carbon::parse($booking->scheduled_at)->format('g:i A')); ?>

                                        <?php else: ?>
                                            Time TBD
                                        <?php endif; ?>
                                    </div>
                                    <div class="booking-status">
                                        <?php if($booking->status === 'completed'): ?>
                                            <span style="background: #28a745; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                                ✓ Completed
                                            </span>
                                        <?php else: ?>
                                            Status: <?php echo e(ucfirst($booking->status ?? 'Scheduled')); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p style="text-align: center; color: #6c757d; padding: 40px;">No confirmed schedule.</p>
            <?php endif; ?>
            
            <!-- Cancelled Bookings Section -->
            <?php if($groupedCancelledBookings->count() > 0): ?>
            <h3 style="margin: 30px 0 12px 0; color: #dc3545; font-size: 1.1rem;">Cancelled Schedules</h3>
            <?php $__currentLoopData = $groupedCancelledBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $dateCancelledBookings): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isPast = \Carbon\Carbon::parse($date)->lt(now()->startOfDay());
                ?>
                <div class="schedule-item" data-is-past="<?php echo e($isPast ? 'true' : 'false'); ?>" style="<?php echo e($isPast ? 'display: none;' : ''); ?>">
                    <div class="schedule-date-header cancelled-header" onclick="toggleDate(this)">
                        <span class="date-text"><?php echo e(\Carbon\Carbon::parse($date)->format('l, F d, Y')); ?></span>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="schedule-bookings" style="max-height: 800px;">
                        <?php $__currentLoopData = $dateCancelledBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="booking-item">
                                <div style="width: 4px; background: #dc3545; border-radius: 2px; flex-shrink: 0; align-self: stretch;"></div>
                                <div class="booking-details">
                                    <div class="booking-instructor">
                                        <?php echo e($booking->instructor->name ?? 'Instructor\'s Name'); ?>

                                        <?php if($booking->course): ?>
                                            <span class="course-badge"><?php echo e($booking->course->title ?? 'Course'); ?></span>
                                            <span class="course-badge"><?php echo e($booking->course->vehicle_type ?? 'Manual'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="booking-time">
                                        <?php if($booking->timeSlot): ?>
                                            <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->end_time)->format('g:i A')); ?>

                                        <?php elseif($booking->scheduled_at): ?>
                                            <?php echo e(\Carbon\Carbon::parse($booking->scheduled_at)->format('g:i A')); ?>

                                        <?php else: ?>
                                            Time TBD
                                        <?php endif; ?>
                                    </div>
                                    <div class="booking-status">
                                        <?php if($booking->cancelled_by === 'student'): ?>
                                            <span style="background: #ffc107; color: #000; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                                Cancelled by You
                                            </span>
                                        <?php elseif($booking->cancelled_by === 'instructor'): ?>
                                            <span style="background: #17a2b8; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                                Cancelled by Instructor
                                            </span>
                                        <?php elseif($booking->cancelled_by === 'admin'): ?>
                                            <span style="background: #6c757d; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                                Cancelled by School
                                            </span>
                                        <?php else: ?>
                                            <span style="background: #dc3545; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                                Cancelled
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($booking->cancellation_reason): ?>
                                        <div style="font-size: 0.8rem; color: #6c757d; margin-top: 4px; font-style: italic;">
                                            Reason: <?php echo e($booking->cancellation_reason); ?>

                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </div>
        
        <!-- Right: Requests Sidebar -->
        <div class="requests-sidebar">
            <?php if($queueEnabled && $queuedBookings->count() > 0): ?>
            <div class="sidebar-section" style="background: #fff3cd; border: 2px solid #ffc107; margin-top: 35px;">
                <h3 class="sidebar-section-title-simple" style="color: #856404;">
                    Schedule Queued (<?php echo e($queuedBookings->count()); ?>)
                </h3>
                <p style="font-size: 0.85rem; color: #856404; margin-bottom: 12px;">
                    Pending bookings will auto-confirm in <?php echo e($queueDays); ?> days
                </p>
                <?php $__currentLoopData = $queuedBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mini-booking-card" style="border-left-color: #ffc107; background: white;">
                        <div class="mini-booking-date" style="color: #856404; display: flex; justify-content: space-between; align-items: center;">
                            <span><?php echo e(\Carbon\Carbon::parse($booking->booking_date)->format('M d, Y')); ?></span>
                        </div>
                        <div class="mini-booking-info" style="font-weight: 600; color: #000;">
                            <?php echo e($booking->course->title ?? 'Course'); ?>

                            <?php if($booking->course): ?>
                                <span style="background: #17a2b8; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.65rem; margin-left: 4px;">
                                    <?php echo e($booking->course->vehicle_type ?? 'Manual'); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="mini-booking-info">
                            <?php if($booking->timeSlot): ?>
                                <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->end_time)->format('g:i A')); ?>

                            <?php elseif($booking->scheduled_at): ?>
                                <?php echo e(\Carbon\Carbon::parse($booking->scheduled_at)->format('g:i A')); ?>

                            <?php endif; ?>
                        </div>
                        <div class="mini-booking-info" style="font-size: 0.7rem; color: #6c757d;">
                            Added <?php echo e(\Carbon\Carbon::parse($booking->created_at)->diffForHumans()); ?>

                        </div>
                        <div style="display: flex; gap: 4px; margin-top: 8px;">
                            <form method="POST" action="<?php echo e(route('schools.student.bookings.confirm', [$school->slug, $booking->id])); ?>" style="flex: 1;">
                                <?php echo csrf_field(); ?>
                                <button type="submit" style="width: 100%; background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="return confirm('Confirm this booking now?')">
                                    Confirm
                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('schools.student.bookings.removeQueue', [$school->slug, $booking->id])); ?>" style="flex: 1;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" style="width: 100%; background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="return confirm('Cancel this booking?')">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
            
            <div class="sidebar-section">
                <h3 class="sidebar-section-title-simple">Upcoming This Week</h3>
                
                <?php
                    $upcomingBookings = $confirmedBookings->whereIn('status', ['scheduled', 'confirmed'])->where('booking_date', '>=', now()->toDateString())->where('booking_date', '<=', now()->addDays(7)->toDateString())->sortBy('booking_date')->take(5);
                ?>
                
                <?php $__empty_1 = true; $__currentLoopData = $upcomingBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mini-booking-card">
                        <div class="mini-booking-date">
                            <?php echo e(\Carbon\Carbon::parse($booking->booking_date)->format('M d, Y')); ?>

                        </div>
                        <div class="mini-booking-info">
                            <?php if($booking->timeSlot): ?>
                                <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A')); ?>

                            <?php else: ?>
                                Time TBD
                            <?php endif; ?>
                        </div>
                        <div class="mini-booking-info">
                            <?php echo e($booking->instructor->name ?? 'Instructor'); ?>

                        </div>
                        <?php if($booking->course): ?>
                            <div class="mini-booking-info">
                                <?php echo e($booking->course->title); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p style="text-align: center; color: #6c757d; padding: 20px; font-size: 14px;">
                        No scheduled lessons.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>
    <!-- End My Schedule View -->
    
    <!-- Available Schedules View -->
    <div id="available-schedules-view" class="main-view-section">
    <div class="schedule-grid">
        <!-- Left: Available Time Slots -->
        <div class="schedule-main">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; margin-top: 20px; flex-wrap: wrap; gap: 12px;">
                <h3 style="margin: 0; color: #000; font-size: 1.5rem; font-weight: 600;">Available Schedules</h3>
                <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: #6c757d;">
                        <input type="checkbox" id="collapse-all-available" onchange="toggleCollapseAllAvailable(this)" style="cursor: pointer;">
                        <span>Collapse All</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: #6c757d;">
                        <input type="checkbox" id="show-past-available" onchange="toggleShowPastAvailable(this)" style="cursor: pointer;">
                        <span>Show Past Schedules</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: #6c757d;">
                        <input type="checkbox" id="show-all-courses" onchange="toggleShowAllCourses()" style="cursor: pointer;">
                        <span>Show All Courses</span>
                    </label>
                    <button class="collapse-btn mobile-queue-btn" onclick="toggleQueuePopup()" style="display: none; padding: 6px 12px; font-size: 14px;">Booked Schedule</button>
                </div>
            </div>
            
            <?php $__empty_1 = true; $__currentLoopData = $groupedAvailableSchedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $dateSchedules): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $isPast = \Carbon\Carbon::parse($date)->lt(now()->startOfDay());
                    // Check if any slots are visible (enrolled) for this date
                    $hasVisibleSlots = $dateSchedules->filter(function($slot) use ($enrolledCourseIds) {
                        return empty($enrolledCourseIds) || in_array($slot->course_id, $enrolledCourseIds);
                    })->count() > 0;
                ?>
                <div class="schedule-item" data-is-past="<?php echo e($isPast ? 'true' : 'false'); ?>" data-has-visible="<?php echo e($hasVisibleSlots ? 'true' : 'false'); ?>" style="<?php echo e($isPast || !$hasVisibleSlots ? 'display: none;' : ''); ?>">
                    <div class="schedule-date-header" onclick="toggleDate(this)">
                        <span class="date-text"><?php echo e(\Carbon\Carbon::parse($date)->format('l, F d, Y')); ?></span>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="schedule-bookings" style="max-height: 800px;">
                        <?php $__currentLoopData = $dateSchedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timeSlot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isEnrolledInCourse = empty($enrolledCourseIds) || in_array($timeSlot->course_id, $enrolledCourseIds);
                                $courseName = $timeSlot->course->title ?? 'Driving Lesson';
                            ?>
                            <div class="available-schedule-card" 
                                 data-course-id="<?php echo e($timeSlot->course_id ?? ''); ?>" 
                                 data-course-name="<?php echo e($courseName); ?>"
                                 data-date="<?php echo e($date); ?>"
                                 data-start-time="<?php echo e(\Carbon\Carbon::parse($timeSlot->start_time)->format('H:i')); ?>"
                                 data-end-time="<?php echo e(\Carbon\Carbon::parse($timeSlot->end_time)->format('H:i')); ?>"
                                 data-enrolled="<?php echo e($isEnrolledInCourse ? 'true' : 'false'); ?>"
                                 style="<?php echo e(!$isEnrolledInCourse ? 'display: none;' : ''); ?>">
                                <div class="available-details">
                                    <div class="booking-time" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <?php echo e(\Carbon\Carbon::parse($timeSlot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($timeSlot->end_time)->format('g:i A')); ?>

                                        <?php if($timeSlot->course): ?>
                                            <span class="course-badge" style="background: <?php echo e($primaryColor); ?>; color: white;"><?php echo e($timeSlot->course->title ?? 'Course'); ?></span>
                                            <?php if($isEnrolledInCourse): ?>
                                                <span class="course-badge" style="background: #d4edda; color: #155724;">✓ Enrolled</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php
                                        $selectionMode = $settings?->instructor_selection_mode ?? 'auto_assign';
                                    ?>
                                    
                                    <?php if($selectionMode === 'student_chooses'): ?>
                                        <!-- Student selects instructor -->
                                        <select class="instructor-select" id="instructor-<?php echo e($timeSlot->id); ?>" onchange="updateBookButton(<?php echo e($timeSlot->id); ?>)">
                                            <option value="">Instructor's Name</option>
                                            <?php if($timeSlot->instructors->count() > 0): ?>
                                                <?php $__currentLoopData = $timeSlot->instructors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instructor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($instructor->id); ?>"><?php echo e($instructor->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                        </select>
                                        
                                        <div style="display: flex; gap: 8px; align-items: center; margin-top: 8px; flex-wrap: wrap;">
                                            <?php if($timeSlot->course): ?>
                                                <span class="course-badge"><?php echo e($timeSlot->course->vehicle_type ?? 'Manual'); ?></span>
                                            <?php endif; ?>
                                            <div class="booking-status" style="margin: 0; flex: 1;">
                                                <?php echo e($timeSlot->getAvailableSpots()); ?> spot(s) available
                                            </div>
                                            <button class="book-now-btn" id="book-btn-<?php echo e($timeSlot->id); ?>" onclick="bookTimeSlot(<?php echo e($timeSlot->id); ?>)" disabled style="opacity: 0.5; cursor: not-allowed; margin: 0;">
                                                Book Lesson
                                            </button>
                                        </div>
                                    <?php elseif($selectionMode === 'auto_assign'): ?>
                                        <!-- System auto-assigns -->
                                        <div style="display: flex; gap: 8px; align-items: center; margin-top: 8px; flex-wrap: wrap;">
                                            <?php if($timeSlot->course): ?>
                                                <span class="course-badge"><?php echo e($timeSlot->course->vehicle_type ?? 'Manual'); ?></span>
                                            <?php endif; ?>
                                            <div class="booking-status" style="margin: 0; flex: 1;">
                                                <?php echo e($timeSlot->getAvailableSpots()); ?> spot(s) available
                                            </div>
                                            <button class="book-now-btn" onclick="bookTimeSlotAuto(<?php echo e($timeSlot->id); ?>)" style="margin: 0;">
                                                Book Now
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <!-- Admin assigns (wait for admin) -->
                                        <div style="display: flex; gap: 8px; align-items: center; margin-top: 8px; flex-wrap: wrap;">
                                            <?php if($timeSlot->course): ?>
                                                <span class="course-badge"><?php echo e($timeSlot->course->vehicle_type ?? 'Manual'); ?></span>
                                            <?php endif; ?>
                                            <div class="booking-status" style="margin: 0; flex: 1;">
                                                <?php echo e($timeSlot->getAvailableSpots()); ?> spot(s) available
                                            </div>
                                            <button class="book-now-btn" onclick="bookTimeSlotAdmin(<?php echo e($timeSlot->id); ?>)" style="margin: 0;">
                                                Request Booking
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p style="text-align: center; color: #6c757d; padding: 40px;">No available time slots at the moment.</p>
            <?php endif; ?>
        </div>
        
        <!-- Right: My Upcoming Lessons & Calendar -->
        <div class="requests-sidebar">
            <!-- Schedule Queued Section -->
            <?php if($queueEnabled && $queuedBookings->count() > 0): ?>
            <div class="sidebar-section" style="background: #fff3cd; border: 2px solid #ffc107;">
                <h3 class="sidebar-section-title-simple" style="color: #856404;">
                    Schedule Queued (<?php echo e($queuedBookings->count()); ?>)
                </h3>
                <p style="font-size: 0.85rem; color: #856404; margin-bottom: 12px;">
                    Pending bookings will auto-confirm in <?php echo e($queueDays); ?> days
                </p>
                <?php $__currentLoopData = $queuedBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mini-booking-card" style="border-left-color: #ffc107; background: white;">
                        <div class="mini-booking-date" style="color: #856404; display: flex; justify-content: space-between; align-items: center;">
                            <span><?php echo e(\Carbon\Carbon::parse($booking->booking_date)->format('M d, Y')); ?></span>
                        </div>
                        <div class="mini-booking-info" style="font-weight: 600; color: #000;">
                            <?php echo e($booking->course->title ?? 'Course'); ?>

                            <?php if($booking->course): ?>
                                <span style="background: #17a2b8; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.65rem; margin-left: 4px;">
                                    <?php echo e($booking->course->vehicle_type ?? 'Manual'); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="mini-booking-info">
                            <?php if($booking->timeSlot): ?>
                                <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->end_time)->format('g:i A')); ?>

                            <?php elseif($booking->scheduled_at): ?>
                                <?php echo e(\Carbon\Carbon::parse($booking->scheduled_at)->format('g:i A')); ?>

                            <?php endif; ?>
                        </div>
                        <div class="mini-booking-info" style="font-size: 0.7rem; color: #6c757d;">
                            Added <?php echo e(\Carbon\Carbon::parse($booking->created_at)->diffForHumans()); ?>

                        </div>
                        <div style="display: flex; gap: 4px; margin-top: 8px;">
                            <form method="POST" action="<?php echo e(route('schools.student.bookings.confirm', [$school->slug, $booking->id])); ?>" style="flex: 1;">
                                <?php echo csrf_field(); ?>
                                <button type="submit" style="width: 100%; background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="return confirm('Confirm this booking now?')">
                                    Confirm
                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('schools.student.bookings.removeQueue', [$school->slug, $booking->id])); ?>" style="flex: 1;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" style="width: 100%; background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="return confirm('Cancel this booking?')">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
            
            <div class="sidebar-section">
                <h3 class="sidebar-section-title-simple">Upcoming This Week</h3>
                
                <?php
                    $upcomingBookings = $confirmedBookings->whereIn('status', ['scheduled', 'confirmed'])->where('booking_date', '>=', now()->toDateString())->where('booking_date', '<=', now()->addDays(7)->toDateString())->sortBy('booking_date')->take(5);
                ?>
                
                <?php $__empty_1 = true; $__currentLoopData = $upcomingBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="mini-booking-card">
                        <div class="mini-booking-date">
                            <?php echo e(\Carbon\Carbon::parse($booking->booking_date)->format('M d, Y')); ?>

                        </div>
                        <div class="mini-booking-info">
                            <?php if($booking->timeSlot): ?>
                                <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A')); ?>

                            <?php else: ?>
                                Time TBD
                            <?php endif; ?>
                        </div>
                        <div class="mini-booking-info">
                            <?php echo e($booking->instructor->name ?? 'Instructor'); ?>

                        </div>
                        <?php if($booking->course): ?>
                            <div class="mini-booking-info">
                                <?php echo e($booking->course->title); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p style="text-align: center; color: #6c757d; padding: 20px; font-size: 14px;">
                        No scheduled lessons.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>
    <!-- End Available Schedules View -->
</div>

<!-- Mobile Queue Popup -->
<div id="queuePopup" class="queue-popup" onclick="closeQueuePopupOnBackdrop(event)">
    <div class="queue-popup-content">
        <div class="queue-popup-header">
            <h3>My Schedule</h3>
            <button class="queue-close-btn" onclick="toggleQueuePopup()">&times;</button>
        </div>
        <div class="queue-popup-body">
            <?php if($queueEnabled && $queuedBookings->count() > 0): ?>
            <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                <h4 style="margin: 0 0 8px 0; color: #856404; font-size: 0.9rem;">Schedule Queued (<?php echo e($queuedBookings->count()); ?>)</h4>
                <p style="margin: 0 0 10px 0; color: #856404; font-size: 0.75rem;">
                    Auto-confirms in <?php echo e($queueDays); ?> days
                </p>
                <?php $__currentLoopData = $queuedBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="background: white; border-radius: 6px; padding: 10px; margin-bottom: 8px; border: 1px solid #ffc107;">
                        <div style="font-weight: 600; color: #000; font-size: 0.85rem; margin-bottom: 4px;">
                            <?php echo e(\Carbon\Carbon::parse($booking->booking_date)->format('M d, Y')); ?>

                        </div>
                        <div style="color: #6c757d; font-size: 0.75rem;">
                            <?php echo e($booking->course->title ?? 'Course'); ?>

                            <?php if($booking->course): ?>
                                <span style="background: #17a2b8; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.65rem; margin-left: 4px;">
                                    <?php echo e($booking->course->vehicle_type ?? 'Manual'); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                        <div style="color: #6c757d; font-size: 0.75rem; margin-top: 2px;">
                            <?php if($booking->timeSlot): ?>
                                <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->end_time)->format('g:i A')); ?>

                            <?php elseif($booking->scheduled_at): ?>
                                <?php echo e(\Carbon\Carbon::parse($booking->scheduled_at)->format('g:i A')); ?>

                            <?php endif; ?>
                        </div>
                        <div style="display: flex; gap: 4px; margin-top: 6px;">
                            <form method="POST" action="<?php echo e(route('schools.student.bookings.confirm', [$school->slug, $booking->id])); ?>" style="flex: 1;">
                                <?php echo csrf_field(); ?>
                                <button type="submit" style="width: 100%; background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="return confirm('Confirm this booking now?')">
                                    Confirm
                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('schools.student.bookings.removeQueue', [$school->slug, $booking->id])); ?>" style="flex: 1;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" style="width: 100%; background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;" onclick="return confirm('Cancel this booking?')">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
            
            <h4 style="margin: 0 0 10px 0; color: #000; font-size: 0.9rem;">Upcoming This Week</h4>
            <?php
                $upcomingBookings = $confirmedBookings
                    ->whereIn('status', ['scheduled', 'confirmed'])
                    ->where('booking_date', '>=', now()->toDateString())
                    ->where('booking_date', '<=', now()->addDays(7)->toDateString())
                    ->sortBy('booking_date')
                    ->take(10);
            ?>
            
            <?php $__empty_1 = true; $__currentLoopData = $upcomingBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="mini-booking-card">
                    <div class="mini-booking-date">
                        <?php echo e(\Carbon\Carbon::parse($booking->booking_date)->format('M d, Y')); ?>

                    </div>
                    <div class="mini-booking-info">
                        <?php if($booking->timeSlot): ?>
                            <?php echo e(\Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A')); ?>

                        <?php else: ?>
                            Time TBD
                        <?php endif; ?>
                    </div>
                    <div class="mini-booking-info">
                        <?php echo e($booking->instructor->name ?? 'Instructor'); ?>

                    </div>
                    <?php if($booking->course): ?>
                        <div class="mini-booking-info">
                            <?php echo e($booking->course->title); ?>

                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p style="text-align: center; color: #6c757d; padding: 20px; font-size: 14px;">
                    No scheduled lessons.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function switchMainView(viewName) {
        // Toggle buttons
        document.querySelectorAll('.main-toggle-btn').forEach(btn => btn.classList.remove('active'));
        
        // Toggle views
        document.querySelectorAll('.main-view-section').forEach(section => section.classList.remove('active'));
        
        if (viewName === 'my-schedule') {
            document.querySelectorAll('.main-toggle-btn')[0].classList.add('active');
            document.getElementById('my-schedule-view').classList.add('active');
        } else {
            document.querySelectorAll('.main-toggle-btn')[1].classList.add('active');
            document.getElementById('available-schedules-view').classList.add('active');
        }
    }
    
    function toggleDate(header) {
        const bookings = header.nextElementSibling;
        header.classList.toggle('collapsed');
        bookings.classList.toggle('collapsed');
    }
    
    function collapseAll() {
        const headers = document.querySelectorAll('.schedule-date-header');
        const bookings = document.querySelectorAll('.schedule-bookings');
        
        headers.forEach(header => header.classList.add('collapsed'));
        bookings.forEach(booking => booking.classList.add('collapsed'));
    }
    
    function toggleCollapseAll(button) {
        const headers = document.querySelectorAll('.schedule-date-header');
        const bookings = document.querySelectorAll('.schedule-bookings');
        const isCollapsed = button.textContent === 'Expand All';
        
        if (isCollapsed) {
            // Expand all
            headers.forEach(header => header.classList.remove('collapsed'));
            bookings.forEach(booking => booking.classList.remove('collapsed'));
            document.getElementById('collapseBtn1').textContent = 'Collapse All';
            document.getElementById('collapseBtn2').textContent = 'Collapse All';
        } else {
            // Collapse all
            headers.forEach(header => header.classList.add('collapsed'));
            bookings.forEach(booking => booking.classList.add('collapsed'));
            document.getElementById('collapseBtn1').textContent = 'Expand All';
            document.getElementById('collapseBtn2').textContent = 'Expand All';
        }
    }
    
    function toggleCollapseAllMySchedule(checkbox) {
        const headers = document.querySelectorAll('#my-schedule-view .schedule-date-header');
        const bookings = document.querySelectorAll('#my-schedule-view .schedule-bookings');
        
        if (checkbox.checked) {
            headers.forEach(header => header.classList.add('collapsed'));
            bookings.forEach(booking => booking.classList.add('collapsed'));
        } else {
            headers.forEach(header => header.classList.remove('collapsed'));
            bookings.forEach(booking => booking.classList.remove('collapsed'));
        }
    }
    
    function toggleCollapseAllAvailable(checkbox) {
        const headers = document.querySelectorAll('#available-schedules-view .schedule-date-header');
        const bookings = document.querySelectorAll('#available-schedules-view .schedule-bookings');
        
        if (checkbox.checked) {
            headers.forEach(header => header.classList.add('collapsed'));
            bookings.forEach(booking => booking.classList.add('collapsed'));
        } else {
            headers.forEach(header => header.classList.remove('collapsed'));
            bookings.forEach(booking => booking.classList.remove('collapsed'));
        }
    }
    
    function toggleShowPastSchedules(button) {
        const isShowingPast = button.textContent === 'Hide Past Schedules';
        const pastItems = document.querySelectorAll('.schedule-item[data-is-past="true"]');
        
        if (isShowingPast) {
            // Hide past schedules
            pastItems.forEach(item => item.style.display = 'none');
            document.getElementById('showPastBtn1').textContent = 'Show Past Schedules';
            document.getElementById('showPastBtn2').textContent = 'Show Past Schedules';
        } else {
            // Show past schedules
            pastItems.forEach(item => item.style.display = '');
            document.getElementById('showPastBtn1').textContent = 'Hide Past Schedules';
            document.getElementById('showPastBtn2').textContent = 'Hide Past Schedules';
        }
    }
    
    function toggleShowAllDates() {
        // Legacy function - kept for compatibility
        toggleShowPastSchedules();
    }
    
    function toggleShowPastMySchedule(checkbox) {
        const pastItems = document.querySelectorAll('#my-schedule-view .schedule-item[data-is-past="true"]');
        
        if (checkbox.checked) {
            pastItems.forEach(item => item.style.display = '');
        } else {
            pastItems.forEach(item => item.style.display = 'none');
        }
    }
    
    function toggleShowPastAvailable(checkbox) {
        const pastItems = document.querySelectorAll('#available-schedules-view .schedule-item[data-is-past="true"]');
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
    
    function toggleShowAllCourses() {
        const checkbox = document.getElementById('show-all-courses');
        const showAll = checkbox.checked;
        const nonEnrolledSlots = document.querySelectorAll('.available-schedule-card[data-enrolled="false"]');
        
        nonEnrolledSlots.forEach(slot => {
            slot.style.display = showAll ? '' : 'none';
        });
        
        // Show/hide date groups based on whether they have visible slots
        document.querySelectorAll('#available-schedules-view .schedule-item').forEach(dateGroup => {
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
                // When filtering courses, check if any enrolled slots are visible
                const hasVisibleSlots = dateGroup.getAttribute('data-has-visible') === 'true';
                dateGroup.style.display = hasVisibleSlots ? '' : 'none';
            }
        });
    }
    
    function toggleQueuePopup() {
        const popup = document.getElementById('queuePopup');
        popup.classList.toggle('active');
        if (popup.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
    
    function closeQueuePopupOnBackdrop(event) {
        if (event.target.id === 'queuePopup') {
            toggleQueuePopup();
        }
    }
    
    function bookTimeSlot(timeSlotId) {
        const select = document.getElementById('instructor-' + timeSlotId);
        const selectedValue = select.value;
        
        if (!selectedValue) {
            alert('Please select an instructor option');
            return;
        }
        
        // Get time slot data from data attributes
        const timeSlotCard = select.closest('.available-schedule-card');
        const courseId = timeSlotCard.getAttribute('data-course-id');
        const courseName = timeSlotCard.getAttribute('data-course-name');
        const date = timeSlotCard.getAttribute('data-date');
        const startTime = timeSlotCard.getAttribute('data-start-time');
        const endTime = timeSlotCard.getAttribute('data-end-time');
        const instructorName = select.options[select.selectedIndex].text;
        
        openBookingModal(timeSlotId, courseId, courseName, selectedValue, instructorName, date, startTime, endTime);
    }
    
    function bookTimeSlotAuto(timeSlotId) {
        const button = event.target;
        const timeSlotCard = button.closest('.available-schedule-card');
        const courseId = timeSlotCard.getAttribute('data-course-id');
        const courseName = timeSlotCard.getAttribute('data-course-name');
        const date = timeSlotCard.getAttribute('data-date');
        const startTime = timeSlotCard.getAttribute('data-start-time');
        const endTime = timeSlotCard.getAttribute('data-end-time');
        
        openBookingModal(timeSlotId, courseId, courseName, null, 'Auto-assigned by system', date, startTime, endTime);
    }
    
    function bookTimeSlotAdmin(timeSlotId) {
        const button = event.target;
        const timeSlotCard = button.closest('.available-schedule-card');
        const courseId = timeSlotCard.getAttribute('data-course-id');
        const courseName = timeSlotCard.getAttribute('data-course-name');
        const date = timeSlotCard.getAttribute('data-date');
        const startTime = timeSlotCard.getAttribute('data-start-time');
        const endTime = timeSlotCard.getAttribute('data-end-time');
        
        openBookingModal(timeSlotId, courseId, courseName, null, 'Will be assigned by admin', date, startTime, endTime);
    }
    
    function updateBookButton(timeSlotId) {
        const select = document.getElementById('instructor-' + timeSlotId);
        const button = document.getElementById('book-btn-' + timeSlotId);
        
        if (select.value) {
            button.disabled = false;
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
        } else {
            button.disabled = true;
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
        }
    }
</script>

<!-- Booking Modal -->
<div id="bookingModal" class="booking-modal" style="display: none;">
    <div class="booking-modal-overlay" onclick="closeBookingModal()"></div>
    <div class="booking-modal-content">
        <div class="booking-modal-header">
            <h3>Book a Lesson</h3>
            <button type="button" class="modal-close-btn" onclick="closeBookingModal()">&times;</button>
        </div>
        <div class="booking-modal-body">
            <form id="bookingForm" method="POST" action="<?php echo e(route('schools.student.bookings.store', $school->slug)); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="student_id" value="<?php echo e($studentId); ?>">
                <input type="hidden" name="time_slot_id" id="modal_time_slot_id">
                <input type="hidden" name="course_id" id="modal_course_id">
                <input type="hidden" name="instructor_id" id="modal_instructor_id">

                <div class="form-group">
                    <label class="form-label">Course</label>
                    <input type="text" class="form-control" id="modal_course_name" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Instructor</label>
                    <input type="text" class="form-control" id="modal_instructor_name" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Date & Time</label>
                    <input type="text" class="form-control" id="modal_datetime" readonly>
                    <input type="hidden" id="scheduled_at" name="scheduled_at">
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Additional Notes (Optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                              placeholder="Any special requests or notes..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeBookingModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Add to Queue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.booking-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.booking-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.booking-modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.booking-modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: <?php echo e($primaryColor); ?>;
    color: white;
}

.booking-modal-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1.2;
}

.modal-close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 2rem;
    cursor: pointer;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background 0.2s;
    line-height: 1;
    padding: 0;
    flex-shrink: 0;
}

.modal-close-btn:hover {
    background: rgba(255, 255, 255, 0.1);
}

.booking-modal-body {
    padding: 24px;
    max-height: calc(90vh - 80px);
    overflow-y: auto;
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.booking-modal-body::-webkit-scrollbar {
    display: none;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
    font-size: 1rem;
    line-height: 1.5;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.2s;
    line-height: 1.5;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: <?php echo e($primaryColor); ?>;
    box-shadow: 0 0 0 3px <?php echo e($primaryColor); ?>20;
}

.form-control:read-only {
    background: #f9fafb;
    cursor: not-allowed;
}

.required {
    color: #ef4444;
}

.modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.modal-actions .btn {
    flex: 1;
    padding: 12px 20px;
    font-size: 1rem;
    font-weight: 500;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    line-height: 1.5;
}

.btn-primary {
    background: <?php echo e($primaryColor); ?>;
    color: white;
}

.btn-primary:hover {
    background: <?php echo e($primaryColor); ?>dd;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px <?php echo e($primaryColor); ?>40;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

@media (max-width: 768px) {
    .booking-modal-content {
        width: 90%;
        max-width: 400px;
        max-height: 85vh;
    }
    
    .booking-modal-header {
        padding: 10px 12px;
    }
    
    .booking-modal-header h3 {
        font-size: 0.95rem;
        line-height: 1.3;
    }
    
    .modal-close-btn {
        width: 24px;
        height: 24px;
        font-size: 1.3rem;
    }
    
    .booking-modal-body {
        padding: 12px;
        max-height: calc(85vh - 60px);
    }
    
    .form-group {
        margin-bottom: 10px;
    }
    
    .form-label {
        font-size: 0.75rem;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    
    .form-control {
        padding: 6px 8px;
        font-size: 0.8rem;
        line-height: 1.4;
    }
    
    textarea.form-control {
        padding: 6px 8px;
        font-size: 0.8rem;
        min-height: 50px;
        line-height: 1.4;
    }
    
    .modal-actions {
        margin-top: 12px;
        padding-top: 10px;
        gap: 6px;
        flex-direction: column;
    }
    
    .modal-actions .btn {
        padding: 8px 12px;
        font-size: 0.8rem;
        width: 100%;
        line-height: 1.4;
    }
    
    .modal-actions .btn i {
        font-size: 0.75rem;
    }
}

@media (max-width: 480px) {
    .booking-modal-content {
        width: 95%;
        max-width: 340px;
        max-height: 90vh;
        border-radius: 8px;
    }
    
    .booking-modal-header {
        padding: 8px 10px;
    }
    
    .booking-modal-header h3 {
        font-size: 0.85rem;
        line-height: 1.2;
    }
    
    .modal-close-btn {
        width: 22px;
        height: 22px;
        font-size: 1.2rem;
    }
    
    .booking-modal-body {
        padding: 10px;
        max-height: calc(90vh - 50px);
    }
    
    .form-group {
        margin-bottom: 8px;
    }
    
    .form-label {
        font-size: 0.7rem;
        margin-bottom: 3px;
        line-height: 1.2;
    }
    
    .form-control {
        padding: 5px 7px;
        font-size: 0.75rem;
        line-height: 1.3;
    }
    
    textarea.form-control {
        padding: 5px 7px;
        font-size: 0.75rem;
        min-height: 45px;
        line-height: 1.3;
    }
    
    .modal-actions {
        margin-top: 10px;
        padding-top: 8px;
        gap: 5px;
    }
    
    .modal-actions .btn {
        padding: 7px 10px;
        font-size: 0.75rem;
        line-height: 1.3;
    }
    
    .modal-actions .btn i {
        font-size: 0.7rem;
    }
}
</style>

<script>
function openBookingModal(timeSlotId, courseId, courseName, instructorId, instructorName, date, startTime, endTime) {
    // Set hidden fields
    document.getElementById('modal_time_slot_id').value = timeSlotId;
    document.getElementById('modal_course_id').value = courseId;
    document.getElementById('modal_instructor_id').value = instructorId || '';
    
    // Set display fields
    document.getElementById('modal_course_name').value = courseName;
    document.getElementById('modal_instructor_name').value = instructorName || 'To be assigned';
    document.getElementById('modal_datetime').value = `${date} at ${startTime} - ${endTime}`;
    
    // Set scheduled_at field
    const scheduledDateTime = `${date}T${startTime}`;
    document.getElementById('scheduled_at').value = scheduledDateTime;
    document.getElementById('scheduled_at').min = scheduledDateTime;
    
    // Show modal
    document.getElementById('bookingModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('bookingForm').reset();
}

// Handle form submission
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeBookingModal();
            showNotification(data.message || 'Booking added to queue successfully!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message || 'Error creating booking. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Add to Queue';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Add to Queue';
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('bookingModal').style.display === 'flex') {
        closeBookingModal();
    }
    if (e.key === 'Escape' && document.getElementById('confirmModal')) {
        closeConfirmModal();
    }
});

// Auto-close alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const successAlert = document.getElementById('success-alert');
    const errorAlert = document.getElementById('error-alert');
    
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.opacity = '0';
            successAlert.style.transition = 'opacity 0.5s';
            setTimeout(() => successAlert.remove(), 500);
        }, 5000);
    }
    
    if (errorAlert) {
        setTimeout(() => {
            errorAlert.style.opacity = '0';
            errorAlert.style.transition = 'opacity 0.5s';
            setTimeout(() => errorAlert.remove(), 500);
        }, 5000);
    }
});

// Custom notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const bgColors = {
        'success': '#d4edda',
        'error': '#f8d7da',
        'info': '#d1ecf1',
        'warning': '#fff3cd'
    };
    const textColors = {
        'success': '#155724',
        'error': '#721c24',
        'info': '#0c5460',
        'warning': '#856404'
    };
    const borderColors = {
        'success': '#c3e6cb',
        'error': '#f5c6cb',
        'info': '#bee5eb',
        'warning': '#ffeeba'
    };
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${bgColors[type] || bgColors['info']};
        color: ${textColors[type] || textColors['info']};
        border: 1px solid ${borderColors[type] || borderColors['info']};
        padding: 12px 16px;
        border-radius: 6px;
        z-index: 10000;
        max-width: 400px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        animation: slideIn 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: ${textColors[type] || textColors['info']}; font-size: 1.2rem; cursor: pointer; padding: 0 4px; margin-left: 12px; line-height: 1;">&times;</button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.5s';
        setTimeout(() => notification.remove(), 500);
    }, 5000);
}

// Custom confirmation modal
let confirmCallback = null;

function showConfirmDialog(message, onConfirm, onCancel = null) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('confirmModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'confirmModal';
        modal.style.cssText = `
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10001;
            align-items: center;
            justify-content: center;
        `;
        
        modal.innerHTML = `
            <div style="background: white; padding: 24px; border-radius: 8px; max-width: 400px; width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #333;">Confirm Action</h3>
                <p id="confirmMessage" style="margin: 0 0 20px 0; color: #666;"></p>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button onclick="closeConfirmModal(false)" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">Cancel</button>
                    <button onclick="closeConfirmModal(true)" style="padding: 8px 16px; border: none; background: #0d6efd; color: white; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">Confirm</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }
    
    document.getElementById('confirmMessage').textContent = message;
    modal.style.display = 'flex';
    confirmCallback = { onConfirm, onCancel };
}

function closeConfirmModal(confirmed = false) {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.style.display = 'none';
        if (confirmed && confirmCallback && confirmCallback.onConfirm) {
            confirmCallback.onConfirm();
        } else if (!confirmed && confirmCallback && confirmCallback.onCancel) {
            confirmCallback.onCancel();
        }
        confirmCallback = null;
    }
}
</script>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\student\schedule.blade.php ENDPATH**/ ?>