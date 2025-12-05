

<?php $__env->startSection('title', 'Payments & Transactions'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
?>

<?php echo $__env->make('school.admin.partials.admin-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<style>
    .method-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.85rem;
        background: #e0e7ff;
        color: #3730a3;
    }
    
    .amount-cell {
        font-weight: 600;
        color: #059669;
    }
    
    .reference-cell {
        font-family: monospace;
        font-size: 0.85rem;
        color: #6b7280;
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Payments & Transactions</h1>
            <p class="page-subtitle">Track and manage all payments for <?php echo e($schoolName); ?></p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₱<?php echo e(number_format($payments->where('status', 'completed')->sum('amount'), 2)); ?></div>
        </div>
        <div class="stat-card success">
            <div class="stat-label">Completed Payments</div>
            <div class="stat-value"><?php echo e($payments->where('status', 'completed')->count()); ?></div>
        </div>
        <div class="stat-card warning">
            <div class="stat-label">Pending Payments</div>
            <div class="stat-value"><?php echo e($payments->where('status', 'pending')->count()); ?></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-group">
        <button class="filter-btn active" data-filter="all" onclick="filterPayments('all', this)">All Payments</button>
        <button class="filter-btn" data-filter="completed" onclick="filterPayments('completed', this)">Completed</button>
        <button class="filter-btn" data-filter="pending" onclick="filterPayments('pending', this)">Pending</button>
        <button class="filter-btn" data-filter="failed" onclick="filterPayments('failed', this)">Failed</button>
    </div>

    <!-- Payments Table -->
    <div class="content-card">
        <div style="overflow-x: auto;">
            <table class="admin-table" id="paymentsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr data-status="<?php echo e($payment->status); ?>">
                        <td><?php echo e($payment->paid_on ? $payment->paid_on->format('M d, Y') : 'N/A'); ?></td>
                        <td><strong><?php echo e($payment->booking->student->name ?? 'N/A'); ?></strong></td>
                        <td><?php echo e($payment->booking->course->title ?? 'N/A'); ?></td>
                        <td class="amount-cell">₱<?php echo e(number_format($payment->amount, 2)); ?></td>
                        <td><span class="method-badge"><?php echo e(ucfirst($payment->method ?? 'N/A')); ?></span></td>
                        <td class="reference-cell"><?php echo e($payment->reference ?? '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo e($payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger')); ?>">
                                <?php echo e(ucfirst($payment->status)); ?>

                            </span>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-title">No payments found</div>
                                <div class="empty-state-text">Payment records will appear here once transactions are made.</div>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterPayments(status, btn) {
    const rows = document.querySelectorAll('#paymentsTable tbody tr[data-status]');
    const buttons = document.querySelectorAll('.filter-btn');
    
    buttons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    rows.forEach(row => {
        const rowStatus = row.dataset.status;
        if (status === 'all' || rowStatus === status) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\admin\payments.blade.php ENDPATH**/ ?>