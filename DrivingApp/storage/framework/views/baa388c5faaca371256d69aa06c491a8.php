
<?php $__env->startSection('title', 'Courses'); ?>
<?php $__env->startSection('page-title', 'All Courses'); ?>
<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header"><h3>Courses (<?php echo e($courses->total()); ?>)</h3></div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>School</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><strong><?php echo e($course->title); ?></strong></td>
                    <td><?php echo e($course->school->name); ?></td>
                    <td><?php echo e($course->duration_hours ?? 'N/A'); ?> hours</td>
                    <td>₱<?php echo e(number_format($course->price ?? 0, 2)); ?></td>
                    <td><?php echo e($course->created_at->format('M d, Y')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php echo e($courses->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.system-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\system-admin\courses.blade.php ENDPATH**/ ?>