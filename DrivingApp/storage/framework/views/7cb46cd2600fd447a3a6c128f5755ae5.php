

<?php $__env->startSection('title', 'Edit Progress'); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-container">
    <div class="page-header">
        <h1>Edit Student Progress</h1>
        <p>Update progress for <?php echo e($progress->student->name ?? 'Student'); ?></p>
    </div>

    <div class="form-card">
        <form action="<?php echo e($schoolRoute('instructor.progress.update', $progress->id)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            
            <div class="form-group">
                <label>Student</label>
                <input type="text" value="<?php echo e($progress->student->name ?? 'Unknown'); ?>" disabled>
            </div>

            <div class="form-group">
                <label>Course</label>
                <input type="text" value="<?php echo e($progress->course->title ?? 'Unknown'); ?>" disabled>
            </div>

            <div class="form-group">
                <label for="completion_percent">Completion Percentage</label>
                <input type="number" name="completion_percent" id="completion_percent" 
                       min="0" max="100" value="<?php echo e($progress->completion_percent); ?>" required>
            </div>

            <div class="form-group">
                <label for="notes">Notes (Optional)</label>
                <textarea name="notes" id="notes" rows="4"><?php echo e($progress->notes); ?></textarea>
            </div>

            <div class="form-actions">
                <a href="<?php echo e($schoolRoute('instructor.students.index')); ?>" class="btn-secondary" onclick="loadContent(this.href); return false;">Cancel</a>
                <button type="submit" class="btn-primary">Update Progress</button>
            </div>
        </form>
    </div>
</div>

<style>
    .dashboard-container {
        padding: 20px;
        max-width: 600px;
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

    .form-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
    }

    .form-group input:disabled {
        background: #f5f5f5;
        color: #666;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary-color, #007bff);
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .btn-primary {
        background: var(--primary-color, #007bff);
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
    }

    .btn-secondary {
        background: #f1f1f1;
        color: #333;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    .btn-secondary:hover {
        background: #e5e5e5;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\instructor\progress-edit.blade.php ENDPATH**/ ?>