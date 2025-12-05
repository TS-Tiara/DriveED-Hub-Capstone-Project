

<?php $__env->startSection('title', 'Progress Details'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#053d86';
?>

<style>
    .progress-detail-container {
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid <?php echo $primaryColor; ?>;
    }
    
    .page-header h1 {
        color: #333;
        font-size: 1.75rem;
        margin: 0;
    }
    
    .back-btn {
        padding: 10px 20px;
        background: #6b7280;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: background 0.3s;
    }
    
    .back-btn:hover {
        background: #4b5563;
        color: white;
    }
    
    .detail-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .detail-header {
        background: <?php echo $primaryColor; ?>;
        color: white;
        padding: 20px;
    }
    
    .detail-header h2 {
        margin: 0 0 5px 0;
        font-size: 1.5rem;
    }
    
    .detail-header p {
        margin: 0;
        opacity: 0.9;
    }
    
    .detail-body {
        padding: 25px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }
    
    .info-item {
        padding: 15px;
        background: #f9fafb;
        border-radius: 8px;
    }
    
    .info-item label {
        display: block;
        font-size: 0.8rem;
        color: #6b7280;
        margin-bottom: 5px;
        text-transform: uppercase;
    }
    
    .info-item .value {
        font-size: 1.1rem;
        color: #111827;
        font-weight: 600;
    }
    
    .progress-visual {
        margin: 25px 0;
    }
    
    .progress-bar-container {
        background: #e5e7eb;
        border-radius: 10px;
        height: 30px;
        overflow: hidden;
    }
    
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, <?php echo $primaryColor; ?>, #10b981);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.9rem;
        transition: width 0.5s ease;
    }
    
    .notes-section {
        margin-top: 20px;
        padding: 15px;
        background: #f9fafb;
        border-radius: 8px;
    }
    
    .notes-section h3 {
        margin: 0 0 10px 0;
        color: #374151;
        font-size: 1rem;
    }
    
    .notes-section p {
        margin: 0;
        color: #6b7280;
        line-height: 1.6;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }
    
    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }
    
    .btn-primary {
        background: <?php echo $primaryColor; ?>;
        color: white;
    }
    
    .btn-primary:hover {
        filter: brightness(1.1);
        color: white;
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .btn-danger:hover {
        background: #dc2626;
        color: white;
    }
</style>

<div class="progress-detail-container">
    <div class="page-header">
        <h1>Progress Details</h1>
    </div>
    
    <div class="detail-card">
        <div class="detail-header">
            <h2><?php echo e($progress->student->name ?? 'Student'); ?></h2>
            <p><?php echo e($progress->course->title ?? 'Course'); ?></p>
        </div>
        
        <div class="detail-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Student Email</label>
                    <div class="value"><?php echo e($progress->student->email ?? 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <label>Course</label>
                    <div class="value"><?php echo e($progress->course->title ?? 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <label>Last Updated</label>
                    <div class="value"><?php echo e($progress->last_updated ? \Carbon\Carbon::parse($progress->last_updated)->format('M d, Y') : 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <label>Created</label>
                    <div class="value"><?php echo e($progress->created_at ? $progress->created_at->format('M d, Y') : 'N/A'); ?></div>
                </div>
            </div>
            
            <div class="progress-visual">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151;">Completion Progress</label>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: <?php echo e($progress->completion_percent ?? 0); ?>%;">
                        <?php echo e(number_format($progress->completion_percent ?? 0, 1)); ?>%
                    </div>
                </div>
            </div>
            
            <?php if($progress->notes): ?>
            <div class="notes-section">
                <h3>Notes</h3>
                <p><?php echo e($progress->notes); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="<?php echo e($schoolRoute('instructor.progress.edit', ['progress' => $progress->id])); ?>" class="btn btn-primary" onclick="loadContent(this.href); return false;">
                    Edit Progress
                </a>
                <form action="<?php echo e($schoolRoute('instructor.progress.destroy', ['progress' => $progress->id])); ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this progress record?');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\instructor\progress-show.blade.php ENDPATH**/ ?>