

<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Platform Overview'); ?>

<?php $__env->startSection('content'); ?>
<!-- Platform Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Registered Schools</h3>
        <div class="value" style="color: #053d86;"><?php echo e($stats['total_schools']); ?></div>
        <div class="subtext">Driving schools on platform</div>
    </div>

    <div class="stat-card">
        <h3>School Admins</h3>
        <div class="value" style="color: #0a4a9e;"><?php echo e($stats['total_school_admins']); ?></div>
        <div class="subtext">Managing their schools</div>
    </div>

    <div class="stat-card">
        <h3>Total Users</h3>
        <div class="value" style="color: #10b981;"><?php echo e($stats['total_users']); ?></div>
        <div class="subtext"><?php echo e($stats['total_students']); ?> students, <?php echo e($stats['total_instructors']); ?> instructors</div>
    </div>

    <div class="stat-card">
        <h3>System Logs</h3>
        <div class="value" style="color: #f97316;"><?php echo e($stats['total_logs']); ?></div>
        <div class="subtext"><?php echo e($stats['error_logs']); ?> errors, <?php echo e($stats['warning_logs']); ?> warnings</div>
    </div>
</div>

<!-- Schools Overview -->
<div class="card">
    <div class="card-header">
        <h3>Registered Schools</h3>
        <a href="<?php echo e(route('system-admin.schools')); ?>" class="btn btn-primary" style="font-size: 0.875rem;">View All</a>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>School Name</th>
                        <th>Slug</th>
                        <th>Students</th>
                        <th>Instructors</th>
                        <th>Admins</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600;"><?php echo e($school->name); ?></div>
                        </td>
                        <td>
                            <code style="background: #f3f4f6; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;"><?php echo e($school->slug); ?></code>
                        </td>
                        <td><?php echo e($school->students_count); ?></td>
                        <td><?php echo e($school->instructors_count); ?></td>
                        <td><?php echo e($school->admins_count); ?></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #9ca3af;">No schools registered yet</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent System Activities -->
<div class="card">
    <div class="card-header">
        <h3>Recent System Logs</h3>
        <a href="<?php echo e(route('system-admin.logs')); ?>" class="btn btn-primary" style="font-size: 0.875rem;">View All Logs</a>
    </div>
    <div class="card-body">
        <?php $__empty_1 = true; $__currentLoopData = $recentActivities->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div style="display: flex; align-items: start; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #f3f4f6; margin-bottom: 16px;">
            <div style="width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; flex-shrink: 0; background: 
                <?php if($activity->level === 'critical' || $activity->level === 'error'): ?> #ef4444
                <?php elseif($activity->level === 'warning'): ?> #f59e0b
                <?php else: ?> #10b981
                <?php endif; ?>;">
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 0.875rem; color: #1f2937; word-break: break-word;"><?php echo e(Str::limit($activity->message, 100)); ?></div>
                <div style="margin-top: 4px; display: flex; flex-wrap: wrap; gap: 8px; font-size: 0.75rem; color: #9ca3af;">
                    <span class="badge" style="background: #f3f4f6; color: #6b7280;"><?php echo e($activity->level); ?></span>
                    <span><?php echo e($activity->school ? $activity->school->name : 'System'); ?></span>
                    <span>•</span>
                    <span><?php echo e($activity->category); ?></span>
                    <span>•</span>
                    <span><?php echo e($activity->created_at->diffForHumans()); ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p style="text-align: center; color: #9ca3af; padding: 20px;">No recent activity logs</p>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.system-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\system-admin\dashboard.blade.php ENDPATH**/ ?>