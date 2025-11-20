

<?php $__env->startSection('title', 'Enrollment Requests Management'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
?>

<style>
    .enrollment-requests-container {
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
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card.pending {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    
    .stat-card.approved {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .stat-card.rejected {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 0.95rem;
        opacity: 0.9;
    }
    
    .filter-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0;
    }
    
    .filter-tab {
        padding: 12px 24px;
        background: none;
        border: none;
        color: #6b7280;
        font-weight: 600;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }
    
    .filter-tab:hover {
        color: #3b82f6;
    }
    
    .filter-tab.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }
    
    .requests-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
        margin-top: 20px;
    }
    
    .requests-table thead th {
        background: #f9fafb;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .requests-table tbody tr {
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .requests-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .requests-table tbody td {
        padding: 18px 15px;
        vertical-align: middle;
    }
    
    .requests-table tbody tr td:first-child {
        border-radius: 8px 0 0 8px;
    }
    
    .requests-table tbody tr td:last-child {
        border-radius: 0 8px 8px 0;
    }
    
    .learner-info {
        display: flex;
        flex-direction: column;
    }
    
    .learner-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 3px;
    }
    
    .learner-email {
        font-size: 0.85rem;
        color: #9ca3af;
    }
    
    .course-info {
        display: flex;
        flex-direction: column;
    }
    
    .course-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 3px;
    }
    
    .course-type {
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .payment-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .payment-pending {
        background: #fef3c7;
        color: #78350f;
    }
    
    .payment-on_hold {
        background: #dbeafe;
        color: #1e3a8a;
    }
    
    .payment-paid {
        background: #d1fae5;
        color: #065f46;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, opacity 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }
    
    .btn-approve {
        background: #10b981;
        color: white;
    }
    
    .btn-reject {
        background: #ef4444;
        color: white;
    }
    
    .btn-view {
        background: #3b82f6;
        color: white;
    }
    
    .no-requests {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    
    .no-requests-icon {
        font-size: 4rem;
        margin-bottom: 15px;
    }
    
    .no-requests-text {
        font-size: 1.2rem;
    }
    
    .date-text {
        font-size: 0.9rem;
        color: #6b7280;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        font-weight: 500;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
</style>

<div class="enrollment-requests-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Enrollment Requests</h1>
            <p class="page-subtitle">Review and manage guest enrollment requests for <?php echo e($schoolName); ?></p>
        </div>
    </div>
    
    <!-- Alert Messages -->
    <?php if(session('success')): ?>
        <div class="alert alert-success">
            ✓ <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        <div class="alert alert-error">
            ✗ <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>
    
    <?php
        $allRequests = \App\Models\EnrollmentRequest::with(['learner', 'course', 'approvedBy'])
            ->where('school_id', $school->id)
            ->latest()
            ->get();
        
        $pendingRequests = $allRequests->where('status', 'pending');
        $approvedRequests = $allRequests->where('status', 'approved');
        $rejectedRequests = $allRequests->where('status', 'rejected');
    ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo e($allRequests->count()); ?></div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-number"><?php echo e($pendingRequests->count()); ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card approved">
            <div class="stat-number"><?php echo e($approvedRequests->count()); ?></div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="stat-card rejected">
            <div class="stat-number"><?php echo e($rejectedRequests->count()); ?></div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>
    
    <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterRequests('all')">All Requests</button>
        <button class="filter-tab" onclick="filterRequests('pending')">Pending</button>
        <button class="filter-tab" onclick="filterRequests('approved')">Approved</button>
        <button class="filter-tab" onclick="filterRequests('rejected')">Rejected</button>
    </div>
    
    <?php if($allRequests->count() > 0): ?>
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Learner</th>
                    <th>Course</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $allRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr data-status="<?php echo e($request->status); ?>">
                        <td>
                            <div class="learner-info">
                                <div class="learner-name"><?php echo e($request->learner->name); ?></div>
                                <div class="learner-email"><?php echo e($request->learner->email); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="course-info">
                                <div class="course-name"><?php echo e($request->course->title ?? 'N/A'); ?></div>
                                <div class="course-type"><?php echo e(ucfirst($request->course->type ?? 'standard')); ?></div>
                            </div>
                        </td>
                        <td>
                            <strong>₱<?php echo e(number_format($request->course->price ?? 0, 2)); ?></strong>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo e($request->status); ?>">
                                <?php echo e(ucfirst($request->status)); ?>

                            </span>
                        </td>
                        <td>
                            <span class="payment-badge payment-<?php echo e($request->payment_status); ?>">
                                <?php echo e(ucfirst(str_replace('_', ' ', $request->payment_status))); ?>

                            </span>
                        </td>
                        <td>
                            <div class="date-text">
                                <?php echo e($request->created_at->format('M d, Y')); ?><br>
                                <small><?php echo e($request->created_at->format('h:i A')); ?></small>
                            </div>
                        </td>
                        <td>
                            <?php if($request->status === 'pending'): ?>
                                <div class="action-buttons">
                                    <form method="POST" action="<?php echo e(route('schools.admin.enrollmentRequests.approve', ['school' => $school, 'enrollmentRequest' => $request->id])); ?>" style="display: inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-approve" onclick="return confirm('Approve this enrollment request? This will promote the guest to a student.')">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-reject" onclick="showRejectModal(<?php echo e($request->id); ?>)">
                                        ✗ Reject
                                    </button>
                                </div>
                            <?php else: ?>
                                <span style="color: #9ca3af; font-size: 0.9rem;">
                                    <?php echo e(ucfirst($request->status)); ?>

                                    <?php if($request->approved_at): ?>
                                        <br><small><?php echo e($request->approved_at->format('M d, Y')); ?></small>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-requests">
            <div class="no-requests-icon">📋</div>
            <div class="no-requests-text">No enrollment requests yet</div>
        </div>
    <?php endif; ?>
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 15px; padding: 30px; max-width: 500px; width: 90%;">
        <h3 style="margin: 0 0 20px 0; color: #333;">Reject Enrollment Request</h3>
        <form id="rejectForm" method="POST">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom: 20px;">
                <label for="remarks" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Reason for Rejection *
                </label>
                <textarea id="remarks" name="remarks" rows="4" required 
                    style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit;"
                    placeholder="Provide a reason for rejecting this enrollment request..."></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeRejectModal()" 
                    style="padding: 10px 20px; background: #e5e7eb; color: #333; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" 
                    style="padding: 10px 20px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Reject Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function filterRequests(status) {
    const tabs = document.querySelectorAll('.filter-tab');
    const rows = document.querySelectorAll('.requests-table tbody tr');
    
    // Update active tab
    tabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter rows
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function showRejectModal(requestId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `<?php echo e(route('schools.admin.enrollmentRequests.reject', ['school' => $school, 'enrollmentRequest' => ':id'])); ?>`.replace(':id', requestId);
    modal.style.display = 'flex';
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.style.display = 'none';
    document.getElementById('remarks').value = '';
}

// Close modal when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/school/admin/enrollment-requests/index.blade.php ENDPATH**/ ?>