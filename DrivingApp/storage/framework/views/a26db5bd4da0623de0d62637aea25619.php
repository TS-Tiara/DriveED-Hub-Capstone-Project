

<?php $__env->startSection('title', 'Schedule Management'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $instructors = $instructors ?? collect();
    $currentFilter = request('type', 'all');
    $primaryColor = $school->schoolSetting->primary_color ?? '#667eea';
    $secondaryColor = $school->schoolSetting->secondary_color ?? '#764ba2';
?>

<?php echo $__env->make('school.admin.partials.admin-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<style>
    /* Custom Button Styles (replacing Bootstrap) */
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        display: inline-block;
        text-align: center;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-success:hover {
        background: #059669;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
    }
    
    .btn-primary:hover {
        background: #2563eb;
    }
    
    .btn-secondary {
        background: #6b7280;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #4b5563;
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .btn-danger:hover {
        background: #dc2626;
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }
    
    /* Alert Styles (replacing Bootstrap alerts) */
    .alert {
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        position: relative;
    }
    
    .alert-success {
        background: #d1fae5;
        border: 1px solid #10b981;
        color: #065f46;
    }
    
    .alert-danger {
        background: #fee2e2;
        border: 1px solid #ef4444;
        color: #991b1b;
    }
    
    .alert .close-btn {
        position: absolute;
        right: 15px;
        top: 15px;
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: inherit;
        opacity: 0.6;
    }
    
    .alert .close-btn:hover {
        opacity: 1;
    }
    
    /* Badge Styles (replacing Bootstrap badges) */
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-light {
        background: #f3f4f6;
        color: #374151;
    }
    
    .badge-success {
        background: #10b981;
        color: white;
    }
    
    .badge-primary {
        background: #3b82f6;
        color: white;
    }
    
    .badge-secondary {
        background: #6b7280;
        color: white;
    }
    
    /* Form Styles (replacing Bootstrap forms) */
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        font-weight: 500;
        margin-bottom: 8px;
        color: #374151;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s;
        box-sizing: border-box;
    }
    
    .form-control:focus {
        outline: none;
        border-color: <?php echo e($primaryColor); ?>;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    /* Checkbox Styles */
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .checkbox-label {
        cursor: pointer;
        user-select: none;
    }
    
    /* Grid System (replacing Bootstrap grid) */
    .row {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .col {
        flex: 1;
    }
    
    .col-half {
        flex: 0 0 calc(50% - 7.5px);
    }
    
    .col-third {
        flex: 0 0 calc(33.333% - 10px);
    }
    
    .col-two-thirds {
        flex: 0 0 calc(66.666% - 5px);
    }
    
    /* Margin/Padding Utilities */
    .mb-3 {
        margin-bottom: 20px;
    }
    
    .me-2 {
        margin-right: 10px;
    }
    
    /* Text Utilities */
    .text-muted {
        color: #6b7280;
    }
    
    .text-center {
        text-align: center;
    }
    
    /* Container Styles */
    .timeslots-container {
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
        border-bottom: 3px solid <?php echo e($school->schoolSetting->primary_color ?? '#667eea'); ?>;
    }
    
    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    
    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }
    
    /* View Toggle Styles */
    .view-toggle {
        display: flex;
        gap: 0;
        margin-bottom: 30px;
        background: #6b7280;
        border-radius: 8px;
        overflow: hidden;
        width: fit-content;
    }
    
    .view-btn {
        padding: 12px 28px;
        background: #6b7280;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 15px;
        font-weight: 500;
        transition: background 0.3s;
    }
    
    .view-btn.active {
        background: #4b5563;
    }
    
    .view-btn:hover {
        background: #4b5563;
    }
    
    .view-content {
        display: none;
    }
    
    .view-content.active {
        display: block;
    }
    
    /* Filter Dropdown Styles */
    .filter-dropdown {
        position: relative;
        display: inline-block;
        margin-bottom: 30px;
    }
    
    .filter-dropdown select {
        padding: 12px 40px 12px 16px;
        border: 2px solid <?php echo e($primaryColor); ?>;
        border-radius: 8px;
        background: white;
        color: #333;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        appearance: none;
        min-width: 200px;
        transition: all 0.3s ease;
    }
    
    .filter-dropdown select:hover {
        border-color: <?php echo e($secondaryColor); ?>;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
    }
    
    .filter-dropdown select:focus {
        outline: none;
        border-color: <?php echo e($primaryColor); ?>;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .filter-dropdown::after {
        content: '▼';
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: <?php echo e($primaryColor); ?>;
        font-size: 12px;
    }
    
    .filter-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }
    
    .btn-create {
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .nav-tabs {
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 30px;
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: #666;
        font-weight: 500;
        padding: 12px 24px;
        transition: all 0.3s;
    }
    
    .nav-tabs .nav-link:hover {
        color: <?php echo e($primaryColor); ?>;
        background: #f9fafb;
    }
    
    .nav-tabs .nav-link.active {
        color: <?php echo e($primaryColor); ?>;
        border-bottom: 3px solid <?php echo e($primaryColor); ?>;
        background: transparent;
    }
    
    .timeslot-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .timeslot-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .date-header {
        background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%);
        color: white;
        padding: 15px 20px;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.3s;
    }
    
    .date-header:hover {
        background: linear-gradient(135deg, #5568d3 0%, #653a8b 100%);
    }
    
    .date-header.collapsed .bi-chevron-down {
        transform: rotate(-90deg);
    }
    
    .timeslot-card .card-body {
        padding: 20px;
        transition: all 0.3s ease;
        max-height: 5000px;
        overflow: hidden;
    }
    
    .timeslot-card .card-body.collapsed {
        max-height: 0;
        padding: 0 20px;
        overflow: hidden;
    }
    
    .timeslot-item {
        background: #f9fafb;
        border-left: 4px solid transparent;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 12px;
        transition: all 0.3s;
    }
    
    .timeslot-item.type-open {
        border-left-color: #10b981;
    }
    
    .timeslot-item.type-assigned {
        border-left-color: #3b82f6;
    }
    
    .timeslot-item:hover {
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .timeslot-row {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .timeslot-time {
        flex: 0 0 250px;
    }
    
    .timeslot-details {
        flex: 1;
    }
    
    .timeslot-actions {
        flex: 0 0 100px;
        text-align: right;
    }
    
    .time-badge {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        display: inline-block;
        margin-bottom: 8px;
    }
    
    .type-badge {
        font-size: 0.75rem;
        padding: 4px 12px;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .instructor-info {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 8px;
    }
    
    .instructor-info .info-item {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #6b7280;
        font-size: 0.9rem;
    }
    
    .instructor-info .info-item i {
        color: #9ca3af;
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 20px;
    }
    
    .empty-state p {
        color: #9ca3af;
        font-size: 1.1rem;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }
    
    .btn-sm-custom {
        padding: 6px 12px;
        font-size: 0.85rem;
        border-radius: 5px;
        transition: all 0.2s;
    }
    
    .modal-header.modal-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .modal-header.modal-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    .modal-header h5 {
        margin: 0;
    }
    
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
    
    .form-label {
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 10px 12px;
        transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: <?php echo e($primaryColor); ?>;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    /* Multi-select styling */
    select[multiple] {
        padding: 8px !important;
        background: #f9fafb !important;
        min-height: 180px !important;
        display: block !important;
        width: 100% !important;
        border: 2px solid #d1d5db !important;
    }
    
    select[multiple] option {
        padding: 8px 10px;
        margin: 2px 0;
        border-radius: 4px;
        cursor: pointer;
    }
    
    select[multiple] option:hover {
        background: #e0e7ff;
    }
    
    select[multiple] option:checked {
        background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%);
        color: white;
        font-weight: 500;
    }
    
    .instructor-list {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 15px;
        max-height: 250px;
        overflow-y: auto;
        background: #f9fafb;
        margin-bottom: 8px;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .instructor-list::-webkit-scrollbar {
        display: none;
    }
    
    .instructor-checkbox {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
        transition: all 0.2s;
    }
    
    .instructor-checkbox:hover {
        background: white;
        border-color: <?php echo e($primaryColor); ?>;
    }
    
    .instructor-checkbox:has(.checkbox:checked) {
        background: #f0f4ff;
        border-color: <?php echo e($primaryColor); ?>;
    }
    
    .instructor-checkbox:has(.checkbox:checked) .checkbox-label {
        color: <?php echo e($primaryColor); ?>;
        font-weight: 500;
    }
    
    /* Custom Modal Styles - Optimized for Performance */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.6);
        justify-content: center;
        align-items: center;
    }
    
    .modal-content {
        background: white;
        width: 600px;
        max-width: 92%;
        border-radius: 19px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        will-change: auto;
    }
    
    .modal-header {
        position: relative;
        background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%);
        color: white;
        padding: 32px;
        border-radius: 16px 16px 0 0;
    }
    
    .modal-header.modal-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .modal-header.modal-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }
    
    .modal-header h5 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 600;
    }
    
    .modal-header .btn-close {
        position: absolute;
        right: 24px;
        top: 24px;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        font-size: 24px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .modal-header .btn-close:hover {
        background: rgba(255,255,255,0.3);
    }
    
    .modal-body {
        padding: 32px;
        max-height: 70vh;
        overflow-y: auto;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .modal-body::-webkit-scrollbar {
        display: none;
    }
    
    .modal-footer {
        padding: 20px 32px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        border-radius: 0 0 16px 16px;
    }
    
    /* Calendar View Styles */
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding: 15px 20px;
        background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%);
        border-radius: 10px;
        color: white;
    }
    
    .calendar-nav {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .nav-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .nav-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }
    
    .current-month {
        font-size: 1.4rem;
        font-weight: 600;
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
        margin-top: 20px;
    }
    
    .calendar-day-header {
        text-align: center;
        font-weight: 600;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 6px;
        color: #495057;
        font-size: 0.9rem;
    }
    
    .calendar-day {
        min-height: 120px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 10px;
        background: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    
    .calendar-day:hover {
        border-color: <?php echo e($primaryColor); ?>;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    
    .calendar-day.other-month {
        background: #f8f9fa;
        opacity: 0.5;
    }
    
    .calendar-day.today {
        border-color: <?php echo e($primaryColor); ?>;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    }
    
    .calendar-day.has-schedule {
        border-color: #4CAF50;
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.05) 0%, rgba(76, 175, 80, 0.02) 100%);
    }
    
    .calendar-day.clickable {
        cursor: pointer;
    }
    
    .calendar-day.clickable:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.2);
    }
    
    .day-number {
        font-weight: 600;
        font-size: 1.1rem;
        color: #333;
        margin-bottom: 8px;
    }
    
    .day-slots {
        font-size: 0.75rem;
        margin-top: 5px;
    }
    
    .slot-badge {
        display: block;
        padding: 4px 6px;
        margin: 3px 0;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .slot-badge.assigned {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    .slot-badge.open {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
</style>

<div class="timeslots-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Schedule Management</h1>
            <p class="page-subtitle">Dual assignment modes: Open (instructor self-select) or Assigned (admin-controlled)</p>
        </div>
        <div>
            <button type="button" class="btn btn-success btn-create" onclick="openCreateModal()">
                <i class="bi bi-calendar-plus"></i> Create Schedule
            </button>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="flash-message success">
        <div class="flash-icon">✓</div>
        <div class="flash-content">
            <div class="flash-title">Success!</div>
            <div class="flash-text"><?php echo e(session('success')); ?></div>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <div class="flash-message error">
        <div class="flash-icon">✕</div>
        <div class="flash-content">
            <div class="flash-title">Error!</div>
            <div class="flash-text"><?php echo e(session('error')); ?></div>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
    <?php endif; ?>

    <!-- View Toggle -->
    <div style="display: flex; justify-content: center; margin-bottom: 30px;">
        <div class="view-toggle">
            <button class="view-btn active" onclick="switchView('list')">List View</button>
            <button class="view-btn" onclick="switchView('calendar')">Calendar View</button>
        </div>
    </div>

    <!-- List View -->
    <div id="list-view" class="view-content active">
    <?php if($timeslots->isEmpty()): ?>
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <p>No schedules found. Create your first schedule above!</p>
        </div>
    <?php else: ?>
        <?php $__currentLoopData = $timeslots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $dateTimeslots): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="timeslot-card">
                <div class="date-header" onclick="toggleDate(this)" style="cursor: pointer;">
                    <span>
                        <i class="bi bi-calendar3"></i> 
                        <?php echo e(\Carbon\Carbon::parse($date)->format('F j, Y')); ?>

                    </span>
                    <span>
                        <span class="badge badge-light"><?php echo e(\Carbon\Carbon::parse($date)->format('l')); ?></span>
                        <i class="bi bi-chevron-down" style="margin-left: 10px; transition: transform 0.3s;"></i>
                    </span>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $dateTimeslots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timeslot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $adminCount = $timeslot->getAdminAssignedCount();
                            $selfCount = $timeslot->getSelfSelectedCount();
                            $totalCount = $timeslot->instructors->count();
                            $availableSpots = $timeslot->getAvailableSpots();
                        ?>
                        <div class="timeslot-item" 
                             data-slot-id="<?php echo e($timeslot->id); ?>"
                             data-date="<?php echo e($timeslot->date); ?>"
                             data-start-time="<?php echo e($timeslot->start_time); ?>"
                             data-end-time="<?php echo e($timeslot->end_time); ?>"
                             data-max-instructors="<?php echo e($timeslot->max_instructors); ?>"
                             data-notes="<?php echo e($timeslot->notes ?? ''); ?>">
                            <div class="timeslot-row">
                                <div class="timeslot-time">
                                    <div class="time-badge">
                                        <i class="bi bi-clock"></i>
                                        <?php echo e(\Carbon\Carbon::parse($timeslot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($timeslot->end_time)->format('g:i A')); ?>

                                    </div>
                                    <br>
                                    <?php if($availableSpots > 0): ?>
                                        <span class="badge badge-success">
                                            <?php echo e($availableSpots); ?> Spot<?php echo e($availableSpots > 1 ? 's' : ''); ?> Available
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">
                                            ✓ Full
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="timeslot-details">
                                    <?php if($timeslot->course): ?>
                                        <div class="course-badge" style="margin-bottom: 12px; padding: 8px 12px; background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%); color: white; border-radius: 8px; display: inline-block; font-weight: 500; box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);">
                                            <i class="bi bi-book"></i> <?php echo e($timeslot->course->title); ?>

                                            <span style="opacity: 0.9; font-size: 0.85em; margin-left: 5px;">(<?php echo e(ucfirst($timeslot->course->type)); ?>)</span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="instructor-info">
                                        <div class="info-item">
                                            <i class="bi bi-people-fill"></i>
                                            <strong><?php echo e($totalCount); ?>/<?php echo e($timeslot->max_instructors); ?></strong> Instructors
                                            <?php if($adminCount > 0 || $selfCount > 0): ?>
                                                <small class="text-muted">
                                                    (
                                                    <?php if($adminCount > 0): ?>
                                                        <span style="color: #3b82f6;"><?php echo e($adminCount); ?> admin</span>
                                                    <?php endif; ?>
                                                    <?php if($adminCount > 0 && $selfCount > 0): ?>, <?php endif; ?>
                                                    <?php if($selfCount > 0): ?>
                                                        <span style="color: #10b981;"><?php echo e($selfCount); ?> self</span>
                                                    <?php endif; ?>
                                                    )
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="info-item">
                                            <i class="bi bi-circle-fill text-<?php echo e($timeslot->status === 'open' ? 'success' : ($timeslot->status === 'closed' ? 'secondary' : 'danger')); ?>"></i>
                                            <?php echo e(ucfirst($timeslot->status)); ?>

                                        </div>
                                    </div>
                                    <?php if($timeslot->instructors->isNotEmpty()): ?>
                                        <div style="margin-top: 12px;">
                                            <?php $__currentLoopData = $timeslot->instructors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instructor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $assignmentType = $instructor->pivot->assignment_type ?? 'admin_assigned';
                                                    $badgeClass = $assignmentType === 'admin_assigned' ? 'badge-primary' : 'badge-success';
                                                    $icon = $assignmentType === 'admin_assigned' ? '[A]' : '[S]';
                                                ?>
                                                <span class="badge <?php echo e($badgeClass); ?>" title="<?php echo e(ucfirst(str_replace('_', ' ', $assignmentType))); ?>">
                                                    <?php echo e($icon); ?> <?php echo e($instructor->name); ?>

                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if($timeslot->notes): ?>
                                        <div class="timeslot-notes" style="margin-top: 12px; padding: 10px; background: #f9fafb; border-radius: 6px; font-size: 0.9rem; color: #666;">
                                            <i class="bi bi-sticky"></i> <?php echo e($timeslot->notes); ?>

                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="timeslot-actions">
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-sm btn-primary btn-sm-custom" onclick="showSlotDetails(<?php echo e($timeslot->id); ?>)" style="margin-right: 8px;">
                                            <i class="bi bi-eye"></i> View/Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-sm-custom" onclick="confirmDeleteSchedule(<?php echo e($timeslot->id); ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                        <form id="deleteScheduleForm<?php echo e($timeslot->id); ?>" method="POST" action="<?php echo e(route('schools.admin.schedules.delete', [$school, $timeslot->id])); ?>" style="display: none;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
    </div>
    <!-- End List View -->

    <!-- Calendar View -->
    <div id="calendar-view" class="view-content">
        <?php
            $currentMonth = request('month', now()->format('Y-m'));
            $calendar = \Carbon\Carbon::parse($currentMonth . '-01');
            $monthStart = $calendar->copy()->startOfMonth();
            $monthEnd = $calendar->copy()->endOfMonth();
            $calendarStart = $monthStart->copy()->startOfWeek();
            $calendarEnd = $monthEnd->copy()->endOfWeek();
            
            // Flatten all timeslots and group by date
            $allTimeslots = collect();
            foreach($timeslots as $date => $dateTimeslots) {
                $allTimeslots = $allTimeslots->merge($dateTimeslots);
            }
            $slotsByDate = $allTimeslots->groupBy(function($slot) {
                return \Carbon\Carbon::parse($slot->date)->format('Y-m-d');
            });
        ?>

        <div class="calendar-header">
            <div class="calendar-nav">
                <button type="button" class="nav-btn" onclick="changeMonth(-1)">← Previous</button>
            </div>
            <div class="current-month" id="currentMonth">
                <?php echo e($calendar->format('F Y')); ?>

            </div>
            <div class="calendar-nav">
                <button type="button" class="nav-btn" onclick="changeMonth(1)">Next →</button>
            </div>
        </div>

        <div class="calendar-grid" id="calendarGrid">
            <?php $__currentLoopData = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="calendar-day-header"><?php echo e($day); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php
                $currentDate = $calendarStart->copy();
            ?>

            <?php while($currentDate <= $calendarEnd): ?>
                <?php
                    $dateStr = $currentDate->format('Y-m-d');
                    $daySlots = $slotsByDate->get($dateStr, collect());
                    
                    $isOtherMonth = $currentDate->month !== $calendar->month;
                    $isToday = $currentDate->isToday();
                    $hasSchedule = $daySlots->isNotEmpty();
                ?>

                <div class="calendar-day <?php echo e($isOtherMonth ? 'other-month' : ''); ?> <?php echo e($isToday ? 'today' : ''); ?> <?php echo e($hasSchedule && !$isOtherMonth ? 'has-schedule clickable' : ''); ?>"
                     data-date="<?php echo e($dateStr); ?>"
                     <?php if($hasSchedule && !$isOtherMonth): ?>
                     onclick="showDayModal('<?php echo e($dateStr); ?>', '<?php echo e($currentDate->format('l, F j, Y')); ?>')"
                     style="cursor: pointer;"
                     <?php endif; ?>>
                    <div class="day-number"><?php echo e($currentDate->day); ?></div>
                    
                    <?php if(!$isOtherMonth && $hasSchedule): ?>
                        <div class="day-slots">
                            <?php $__currentLoopData = $daySlots->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $adminCount = $slot->getAdminAssignedCount();
                                    $selfCount = $slot->getSelfSelectedCount();
                                    $availableSpots = $slot->getAvailableSpots();
                                    $totalAssigned = $adminCount + $selfCount;
                                    $hasAvailableSpots = $availableSpots > 0;
                                ?>
                                <div class="slot-badge <?php echo e($hasAvailableSpots ? 'open' : 'assigned'); ?>" 
                                     title="<?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?> | <?php echo e($totalAssigned); ?>/<?php echo e($slot->max_instructors); ?> (<?php echo e($adminCount); ?> admin, <?php echo e($selfCount); ?> self)">
                                    <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?>

                                    (<?php echo e($totalAssigned); ?>/<?php echo e($slot->max_instructors); ?>)
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            
                            <?php if($daySlots->count() > 3): ?>
                                <div class="slot-badge" style="background: #6b7280; color: white;" title="Click day to see all <?php echo e($daySlots->count()); ?> schedules">
                                    +<?php echo e($daySlots->count() - 3); ?> more
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                    $currentDate->addDay();
                ?>
            <?php endwhile; ?>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem; color: #666;">
            <strong>Legend:</strong> 
            <span style="display: inline-block; width: 12px; height: 12px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 3px; margin: 0 5px;"></span> Has Available Spots
            <span style="display: inline-block; width: 12px; height: 12px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 3px; margin: 0 5px 0 15px;"></span> Fully Assigned
            <br><small style="margin-top: 8px; display: block;">Click on a time slot to view details</small>
        </div>
    </div>
    <!-- End Calendar View -->
</div>

<!-- Create Schedule Modal (Unified) -->
<div class="modal" id="createModal">
    <div class="modal-content">
        <div class="modal-header modal-success">
            <h5><i class="bi bi-calendar-plus"></i> Create Schedule</h5>
            <button type="button" class="btn-close" onclick="closeCreateModal()">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('schools.admin.schedules.create', $school)); ?>">
            <?php echo csrf_field(); ?>
            <div class="modal-body">
                <div style="padding: 15px; background: #f0f9ff; border-left: 4px solid #3b82f6; border-radius: 6px; margin-bottom: 20px;">
                    <strong>How it works:</strong>
                    <ul style="margin: 10px 0 0 0; padding-left: 20px; font-size: 0.9rem;">
                        <li>Select the course for this schedule</li>
                        <li>Set the max capacity for this schedule</li>
                        <li>Optionally pre-assign instructors (they'll be marked as "Admin Assigned")</li>
                        <li>Remaining spots will be available for instructor self-selection</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-book"></i> Course <span style="color: #ef4444;">*</span>
                    </label>
                    <?php if(isset($courses) && $courses->isNotEmpty()): ?>
                        <select name="course_id" id="createCourseSelect" class="form-control" required>
                            <option value="">-- Select a Course --</option>
                            <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($course->id); ?>">
                                    <?php echo e($course->title); ?> (<?php echo e($course->type); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php else: ?>
                        <p class="text-muted text-center" style="padding: 20px; background: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px;">
                            <i class="bi bi-exclamation-triangle"></i> No active courses available. Please create a course first.
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" required min="<?php echo e(date('Y-m-d')); ?>">
                </div>
                
                <div class="row">
                    <div class="col-half">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-half">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Max Instructors (Total Capacity)</label>
                    <input type="number" name="max_instructors" class="form-control" min="1" max="10" value="3" required>
                    <small class="text-muted">Maximum number of instructors for this schedule</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-people"></i> Pre-Assign Instructors (Optional)
                        <small style="color: #888;">(<?php echo e($instructors->count()); ?> available)</small>
                    </label>
                    <?php if($instructors->isEmpty()): ?>
                        <p class="text-muted text-center" style="padding: 20px; background: #f9fafb; border-radius: 8px;">
                            <i class="bi bi-info-circle"></i> No active instructors available.
                        </p>
                    <?php else: ?>
                        <select name="instructor_ids[]" class="form-control" multiple size="6">
                            <?php $__currentLoopData = $instructors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instructor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($instructor->id); ?>">
                                    <?php echo e($instructor->name); ?> (<?php echo e($instructor->email); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Hold Ctrl (Windows) or Cmd (Mac) to select multiple instructors. 
                            Leave unselected to make all spots available for self-selection. 
                            Selected instructors will be marked as "Admin Assigned" [A].
                        </small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="E.g., Saturday morning rush - need experienced instructors"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Create Schedule
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Schedule Details Modal -->
<div class="modal" id="detailsModal">
    <div class="modal-content">
        <div class="modal-header modal-primary">
            <h5><i class="bi bi-info-circle"></i> Schedule Details</h5>
            <button type="button" class="btn-close" onclick="closeDetailsModal()">&times;</button>
        </div>
        <div class="modal-body" id="detailsModalContent">
            <!-- Content will be populated by JavaScript -->
            <div style="text-align: center; padding: 40px;">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDetailsModal()">Close</button>
            <button type="button" class="btn btn-primary" onclick="openEditModal()">
                <i class="bi bi-pencil"></i> Edit Schedule
            </button>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header modal-primary">
            <h5><i class="bi bi-pencil-square"></i> Edit Schedule</h5>
            <button type="button" class="btn-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" id="editScheduleForm">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="modal-body" id="editModalContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Day Schedule Modal (shows all schedules for clicked day) -->
<div class="modal" id="dayModal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header modal-success">
            <div>
                <h5 style="margin: 0;"><i class="bi bi-calendar-day"></i> <span id="modalDayTitle">Schedule Details</span></h5>
                <small id="modalDayDate" style="color: rgba(255,255,255,0.9); display: block; margin-top: 5px;"></small>
            </div>
            <button type="button" class="btn-close" onclick="closeDayModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalDayBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDayModal()">Close</button>
        </div>
    </div>
</div>

<script>
    let currentSlotId = null;
    let currentSlotData = null;
    
    // Store all schedule data for calendar modal access
    const allSchedulesData = {
        <?php $__currentLoopData = $timeslots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $dateTimeslots): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                // Ensure consistent date format (Y-m-d)
                $formattedDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            ?>
            '<?php echo e($formattedDate); ?>': [
                <?php $__currentLoopData = $dateTimeslots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timeslot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $adminCount = $timeslot->getAdminAssignedCount();
                        $selfCount = $timeslot->getSelfSelectedCount();
                        $totalCount = $timeslot->instructors->count();
                        $availableSpots = $timeslot->getAvailableSpots();
                    ?>
                    {
                        id: <?php echo e($timeslot->id); ?>,
                        startTime: '<?php echo e($timeslot->start_time); ?>',
                        endTime: '<?php echo e($timeslot->end_time); ?>',
                        maxInstructors: <?php echo e($timeslot->max_instructors); ?>,
                        totalCount: <?php echo e($totalCount); ?>,
                        availableSpots: <?php echo e($availableSpots); ?>,
                        adminCount: <?php echo e($adminCount); ?>,
                        selfCount: <?php echo e($selfCount); ?>,
                        status: '<?php echo e($timeslot->status); ?>',
                        notes: <?php echo json_encode($timeslot->notes ?? ''); ?>,
                        instructors: [
                            <?php $__currentLoopData = $timeslot->instructors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instructor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                {
                                    name: <?php echo json_encode($instructor->name); ?>,
                                    type: '<?php echo e($instructor->pivot->assignment_type ?? "admin_assigned"); ?>'
                                },
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        ]
                    },
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            ],
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    };

    function openCreateModal() {
        document.getElementById('createModal').style.display = 'flex';
    }
    
    function closeCreateModal() {
        document.getElementById('createModal').style.display = 'none';
    }
    
    function openDetailsModal() {
        document.getElementById('detailsModal').style.display = 'flex';
    }
    
    function closeDetailsModal() {
        document.getElementById('detailsModal').style.display = 'none';
    }
    
    function openEditModal() {
        closeDetailsModal();
        if (currentSlotData) {
            populateEditModal(currentSlotData);
            document.getElementById('editModal').style.display = 'flex';
        }
    }
    
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    // Close modal when clicking outside of it
    window.onclick = function(event) {
        const createModal = document.getElementById('createModal');
        const detailsModal = document.getElementById('detailsModal');
        const editModal = document.getElementById('editModal');
        const dayModal = document.getElementById('dayModal');
        
        if (event.target == createModal) {
            closeCreateModal();
        } else if (event.target == detailsModal) {
            closeDetailsModal();
        } else if (event.target == editModal) {
            closeEditModal();
        } else if (event.target == dayModal) {
            closeDayModal();
        }
    }
    
    // View Toggle Functions with State Persistence
    function switchView(view) {
        const listView = document.getElementById('list-view');
        const calendarView = document.getElementById('calendar-view');
        const viewBtns = document.querySelectorAll('.view-btn');
        
        viewBtns.forEach(btn => btn.classList.remove('active'));
        
        if (view === 'list') {
            listView.classList.add('active');
            calendarView.classList.remove('active');
            viewBtns[0].classList.add('active');
            localStorage.setItem('adminTimeslotsView', 'list');
        } else {
            listView.classList.remove('active');
            calendarView.classList.add('active');
            viewBtns[1].classList.add('active');
            localStorage.setItem('adminTimeslotsView', 'calendar');
        }
    }
    
    // Restore view state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('adminTimeslotsView');
        if (savedView === 'calendar') {
            switchView('calendar');
        }
        
        // Debug: Check if instructor select exists
        const instructorSelect = document.querySelector('select[name="instructor_ids[]"]');
        if (instructorSelect) {
            console.log('Instructor select found with', instructorSelect.options.length, 'options');
        } else {
            console.warn('Instructor select not found in modal');
        }
    });
    
    // Collapsible Date Sections
    function toggleDate(dateHeader) {
        const cardBody = dateHeader.closest('.timeslot-card').querySelector('.card-body');
        cardBody.classList.toggle('collapsed');
        dateHeader.classList.toggle('collapsed');
    }
    
    // Calendar Navigation - Preserves view state
    function changeMonth(delta) {
        const currentMonthEl = document.getElementById('currentMonth');
        const monthText = currentMonthEl.textContent.trim();
        const currentDate = new Date(monthText + ' 1');
        
        currentDate.setMonth(currentDate.getMonth() + delta);
        
        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        
        const url = new URL(window.location);
        url.searchParams.set('month', `${year}-${month}`);
        window.location.href = url.toString();
    }
    
    // Show Slot Details Modal
    function showSlotDetails(slotId) {
        console.log('showSlotDetails called with ID:', slotId);
        currentSlotId = slotId;
        
        // Find the slot data from the page DOM using data attributes
        const slotItem = document.querySelector(`.timeslot-item[data-slot-id="${slotId}"]`);
        
        if (!slotItem) {
            console.error('Could not find slot item with ID:', slotId);
            Toast.error('Could not find schedule details. Please refresh the page and try again.', 'Not Found');
            return;
        }
        
        console.log('Found slot item:', slotItem);
        
        // Extract data from data attributes and DOM
        const startTime = slotItem.dataset.startTime || '';
        const endTime = slotItem.dataset.endTime || '';
        const maxInstructors = slotItem.dataset.maxInstructors || '';
        const notes = slotItem.dataset.notes || 'No notes';
        
        // Format time
        const timeText = startTime && endTime ? 
            `${formatTime(startTime)} - ${formatTime(endTime)}` : 
            'Time not available';
        
        // Get instructors from the DOM
        const instructorBadges = slotItem.querySelectorAll('.badge-primary, .badge-success');
        let instructorsList = [];
        instructorBadges.forEach(badge => {
            const nameText = badge.textContent.trim().replace(/^\[A\]\s*|^\[S\]\s*/, ''); // Remove prefix
            instructorsList.push({
                name: nameText,
                type: badge.classList.contains('badge-primary') ? 'admin' : 'self'
            });
        });
        
        console.log('Instructors found:', instructorsList);
        
        // Get availability
        const availableBadge = slotItem.querySelector('.badge-success:not(.badge-primary), .badge-secondary');
        const availableText = availableBadge ? availableBadge.textContent.trim() : 'Full';
        
        // Store data for edit modal
        currentSlotData = {
            id: slotId,
            time: timeText,
            instructors: instructorsList,
            notes: notes,
            available: availableText,
            maxInstructors: maxInstructors
        };
            
            // Populate details modal
            let instructorsHtml = '';
            if (instructorsList.length > 0) {
                instructorsHtml = instructorsList.map(inst => {
                    const icon = inst.type === 'admin' ? '[A]' : '[S]';
                    const label = inst.type === 'admin' ? 'Admin Assigned' : 'Self Selected';
                    return `<span class="badge badge-${inst.type === 'admin' ? 'primary' : 'success'}" title="${label}">${icon} ${inst.name}</span>`;
                }).join(' ');
            } else {
                instructorsHtml = '<span class="text-muted">No instructors assigned yet</span>';
            }
            
            document.getElementById('detailsModalContent').innerHTML = `
                <div style="padding: 10px;">
                    <div style="margin-bottom: 20px;">
                        <strong style="color: #666; display: block; margin-bottom: 8px;">⏰ Time:</strong>
                        <div style="font-size: 1.1rem;">${timeText}</div>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <strong style="color: #666; display: block; margin-bottom: 8px;">Instructors:</strong>
                        <div>${instructorsHtml}</div>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <strong style="color: #666; display: block; margin-bottom: 8px;">Availability:</strong>
                        <div>${availableText}</div>
                    </div>
                    
                    <div>
                        <strong style="color: #666; display: block; margin-bottom: 8px;">Notes:</strong>
                        <div style="background: #f9fafb; padding: 12px; border-radius: 6px; min-height: 40px;">
                            ${notes}
                        </div>
                    </div>
                </div>
            `;
            
            openDetailsModal();
        
        console.log('Modal opened successfully');
    }
    
    // Helper function to format time (HH:MM:SS to HH:MM AM/PM)
    function formatTime(timeStr) {
        if (!timeStr) return '';
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minutes} ${ampm}`;
    }
    
    // Show all schedules for a specific day in modal
    function showDayModal(dateStr, formattedDate) {
        console.log('Showing day modal for:', dateStr);
        console.log('All available dates in allSchedulesData:', Object.keys(allSchedulesData));
        console.log('Full allSchedulesData object:', allSchedulesData);
        
        document.getElementById('modalDayTitle').textContent = 'Schedules for ' + formattedDate.split(',')[0];
        document.getElementById('modalDayDate').textContent = formattedDate;
        
        // Get schedules for this date from the stored data
        const daySchedules = allSchedulesData[dateStr] || [];
        
        console.log('Looking for date:', dateStr);
        console.log('Found schedules:', daySchedules);
        
        if (daySchedules.length === 0) {
            document.getElementById('modalDayBody').innerHTML = `
                <div style="text-align: center; padding: 40px; color: #999;">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <p>No schedules found for this date.</p>
                </div>
            `;
        } else {
            let modalContent = '<div style="display: flex; flex-direction: column; gap: 15px;">';
            
            daySchedules.forEach(schedule => {
                // Build instructors HTML
                let instructorsHtml = '';
                if (schedule.instructors.length > 0) {
                    schedule.instructors.forEach(instructor => {
                        const icon = instructor.type === 'admin_assigned' ? '[A]' : '[S]';
                        const badgeClass = instructor.type === 'admin_assigned' ? 'badge-primary' : 'badge-success';
                        const title = instructor.type === 'admin_assigned' ? 'Admin Assigned' : 'Self Selected';
                        instructorsHtml += `<span class="badge ${badgeClass}" title="${title}">${icon} ${instructor.name}</span> `;
                    });
                } else {
                    instructorsHtml = '<span style="color: #999;">No instructors assigned</span>';
                }
                
                // Build availability badge
                let availableHtml = '';
                if (schedule.availableSpots > 0) {
                    availableHtml = `<span class="badge badge-success">🔓 ${schedule.availableSpots} Spot${schedule.availableSpots > 1 ? 's' : ''} Available</span>`;
                } else {
                    availableHtml = '<span class="badge badge-secondary">✓ Full</span>';
                }
                
                modalContent += `
                    <div style="border: 2px solid #e5e7eb; border-radius: 8px; padding: 15px; background: #f9fafb; transition: all 0.2s;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                            <div>
                                <div style="font-size: 1.1rem; font-weight: 600; color: #333; margin-bottom: 5px;">
                                    <i class="bi bi-clock"></i> ${formatTime(schedule.startTime)} - ${formatTime(schedule.endTime)}
                                </div>
                                ${availableHtml}
                                <div style="margin-top: 5px; font-size: 0.9rem; color: #666;">
                                    <i class="bi bi-people-fill"></i> ${schedule.totalCount}/${schedule.maxInstructors} Instructors
                                    ${schedule.adminCount > 0 || schedule.selfCount > 0 ? 
                                        `<span style="font-size: 0.85rem; color: #999;">
                                            (${schedule.adminCount > 0 ? `<span style="color: #3b82f6;">${schedule.adminCount} admin</span>` : ''}${schedule.adminCount > 0 && schedule.selfCount > 0 ? ', ' : ''}${schedule.selfCount > 0 ? `<span style="color: #10b981;">${schedule.selfCount} self</span>` : ''})
                                        </span>` 
                                        : ''}
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="closeDayModal(); showSlotDetails(${schedule.id})">
                                <i class="bi bi-eye"></i> View/Edit
                            </button>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong style="color: #666;">Instructors:</strong><br>
                            ${instructorsHtml}
                        </div>
                        ${schedule.notes ? `
                        <div style="margin-top: 10px; padding: 10px; background: white; border-radius: 4px; font-size: 0.9rem; color: #666;">
                            <i class="bi bi-sticky"></i> ${schedule.notes}
                        </div>
                        ` : ''}
                    </div>
                `;
            });
            
            modalContent += '</div>';
            document.getElementById('modalDayBody').innerHTML = modalContent;
        }
        
        document.getElementById('dayModal').style.display = 'flex';
    }
    
    function closeDayModal() {
        document.getElementById('dayModal').style.display = 'none';
    }
    
    // Populate Edit Modal
    function populateEditModal(slotData) {
        const schoolSlug = '<?php echo e($school->slug); ?>';
        const actionUrl = `/${schoolSlug}/admin/schedules/${slotData.id}`;
        document.getElementById('editScheduleForm').action = actionUrl;
        
        // Get available instructors from the create modal
        const createModalSelect = document.querySelector('#createModal select[name="instructor_ids[]"]');
        const allInstructors = createModalSelect ? Array.from(createModalSelect.options).map(opt => ({
            id: opt.value,
            name: opt.text
        })) : [];
        
        // Build instructor multi-select
        let instructorOptions = allInstructors.map(inst => {
            // Match by checking if instructor name is in the assigned list
            const instructorName = inst.name.split(' (')[0].trim(); // Remove email part
            const isSelected = slotData.instructors.some(assigned => {
                const assignedName = assigned.name.replace(/[🔵🟢]\s*/, '').trim();
                return assignedName === instructorName;
            });
            return `<option value="${inst.id}" ${isSelected ? 'selected' : ''}>${inst.name}</option>`;
        }).join('');
        
        document.getElementById('editModalContent').innerHTML = `
            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-people"></i> Assigned Instructors
                </label>
                <select name="instructor_ids[]" class="form-control" multiple size="6" style="height: auto;">
                    ${instructorOptions}
                </select>
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Hold Ctrl (Windows) or Cmd (Mac) to select multiple. 
                    Changes will update instructor assignments.
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">${slotData.notes !== 'No notes' ? slotData.notes : ''}</textarea>
            </div>
        `;
    }
    
    // Confirm Delete Schedule
    function confirmDeleteSchedule(scheduleId) {
        showConfirm({
            type: 'danger',
            title: 'Delete Schedule',
            message: 'Are you sure you want to delete this schedule? This action cannot be undone.',
            confirmText: 'Yes, Delete',
            onConfirm: () => {
                document.getElementById('deleteScheduleForm' + scheduleId).submit();
            }
        });
    }
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\admin\schedules.blade.php ENDPATH**/ ?>