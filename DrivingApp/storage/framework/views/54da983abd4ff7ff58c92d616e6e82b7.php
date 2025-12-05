
<?php $__env->startSection('title', 'Payments'); ?>
<?php $__env->startSection('page-title', 'All Payments'); ?>
<?php $__env->startSection('content'); ?>
<div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 24px;">
    <div class="stat-card">
        <h3>Total Paid</h3>
        <div class="value" style="color: #059669;">₱<?php echo e(number_format($totalPaid, 2)); ?></div>
    </div>
    <div class="stat-card">
        <h3>Pending Payments</h3>
        <div class="value" style="color: #f59e0b;">₱<?php echo e(number_format($totalPending, 2)); ?></div>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3>Payments (<?php echo e($payments->total()); ?>)</h3></div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Method</th>
                    <th>School</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($payment->id); ?></td>
                    <td><?php echo e($payment->booking->student->name ?? 'N/A'); ?></td>
                    <td><strong>₱<?php echo e(number_format($payment->amount, 2)); ?></strong></td>
                    <td>
                        <span class="badge <?php echo e($payment->status === 'paid' ? 'badge-success' : 'badge-warning'); ?>">
                            <?php echo e(ucfirst($payment->status)); ?>

                        </span>
                    </td>
                    <td><?php echo e($payment->payment_method ?? 'N/A'); ?></td>
                    <td><?php echo e($payment->school->name); ?></td>
                    <td><?php echo e($payment->created_at->format('M d, Y')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php echo e($payments->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.system-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\system-admin\payments.blade.php ENDPATH**/ ?>