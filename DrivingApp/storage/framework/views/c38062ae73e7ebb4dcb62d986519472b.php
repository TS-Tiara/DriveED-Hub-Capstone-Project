

<?php $__env->startSection('title', 'Student Reports'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
?>

<div style="padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 20px;">
    <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f1c40f;">
        <h1 style="font-size: 2rem; color: #333; margin: 0;">Student Reports - <?php echo e($schoolName); ?></h1>
    </div>
    
    <p style="font-size: 16px; color: #666; margin-bottom: 20px;">
        This is where the admin can review student performance and progress metrics for <?php echo e($schoolName); ?>.
    </p>
    
    <div style="text-align: center; padding: 40px; color: #999;">
        <h3>Student Reports Coming Soon</h3>
        <p>Comprehensive student performance tracking and reporting features will be available here.</p>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\admin\reports\students.blade.php ENDPATH**/ ?>