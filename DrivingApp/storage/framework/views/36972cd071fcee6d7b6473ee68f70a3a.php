<!DOCTYPE html>
<html>
<head>
    <?php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    ?>
    <title>System Logs - <?php echo e($schoolName); ?></title>
</head>
<body>
    <h1>System Logs - <?php echo e($schoolName); ?></h1>
    <p>Recent activities and audit logs scoped to <?php echo e($schoolName); ?> will appear in this section.</p>

    <p><a href="<?php echo e($schoolRoute('admin.dashboard')); ?>">← Back to Dashboard</a></p>
</body>
</html><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\admin\reports\logs.blade.php ENDPATH**/ ?>