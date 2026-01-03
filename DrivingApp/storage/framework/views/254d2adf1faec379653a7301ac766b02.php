

<?php $__env->startSection('title', 'Manage Enrollments'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $school->schoolSetting->primary_color ?? '#667eea';
?>

<?php echo $__env->make('school.admin.partials.admin-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
        border-bottom: 3px solid <?php echo e($primaryColor); ?>;
    }
    
    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    
    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
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
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid transparent;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }
    
    .stat-card.active {
        border-color: #ffffff;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        transform: scale(1.05);
    }
    
    .stat-card.active::before {
        content: '';
        position: absolute;
        top: 15px;
        right: 15px;
        width: 12px;
        height: 12px;
        background: #ffffff;
        border-radius: 50%;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
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
    
    .status-completed {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-cancelled {
        background: #f3f4f6;
        color: #374151;
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
</style>

<div class="enrollment-requests-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Manage Enrollments</h1>
            <p class="page-subtitle">View and manage all enrollment requests and active student enrollments</p>
        </div>
    </div>
    
    <!-- Alert Messages -->
    <?php if(session('success')): ?>
    <div class="flash-message success">
        <div class="flash-icon">✓</div>
        <div class="flash-content">
            <div class="flash-title">Success!</div>
            <div class="flash-text"><?php echo e(session('success')); ?></div>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <div class="flash-message error">
        <div class="flash-icon">✕</div>
        <div class="flash-content">
            <div class="flash-title">Error!</div>
            <div class="flash-text"><?php echo e(session('error')); ?></div>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
    <?php endif; ?>
    
    <?php
        $allRequests = \App\Models\EnrollmentRequest::with(['learner', 'course', 'approvedBy'])
            ->where('school_id', $school->id)
            ->latest()
            ->get();
        
        $pendingRequests = $allRequests->where('status', 'pending');
        $approvedRequests = $allRequests->where('status', 'approved');
        $completedRequests = $allRequests->where('status', 'completed');
        $cancelledRequests = $allRequests->where('status', 'cancelled');
        $rejectedRequests = $allRequests->where('status', 'rejected');
    ?>
    
    <div class="stats-grid">
        <div class="stat-card active" onclick="filterRequests('all', this)" data-status="all">
            <div class="stat-number"><?php echo e($allRequests->count()); ?></div>
            <div class="stat-label">All Enrollments</div>
        </div>
        <div class="stat-card pending" onclick="filterRequests('pending', this)" data-status="pending">
            <div class="stat-number"><?php echo e($pendingRequests->count()); ?></div>
            <div class="stat-label">Pending Approval</div>
        </div>
        <div class="stat-card approved" onclick="filterRequests('approved', this)" data-status="approved">
            <div class="stat-number"><?php echo e($approvedRequests->count()); ?></div>
            <div class="stat-label">Active</div>
        </div>
        <div class="stat-card completed" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" onclick="filterRequests('completed', this)" data-status="completed">
            <div class="stat-number"><?php echo e($completedRequests->count()); ?></div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card cancelled" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);" onclick="filterRequests('cancelled', this)" data-status="cancelled">
            <div class="stat-number"><?php echo e($cancelledRequests->count()); ?></div>
            <div class="stat-label">Cancelled</div>
        </div>
        <div class="stat-card rejected" onclick="filterRequests('rejected', this)" data-status="rejected">
            <div class="stat-number"><?php echo e($rejectedRequests->count()); ?></div>
            <div class="stat-label">Rejected</div>
        </div>
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
                                    <form method="POST" action="<?php echo e(route('schools.admin.enrollments.approve', ['school' => $school, 'enrollmentRequest' => $request->id])); ?>" style="display: inline;" id="approveForm<?php echo e($request->id); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="button" class="btn btn-approve" onclick="approveRequest(<?php echo e($request->id); ?>)">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-reject" onclick="showRejectModal(<?php echo e($request->id); ?>)">
                                        ✗ Reject
                                    </button>
                                </div>
                            <?php elseif($request->status === 'approved'): ?>
                                <div class="action-buttons">
                                    <form method="POST" action="<?php echo e(route('schools.admin.enrollments.complete', ['school' => $school, 'enrollmentRequest' => $request->id])); ?>" style="display: inline;" id="completeForm<?php echo e($request->id); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="button" class="btn btn-approve" onclick="completeEnrollment(<?php echo e($request->id); ?>)">
                                            ✓ Complete
                                        </button>
                                    </form>
                                    <button class="btn btn-reject" onclick="showCancelModal(<?php echo e($request->id); ?>)">
                                        ✗ Cancel
                                    </button>
                                </div>
                            <?php else: ?>
                                <span style="color: #9ca3af; font-size: 0.9rem;">
                                    <?php echo e(ucfirst($request->status)); ?>

                                    <?php if($request->approved_at): ?>
                                        <br><small><?php echo e($request->approved_at->format('M d, Y')); ?></small>
                                    <?php endif; ?>
                                    <?php if($request->completed_at): ?>
                                        <br><small><?php echo e($request->completed_at->format('M d, Y')); ?></small>
                                    <?php endif; ?>
                                    <?php if($request->cancelled_at): ?>
                                        <br><small><?php echo e($request->cancelled_at->format('M d, Y')); ?></small>
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

<!-- Cancel Modal -->
<div id="cancelModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 15px; padding: 30px; max-width: 500px; width: 90%;">
        <h3 style="margin: 0 0 20px 0; color: #333;">Cancel Enrollment</h3>
        <form id="cancelForm" method="POST">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom: 20px;">
                <label for="cancel_remarks" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Reason for Cancellation (optional)
                </label>
                <textarea id="cancel_remarks" name="remarks" rows="4" 
                    style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit;"
                    placeholder="Provide a reason for cancelling this enrollment..."></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeCancelModal()" 
                    style="padding: 10px 20px; background: #e5e7eb; color: #333; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" 
                    style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancel Enrollment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function filterRequests(status, cardElement) {
    const cards = document.querySelectorAll('.stat-card');
    const rows = document.querySelectorAll('.requests-table tbody tr');
    
    // Update active card
    cards.forEach(card => card.classList.remove('active'));
    cardElement.classList.add('active');
    
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
    form.action = `<?php echo e(route('schools.admin.enrollments.reject', ['school' => $school, 'enrollmentRequest' => ':id'])); ?>`.replace(':id', requestId);
    modal.style.display = 'flex';
}

function showCancelModal(requestId) {
    const modal = document.getElementById('cancelModal');
    const form = document.getElementById('cancelForm');
    form.action = `<?php echo e(route('schools.admin.enrollments.cancel', ['school' => $school, 'enrollmentRequest' => ':id'])); ?>`.replace(':id', requestId);
    modal.style.display = 'flex';
}

function approveRequest(requestId) {
    showConfirm({
        type: 'success',
        title: 'Approve Enrollment',
        message: 'Are you sure you want to approve this enrollment request? This will promote the guest to a student.',
        confirmText: 'Approve',
        onConfirm: function() {
            document.getElementById('approveForm' + requestId).submit();
        }
    });
}

function completeEnrollment(requestId) {
    showConfirm({
        type: 'success',
        title: 'Complete Enrollment',
        message: 'Are you sure you want to mark this enrollment as completed? The student has finished the course.',
        confirmText: 'Complete',
        onConfirm: function() {
            document.getElementById('completeForm' + requestId).submit();
        }
    });
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.style.display = 'none';
    document.getElementById('remarks').value = '';
}

function closeCancelModal() {
    const modal = document.getElementById('cancelModal');
    modal.style.display = 'none';
    document.getElementById('cancel_remarks').value = '';
}

// Close modals when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/school/admin/enrollment-requests/index.blade.php ENDPATH**/ ?>