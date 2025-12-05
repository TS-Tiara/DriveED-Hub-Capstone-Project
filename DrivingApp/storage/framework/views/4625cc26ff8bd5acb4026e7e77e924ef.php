

<?php $__env->startSection('title', 'Student Progress'); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-container">
    <div class="page-header">
        <div class="header-content">
            <h1>Student Progress</h1>
            <p>Track and manage student learning progress</p>
        </div>
    </div>

    <!-- Progress List -->
    <div class="progress-list">
        <?php $__empty_1 = true; $__currentLoopData = $progresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $progress): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="progress-card">
                <div class="progress-header">
                    <div class="student-info">
                        <h3><?php echo e($progress->student->name ?? 'Unknown Student'); ?></h3>
                        <p class="course-name"><?php echo e($progress->course->title ?? 'Unknown Course'); ?></p>
                    </div>
                    <div class="progress-percent">
                        <span class="percent-value"><?php echo e($progress->completion_percent); ?>%</span>
                    </div>
                </div>
                
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo e($progress->completion_percent); ?>%"></div>
                </div>
                
                <div class="progress-details">
                    <div class="detail-item">
                        <span class="label">Hours Completed:</span>
                        <span class="value"><?php echo e($progress->hours_completed ?? 0); ?> hrs</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Last Updated:</span>
                        <span class="value"><?php echo e($progress->last_updated ? \Carbon\Carbon::parse($progress->last_updated)->format('M d, Y') : 'N/A'); ?></span>
                    </div>
                </div>

                <?php if($progress->notes): ?>
                <div class="progress-notes">
                    <p><?php echo e($progress->notes); ?></p>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty-state">
                <p>No progress records found for your students.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .dashboard-container {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 28px;
        color: #333;
        margin-bottom: 5px;
    }

    .page-header p {
        color: #666;
    }

    .progress-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .progress-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .student-info h3 {
        font-size: 18px;
        color: #333;
        margin-bottom: 5px;
    }

    .course-name {
        color: #666;
        font-size: 14px;
    }

    .progress-percent {
        background: var(--primary-color, #007bff);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
    }

    .progress-bar-container {
        height: 10px;
        background: #e9ecef;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 15px;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-color, #007bff), var(--accent-color, #0056b3));
        border-radius: 5px;
    }

    .progress-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        padding: 15px 0;
        border-top: 1px solid #eee;
    }

    .detail-item .label {
        color: #888;
        font-size: 12px;
        display: block;
        margin-bottom: 3px;
    }

    .detail-item .value {
        color: #333;
        font-weight: 500;
    }

    .progress-notes {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-top: 15px;
    }

    .progress-notes p {
        color: #333;
        margin: 0;
        font-size: 14px;
    }

    .empty-state {
        text-align: center;
        padding: 50px;
        background: white;
        border-radius: 12px;
        color: #666;
    }

    @media (max-width: 768px) {
        .progress-header {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\instructor\progress.blade.php ENDPATH**/ ?>