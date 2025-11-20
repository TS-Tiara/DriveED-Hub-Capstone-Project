

<?php $__env->startSection('title', 'Student Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .container {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
    
    h1 {
        color: #333;
        text-align: center;
        margin-bottom: 30px;
        font-size: 2.5rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .dashboard-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        font-size: 1.2rem;
        font-weight: 600;
        text-align: center;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .card-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .card-item:last-child {
        border-bottom: none;
    }
    
    .card-item-label {
        font-weight: 500;
        color: #374151;
    }
    
    .card-item-value {
        color: #6b7280;
        font-weight: 600;
    }
    
    .progress-bar {
        width: 100%;
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 8px;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        transition: width 0.3s ease;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .status-active {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-completed {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 30px;
    }
    
    .action-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 15px 20px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
        display: block;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        color: white;
        text-decoration: none;
    }
    
    @media (max-width: 768px) {
        h1 {
            font-size: 2rem;
        }
        
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container">
    <h1>Student Dashboard - <?php echo e($schoolName ?? 'Driving School'); ?></h1>
    
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-header">
                📚 Learning Progress
            </div>
            <div class="card-body">
                <div class="card-item">
                    <span class="card-item-label">Lessons Completed</span>
                    <span class="card-item-value"><?php echo e($completedLessons ?? '8'); ?>/<?php echo e($totalLessons ?? '20'); ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo e($progressPercentage ?? '40'); ?>%"></div>
                </div>
                <div class="card-item">
                    <span class="card-item-label">Hours Driven</span>
                    <span class="card-item-value"><?php echo e($hoursDriven ?? '16'); ?> hours</span>
                </div>
                <div class="card-item">
                    <span class="card-item-label">Current Level</span>
                    <span class="card-item-value"><?php echo e($currentLevel ?? 'Intermediate'); ?></span>
                </div>
                <div class="card-item">
                    <span class="card-item-label">Status</span>
                    <span class="status-badge status-active">Active</span>
                </div>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                📅 Upcoming Lessons
            </div>
            <div class="card-body">
                <div class="card-item">
                    <span class="card-item-label">Next Lesson</span>
                    <span class="card-item-value"><?php echo e($nextLesson ?? 'Tomorrow 3:00 PM'); ?></span>
                </div>
                <div class="card-item">
                    <span class="card-item-label">Instructor</span>
                    <span class="card-item-value"><?php echo e($instructorName ?? 'John Smith'); ?></span>
                </div>
                <div class="card-item">
                    <span class="card-item-label">Lesson Type</span>
                    <span class="card-item-value"><?php echo e($lessonType ?? 'Highway Driving'); ?></span>
                </div>
                <div class="card-item">
                    <span class="card-item-label">This Week</span>
                    <span class="card-item-value"><?php echo e($weeklyLessons ?? '3'); ?> lessons</span>
                </div>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                🎯 Goals & Achievements
            </div>
            <div class="card-body">
                <div class="card-item">
                    <span class="card-item-label">Test Readiness</span>
                    <span class="card-item-value"><?php echo e($testReadiness ?? '65'); ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo e($testReadiness ?? '65'); ?>%"></div>
                </div>
                <div class="card-item">
                    <span class="card-item-label">Skills Mastered</span>
                    <span class="card-item-value"><?php echo e($skillsMastered ?? '12'); ?>/<?php echo e($totalSkills ?? '20'); ?></span>
                </div>
                <div class="card-item">
                    <span class="card-item-label">Practice Hours</span>
                    <span class="card-item-value"><?php echo e($practiceHours ?? '25'); ?>/<?php echo e($requiredHours ?? '40'); ?></span>
                </div>
                <div class="card-item">
                    <span class="card-item-label">Est. Test Date</span>
                    <span class="card-item-value"><?php echo e($estimatedTestDate ?? 'Dec 15, 2025'); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="quick-actions">
        <a href="<?php echo e($schoolRoute('student.courses.index')); ?>" class="action-btn" onclick="loadContent(this.href); return false;">
            � Browse Courses
        </a>
        <a href="<?php echo e($schoolRoute('student.bookings.index')); ?>" class="action-btn" onclick="loadContent(this.href); return false;">
            � My Bookings
        </a>
        <a href="<?php echo e($schoolRoute('student.payments.index')); ?>" class="action-btn" onclick="loadContent(this.href); return false;">
            💰 Payments
        </a>
        <a href="<?php echo e($schoolRoute('student.progress.index')); ?>" class="action-btn" onclick="loadContent(this.href); return false;">
            � My Progress
        </a>
        <a href="<?php echo e($schoolRoute('student.profile')); ?>" class="action-btn" onclick="loadContent(this.href); return false;">
            � Update Profile
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/school/student/dashboard.blade.php ENDPATH**/ ?>