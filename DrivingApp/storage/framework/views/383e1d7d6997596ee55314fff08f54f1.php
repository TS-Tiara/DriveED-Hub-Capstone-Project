

<?php $__env->startSection('title', 'Instructor Reports'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $school->schoolSetting->primary_color ?? '#667eea';
?>

<div style="padding: 20px; margin: 20px auto; max-width: 1600px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid <?php echo e($primaryColor); ?>;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 600; color: #1f2937; margin: 0;">Instructor Reports</h1>
            <p style="color: #6b7280; font-size: 0.9rem; margin-top: 5px;">Instructor performance and activity reports for <?php echo e($schoolName); ?></p>
        </div>
    </div>
    
    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); padding: 60px 40px; text-align: center;">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, #764ba2 100%); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        </div>
        <h3 style="font-size: 1.25rem; color: #1f2937; margin-bottom: 10px;">Instructor Reports Coming Soon</h3>
        <p style="color: #6b7280; max-width: 400px; margin: 0 auto;">Comprehensive instructor performance, availability, and activity reports will be available here.</p>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\admin\reports\instructors.blade.php ENDPATH**/ ?>