
<?php $__env->startSection('title', 'Bookings'); ?>
<?php $__env->startSection('page-title', 'All Bookings'); ?>
<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header"><h3>Bookings (<?php echo e($bookings->total()); ?>)</h3></div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>School</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($booking->id); ?></td>
                    <td><?php echo e($booking->student->name ?? 'N/A'); ?></td>
                    <td><?php echo e($booking->course->title ?? 'N/A'); ?></td>
                    <td><?php echo e($booking->school->name); ?></td>
                    <td>
                        <span class="badge 
                            <?php if($booking->status === 'completed'): ?> badge-success
                            <?php elseif($booking->status === 'pending'): ?> badge-warning
                            <?php else: ?> badge-secondary
                            <?php endif; ?>">
                            <?php echo e(ucfirst($booking->status)); ?>

                        </span>
                    </td>
                    <td><?php echo e($booking->created_at->format('M d, Y')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php echo e($bookings->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.system-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\system-admin\bookings.blade.php ENDPATH**/ ?>