<!DOCTYPE html>
<html>
<head>
    <?php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    ?>
    <title>Instructor Reports - <?php echo e($schoolName); ?></title>
</head>
<body>
    <h1>Instructor Reports - <?php echo e($schoolName); ?></h1>
    <p>Instructor performance, availability, and activity reports for <?php echo e($schoolName); ?> appear here.</p>

    <p><a href="<?php echo e($schoolRoute('admin.dashboard')); ?>">← Back to Dashboard</a></p>
</body>
</html><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\admin\reports\instructors.blade.php ENDPATH**/ ?>