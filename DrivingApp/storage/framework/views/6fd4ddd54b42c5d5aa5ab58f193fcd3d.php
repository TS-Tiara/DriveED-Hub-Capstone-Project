

<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard Overview'); ?>

<?php $__env->startSection('content'); ?>
<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Schools</h3>
        <div class="value" style="color: #8b5cf6;"><?php echo e($stats['total_schools']); ?></div>
    </div>

    <div class="stat-card">
        <h3>Total Students</h3>
        <div class="value" style="color: #3b82f6;"><?php echo e($stats['total_students']); ?></div>
        <div class="subtext"><?php echo e($stats['active_students']); ?> active</div>
    </div>

    <div class="stat-card">
        <h3>Total Instructors</h3>
        <div class="value" style="color: #10b981;"><?php echo e($stats['total_instructors']); ?></div>
        <div class="subtext"><?php echo e($stats['active_instructors']); ?> active</div>
    </div>

    <div class="stat-card">
        <h3>Total Courses</h3>
        <div class="value" style="color: #f97316;"><?php echo e($stats['total_courses']); ?></div>
    </div>

    <div class="stat-card">
        <h3>Total Bookings</h3>
        <div class="value" style="color: #6366f1;"><?php echo e($stats['total_bookings']); ?></div>
        <div class="subtext"><?php echo e($stats['pending_bookings']); ?> pending</div>
    </div>

    <div class="stat-card">
        <h3>Completed Bookings</h3>
        <div class="value" style="color: #14b8a6;"><?php echo e($stats['completed_bookings']); ?></div>
    </div>

    <div class="stat-card">
        <h3>Total Revenue</h3>
        <div class="value" style="color: #059669;">₱<?php echo e(number_format($stats['total_revenue'], 2)); ?></div>
    </div>

    <div class="stat-card">
        <h3>Pending Payments</h3>
        <div class="value" style="color: #f59e0b;">₱<?php echo e(number_format($stats['pending_payments'], 2)); ?></div>
    </div>
</div>

<!-- Schools Overview -->
<div class="card">
    <div class="card-header">
        <h3>Schools Overview</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>School Name</th>
                        <th>Students</th>
                        <th>Instructors</th>
                        <th>Admins</th>
                        <th>Courses</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600;"><?php echo e($school->name); ?></div>
                            <div style="font-size: 0.75rem; color: #9ca3af;"><?php echo e($school->slug); ?></div>
                        </td>
                        <td><?php echo e($school->students_count); ?></td>
                        <td><?php echo e($school->instructors_count); ?></td>
                        <td><?php echo e($school->admins_count); ?></td>
                        <td><?php echo e($school->courses_count); ?></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="card">
    <div class="card-header">
        <h3>Recent System Activities</h3>
    </div>
    <div class="card-body">
        <?php $__currentLoopData = $recentActivities->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div style="display: flex; align-items: start; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #f3f4f6; margin-bottom: 16px;">
            <div style="width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; background: 
                <?php if($activity->level === 'critical' || $activity->level === 'error'): ?> #ef4444
                <?php elseif($activity->level === 'warning'): ?> #f59e0b
                <?php else: ?> #10b981
                <?php endif; ?>;">
            </div>
            <div style="flex: 1;">
                <div style="font-size: 0.875rem; color: #1f2937;"><?php echo e($activity->message); ?></div>
                <div style="margin-top: 4px; display: flex; gap: 8px; font-size: 0.75rem; color: #9ca3af;">
                    <span><?php echo e($activity->school ? $activity->school->name : 'System'); ?></span>
                    <span>•</span>
                    <span><?php echo e($activity->category); ?></span>
                    <span>•</span>
                    <span><?php echo e($activity->created_at->diffForHumans()); ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.system-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/system-admin/dashboard.blade.php ENDPATH**/ ?>