

<?php $__env->startSection('title', 'My Schedule'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#0d6efd';
    $secondaryColor = $settings?->secondary_color ?? '#6c757d';
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
        font-size: 1.8rem;
        font-weight: 600;
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
        background: <?php echo e($primaryColor); ?>;
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
    .slot-indicator.available { background: <?php echo e($primaryColor); ?>; }
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
        background: <?php echo e($primaryColor); ?>;
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
        border-bottom: 2px solid <?php echo e($primaryColor); ?>;
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
        border-left: 3px solid <?php echo e($primaryColor); ?>;
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
        background: <?php echo e($primaryColor); ?>;
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
        .schedule-container { padding: 10px; }
        .schedule-header h1 { font-size: 1.3rem; }
        .main-toggle-btn { padding: 10px 16px; font-size: 13px; }
        .slot-item { padding: 12px; }
        .schedule-sidebar { display: none; }
        .mobile-sidebar-btn { display: inline-block !important; }
    }
    
    .mobile-sidebar-btn { display: none; }
</style>

<div class="schedule-container">
    <!-- Header -->
    <div class="schedule-header">
        <h1>My Schedule</h1>
        <div class="main-toggle">
            <button type="button" class="main-toggle-btn active" onclick="switchMainView('my-slots')">My Slots</button>
            <button type="button" class="main-toggle-btn" onclick="switchMainView('available')">Available Slots</button>
        </div>
    </div>
    
    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <span><?php echo e(session('success')); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        <div class="alert alert-error">
            <span><?php echo e(session('error')); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
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
                
                <?php $__empty_1 = true; $__currentLoopData = $groupedMySlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $dateSlots): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $isPast = $date < $todayDate; ?>
                    <div class="schedule-item" data-is-past="<?php echo e($isPast ? 'true' : 'false'); ?>" style="<?php echo e($isPast ? 'display: none;' : ''); ?>">
                        <div class="schedule-date-header" onclick="toggleDate(this)">
                            <span class="date-text"><?php echo e(\Carbon\Carbon::parse($date)->format('l, F d, Y')); ?></span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="schedule-slots">
                            <?php $__currentLoopData = $dateSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $instructor = $slot->instructors->firstWhere('id', $instructorId);
                                    $isAdminAssigned = $instructor && $instructor->pivot->assignment_type === 'admin_assigned';
                                    $hasPendingRequest = in_array($slot->id, $pendingRemovalRequests);
                                    $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                                    $daysUntilSlot = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($slot->date)->startOfDay(), false);
                                    $canRequestRemoval = $daysUntilSlot >= $minimumNoticeDays;
                                ?>
                                <div class="slot-item">
                                    <div class="slot-indicator <?php echo e($isAdminAssigned ? 'admin-assigned' : 'my-slot'); ?>"></div>
                                    <div class="slot-details">
                                        <div class="slot-time">
                                            <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                                            <?php if($hasPendingRequest): ?>
                                                <span class="slot-badge badge-pending">Removal Requested</span>
                                            <?php elseif($isAdminAssigned): ?>
                                                <span class="slot-badge badge-admin">Admin Assigned</span>
                                            <?php else: ?>
                                                <span class="slot-badge badge-my-slot">My Slot</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="slot-info">
                                            <?php echo e($slot->course->title ?? 'General'); ?> • <?php echo e($slot->instructors->count()); ?>/<?php echo e($slot->max_instructors ?? 1); ?> instructors
                                            <?php if($slotBookings->count() > 0): ?>
                                                • <?php echo e($slotBookings->count()); ?> student(s) booked
                                            <?php endif; ?>
                                        </div>
                                        <?php if($slot->notes): ?>
                                            <div class="slot-info"><?php echo e($slot->notes); ?></div>
                                        <?php endif; ?>
                                        
                                        <?php if(!$hasPendingRequest): ?>
                                        <div class="slot-actions">
                                            <?php if($isAdminAssigned): ?>
                                                <button type="button" class="btn-request" onclick="showRemovalModal(<?php echo e($slot->id); ?>)" <?php echo e(!$canRequestRemoval ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''); ?>>
                                                    Request Removal
                                                </button>
                                                <?php if(!$canRequestRemoval): ?>
                                                    <span style="font-size: 11px; color: #dc3545;">(Min <?php echo e($minimumNoticeDays); ?> days notice)</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button type="button" class="btn-leave" onclick="leaveSlot(<?php echo e($slot->id); ?>, this)">Leave Slot</button>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="empty-state">
                        <p>No slots selected yet. Go to "Available Slots" to select time slots.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="schedule-sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Today's Lessons</h3>
                    <?php if($todaySlots->isNotEmpty()): ?>
                        <?php $__currentLoopData = $todaySlots->sortBy('start_time'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                            ?>
                            <div class="today-lesson-card">
                                <div class="lesson-time">
                                    <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                                </div>
                                <?php if($slotBookings->isNotEmpty()): ?>
                                    <?php $__currentLoopData = $slotBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="student-item">
                                            <div class="student-name"><?php echo e($booking->student->name ?? 'Student'); ?></div>
                                            <div class="student-course"><?php echo e($booking->course->title ?? 'Course'); ?></div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <p style="color: #6c757d; font-size: 13px; margin: 0;">No students booked</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div class="no-lessons">No lessons scheduled for today</div>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Upcoming This Week</h3>
                    <?php $__empty_1 = true; $__currentLoopData = $upcomingSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $instructor = $slot->instructors->firstWhere('id', $instructorId);
                            $isAdmin = $instructor && $instructor->pivot->assignment_type === 'admin_assigned';
                        ?>
                        <div class="mini-schedule-card" style="border-left-color: <?php echo e($isAdmin ? '#ff9800' : '#28a745'); ?>;">
                            <div class="mini-schedule-date"><?php echo e(\Carbon\Carbon::parse($slot->date)->format('D, M d')); ?></div>
                            <div class="mini-schedule-info">
                                <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="no-lessons">No upcoming slots</div>
                    <?php endif; ?>
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
                
                <?php $__empty_1 = true; $__currentLoopData = $groupedAvailableSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $dateSlots): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php 
                        $isPast = $date < $todayDate;
                        $hasVisibleSlots = $dateSlots->filter(function($slot) use ($qualifiedCourseIds) {
                            return empty($qualifiedCourseIds) || in_array($slot->course_id, $qualifiedCourseIds);
                        })->count() > 0;
                    ?>
                    <div class="schedule-item" data-is-past="<?php echo e($isPast ? 'true' : 'false'); ?>" data-has-visible="<?php echo e($hasVisibleSlots ? 'true' : 'false'); ?>" style="<?php echo e($isPast || !$hasVisibleSlots ? 'display: none;' : ''); ?>">
                        <div class="schedule-date-header" onclick="toggleDate(this)">
                            <span class="date-text"><?php echo e(\Carbon\Carbon::parse($date)->format('l, F d, Y')); ?></span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="schedule-slots">
                            <?php $__currentLoopData = $dateSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $isQualified = empty($qualifiedCourseIds) || in_array($slot->course_id, $qualifiedCourseIds);
                                    $spotsLeft = ($slot->max_instructors ?? 1) - $slot->instructors->count();
                                ?>
                                <div class="slot-item" data-qualified="<?php echo e($isQualified ? 'true' : 'false'); ?>" style="<?php echo e(!$isQualified ? 'display: none;' : ''); ?>">
                                    <div class="slot-indicator available"></div>
                                    <div class="slot-details">
                                        <div class="slot-time">
                                            <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                                            <span class="slot-badge badge-available">Available</span>
                                            <?php if($isQualified): ?>
                                                <span class="slot-badge badge-qualified"><?php echo e($slot->course->title ?? 'General'); ?> ✓</span>
                                            <?php else: ?>
                                                <span class="slot-badge badge-not-qualified"><?php echo e($slot->course->title ?? 'General'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="slot-info">
                                            <?php echo e($slot->instructors->count()); ?>/<?php echo e($slot->max_instructors ?? 1); ?> instructors • <?php echo e($spotsLeft); ?> spot(s) left
                                            <?php if(!$isQualified): ?>
                                                • <span style="color: #856404;">Not your specialty</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if($slot->notes): ?>
                                            <div class="slot-info"><?php echo e($slot->notes); ?></div>
                                        <?php endif; ?>
                                        <div class="slot-actions">
                                            <button type="button" class="btn-select" onclick="selectSlot(<?php echo e($slot->id); ?>, this)">Select Slot</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="empty-state">
                        <p>No available time slots at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar (same as My Slots) -->
            <div class="schedule-sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Today's Lessons</h3>
                    <?php if($todaySlots->isNotEmpty()): ?>
                        <?php $__currentLoopData = $todaySlots->sortBy('start_time'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                            ?>
                            <div class="today-lesson-card">
                                <div class="lesson-time">
                                    <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                                </div>
                                <?php if($slotBookings->isNotEmpty()): ?>
                                    <?php $__currentLoopData = $slotBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="student-item">
                                            <div class="student-name"><?php echo e($booking->student->name ?? 'Student'); ?></div>
                                            <div class="student-course"><?php echo e($booking->course->title ?? 'Course'); ?></div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <p style="color: #6c757d; font-size: 13px; margin: 0;">No students booked</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div class="no-lessons">No lessons scheduled for today</div>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Your Schedule</h3>
                    <?php $__empty_1 = true; $__currentLoopData = $upcomingSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mini-schedule-card">
                            <div class="mini-schedule-date"><?php echo e(\Carbon\Carbon::parse($slot->date)->format('D, M d')); ?></div>
                            <div class="mini-schedule-info">
                                <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="no-lessons">No upcoming slots</div>
                    <?php endif; ?>
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
                <?php echo csrf_field(); ?>
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
            <?php if($todaySlots->isNotEmpty()): ?>
                <?php $__currentLoopData = $todaySlots->sortBy('start_time'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $slotBookings = $slot->bookings->where('instructor_id', $instructorId)->where('status', '!=', 'cancelled');
                    ?>
                    <div class="today-lesson-card">
                        <div class="lesson-time">
                            <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                        </div>
                        <?php if($slotBookings->isNotEmpty()): ?>
                            <?php $__currentLoopData = $slotBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="student-item">
                                    <div class="student-name"><?php echo e($booking->student->name ?? 'Student'); ?></div>
                                    <div class="student-course"><?php echo e($booking->course->title ?? 'Course'); ?></div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <p style="color: #6c757d; font-size: 13px; margin: 0;">No students booked</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <div class="no-lessons">No lessons scheduled for today</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
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
        if (!confirm('Are you sure you want to leave this slot?')) {
            return;
        }
        
        btn.textContent = 'Leaving...';
        btn.disabled = true;
        
        fetch('<?php echo e(url($school->slug)); ?>/instructor/timeslots/' + slotId + '/toggle', {
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
                alert(data.message || 'Successfully left the slot!');
                window.location.reload();
            } else {
                alert(data.message || 'Failed to leave slot');
                btn.textContent = 'Leave Slot';
                btn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            btn.textContent = 'Leave Slot';
            btn.disabled = false;
        });
    }
    
    // Select slot
    function selectSlot(slotId, btn) {
        btn.textContent = 'Selecting...';
        btn.disabled = true;
        
        fetch('<?php echo e(url($school->slug)); ?>/instructor/timeslots/' + slotId + '/toggle', {
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
                alert(data.message || 'Successfully selected the slot!');
                window.location.reload();
            } else {
                alert(data.message || 'Failed to select slot');
                btn.textContent = 'Select Slot';
                btn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            btn.textContent = 'Select Slot';
            btn.disabled = false;
        });
    }
    
    // Removal request modal
    function showRemovalModal(slotId) {
        var modal = document.getElementById('removalModal');
        var form = document.getElementById('removalForm');
        form.action = '<?php echo e(url($school->slug)); ?>/instructor/timeslots/' + slotId + '/request-removal';
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
                alert(data.message || 'Removal request submitted!');
                window.location.reload();
            } else {
                alert(data.message || 'Failed to submit request');
                submitBtn.textContent = 'Submit Request';
                submitBtn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            submitBtn.textContent = 'Submit Request';
            submitBtn.disabled = false;
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\instructor\schedule-new.blade.php ENDPATH**/ ?>