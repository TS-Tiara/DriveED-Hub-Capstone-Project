

<?php $__env->startSection('title', 'Payments & Transactions'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $schoolName = $school->name ?? 'Driving School';
?>

<style>
.payments-container {
    padding: 20px;
    margin: 20px auto;
    max-width: 1600px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #667eea;
}

.page-title {
    font-size: 2rem;
    color: #333;
    margin: 0;
}

.page-subtitle {
    color: #666;
    font-size: 0.95rem;
    margin-top: 5px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.stat-card.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.stat-card.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stat-card h3 {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: 10px;
}

.stat-card .value {
    font-size: 2rem;
    font-weight: 700;
}

.payments-filters {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 600;
}

.filter-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
}

.payments-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #f9fafb;
}

th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}

td {
    padding: 15px;
    border-bottom: 1px solid #e5e7eb;
}

tr:hover {
    background: #f9fafb;
}

.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-completed { background: #d1fae5; color: #065f46; }
.badge-pending { background: #fef3c7; color: #92400e; }
.badge-failed { background: #fee2e2; color: #991b1b; }

.method-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.85rem;
    background: #e0e7ff;
    color: #3730a3;
}

@media (max-width: 768px) {
    .payments-table {
        overflow-x: auto;
    }
    
    table {
        min-width: 800px;
    }
}
</style>

<div class="payments-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Payments & Transactions</h1>
            <p class="page-subtitle">Track and manage all payments for <?php echo e($schoolName); ?></p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Revenue</h3>
            <div class="value">₱<?php echo e(number_format($payments->where('status', 'completed')->sum('amount'), 2)); ?></div>
        </div>
            <div class="stat-card success">
                <h3>Completed Payments</h3>
                <div class="value"><?php echo e($payments->where('status', 'completed')->count()); ?></div>
            </div>
            <div class="stat-card warning">
                <h3>Pending Payments</h3>
                <div class="value"><?php echo e($payments->where('status', 'pending')->count()); ?></div>
            </div>
        </div>

        <div class="payments-filters">
            <button class="filter-btn active" data-filter="all" onclick="filterPayments('all')">All Payments</button>
            <button class="filter-btn" data-filter="completed" onclick="filterPayments('completed')">Completed</button>
        <button class="filter-btn" data-filter="pending" onclick="filterPayments('pending')">Pending</button>
        <button class="filter-btn" data-filter="failed" onclick="filterPayments('failed')">Failed</button>
    </div>

    <div class="payments-table">
        <table id="paymentsTable">
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
                    <td><strong><?php echo e($payment->booking->student->name); ?></strong></td>
                    <td><?php echo e($payment->booking->course->title); ?></td>
                    <td><strong style="color: #10b981;">₱<?php echo e(number_format($payment->amount, 2)); ?></strong></td>
                    <td><span class="method-badge"><?php echo e(ucfirst($payment->method ?? 'N/A')); ?></span></td>
                    <td style="font-family: monospace; font-size: 0.9rem;"><?php echo e($payment->reference ?? '-'); ?></td>
                    <td><span class="badge badge-<?php echo e($payment->status); ?>"><?php echo e(ucfirst($payment->status)); ?></span></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 60px 20px; color: #9ca3af;">
                        <p style="font-size: 1.2rem;">No payments found</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterPayments(status) {
    const rows = document.querySelectorAll('#paymentsTable tbody tr[data-status]');
    const buttons = document.querySelectorAll('.filter-btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
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