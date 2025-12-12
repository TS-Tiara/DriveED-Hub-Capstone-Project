

<?php $__env->startSection('title', 'My Progress'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school->schoolSetting;
?>

<style>
.progress-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 4px solid <?php echo e($settings->primary_color ?? '#667eea'); ?>;
}

.page-title {
    font-size: 2rem;
    color: #111827;
    margin: 0;
    font-weight: 400;
}

.progress-grid {
    display: grid;
    gap: 25px;
}

.progress-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.progress-header h3 {
    font-size: 1.5rem;
    color: #333;
}

.progress-percentage {
    font-size: 3rem;
    font-weight: 700;
    <?php if($settings->use_gradient_header ?? false): ?>
        background: linear-gradient(135deg, <?php echo e($settings->primary_color ?? '#667eea'); ?>, <?php echo e($settings->secondary_color ?? '#764ba2'); ?>);
    <?php else: ?>
        background: <?php echo e($settings->primary_color ?? '#667eea'); ?>;
    <?php endif; ?>
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.progress-bar-container {
    width: 100%;
    height: 40px;
    background: #e5e7eb;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 25px;
}

.progress-bar-fill {
    height: 100%;
    <?php if($settings->use_gradient_header ?? false): ?>
        background: linear-gradient(135deg, <?php echo e($settings->primary_color ?? '#667eea'); ?> 0%, <?php echo e($settings->secondary_color ?? '#764ba2'); ?> 100%);
    <?php else: ?>
        background: <?php echo e($settings->primary_color ?? '#667eea'); ?>;
    <?php endif; ?>
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 20px;
    transition: width 0.5s ease;
}

.progress-bar-text {
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
}

.progress-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.info-item {
    padding: 20px;
    background: #f9fafb;
    border-radius: 10px;
}

.info-item .label {
    font-size: 0.9rem;
    color: #6b7280;
    margin-bottom: 8px;
}

.info-item .value {
    font-size: 1.2rem;
    color: #1f2937;
    font-weight: 600;
}

.notes-section {
    margin-top: 20px;
    padding: 20px;
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    border-radius: 8px;
}

.notes-section h4 {
    margin: 0 0 10px 0;
    color: #92400e;
}

.notes-section p {
    margin: 0;
    color: #78350f;
    line-height: 1.6;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .container {
        padding: 20px 15px;
    }
    
    .page-header h1 {
        font-size: 24px;
    }
    
    .progress-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .progress-card {
        padding: 20px;
    }
    
    .progress-header h3 {
        font-size: 1.5rem;
    }
    
    .progress-percentage {
        font-size: 2rem;
    }
    
    .progress-bar {
        height: 30px;
    }
    
    .progress-bar-fill {
        font-size: 13px;
    }
    
    .progress-details {
        padding: 15px;
    }
    
    .stat-item {
        padding: 10px;
    }
    
    .stat-item .label {
        font-size: 13px;
    }
    
    .stat-item .value {
        font-size: 20px;
    }
    
    .notes-section {
        padding: 15px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 15px 10px;
    }
    
    .page-header h1 {
        font-size: 20px;
    }
    
    .progress-card {
        padding: 15px;
    }
    
    .progress-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .progress-header h3 {
        font-size: 1.25rem;
    }
    
    .progress-percentage {
        font-size: 1.75rem;
    }
    
    .progress-bar {
        height: 25px;
    }
    
    .progress-bar-fill {
        font-size: 12px;
    }
    
    .progress-details {
        grid-template-columns: 1fr;
        padding: 12px;
        gap: 8px;
    }
    
    .stat-item {
        padding: 8px;
    }
    
    .stat-item .label {
        font-size: 12px;
    }
    
    .stat-item .value {
        font-size: 18px;
    }
    
    .notes-section {
        padding: 12px;
    }
    
    .notes-section h4 {
        font-size: 14px;
    }
    
    .notes-section p {
        font-size: 13px;
    }
}
</style>

<div class="progress-container">
    <div class="page-header">
        <h1 class="page-title">My Progress</h1>
    </div>

    <div class="progress-grid">
        <?php $__empty_1 = true; $__currentLoopData = $progresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $progress): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="progress-card">
            <div class="progress-header">
                <h3><?php echo e($progress->course->title); ?></h3>
                <div class="progress-percentage"><?php echo e($progress->completion_percent); ?>%</div>
            </div>

            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?php echo e($progress->completion_percent); ?>%;">
                    <?php if($progress->completion_percent > 10): ?>
                    <span class="progress-bar-text"><?php echo e($progress->completion_percent); ?>% Complete</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="progress-info">
                <div class="info-item">
                    <div class="label">Status</div>
                    <div class="value">
                        <?php if($progress->completion_percent == 100): ?>
                            <span style="color: #10b981;">✓ Completed</span>
                        <?php elseif($progress->completion_percent > 0): ?>
                            <span style="color: #3b82f6;">⏳ In Progress</span>
                        <?php else: ?>
                            <span style="color: #6b7280;">⚪ Not Started</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Last Updated</div>
                    <div class="value"><?php echo e($progress->last_updated ? $progress->last_updated->format('M d, Y') : 'Never'); ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Total Hours</div>
                    <div class="value"><?php echo e($progress->course->duration_hours); ?> hours</div>
                </div>
            </div>

            <?php if($progress->notes): ?>
            <div class="notes-section">
                <h4>Instructor's Notes</h4>
                <p><?php echo e($progress->notes); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="text-align: center; padding: 60px 20px; color: #9ca3af;">
            <p style="font-size: 1.2rem;">No progress records found</p>
            <p>Your instructor will update your progress after each lesson</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/school/student/progress.blade.php ENDPATH**/ ?>