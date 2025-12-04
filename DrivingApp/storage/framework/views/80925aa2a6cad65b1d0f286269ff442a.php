
<?php $__env->startSection('title', 'Students'); ?>
<?php $__env->startSection('page-title', 'All Students'); ?>
<?php $__env->startSection('content'); ?>
<div class="filters">
    <form method="GET" class="filter-grid">
        <div class="form-group">
            <label>School</label>
            <select name="school_id" class="form-control">
                <option value="">All Schools</option>
                <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($school->id); ?>" <?php echo e(request('school_id') == $school->id ? 'selected' : ''); ?>><?php echo e($school->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">All</option>
                <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Active</option>
                <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>Inactive</option>
            </select>
        </div>
        <div class="form-group">
            <label>Search</label>
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Name or email" class="form-control">
        </div>
        <div class="form-group" style="display: flex; align-items: flex-end;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Filter</button>
        </div>
    </form>
</div>
<div class="card">
    <div class="card-header"><h3>Students (<?php echo e($students->total()); ?>)</h3></div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>School</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><strong><?php echo e($student->name); ?></strong></td>
                    <td><?php echo e($student->email); ?></td>
                    <td><?php echo e($student->school->name); ?></td>
                    <td><span class="badge <?php echo e($student->status === 'active' ? 'badge-success' : 'badge-secondary'); ?>"><?php echo e(ucfirst($student->status)); ?></span></td>
                    <td><?php echo e($student->created_at->format('M d, Y')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php echo e($students->appends(request()->query())->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.system-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\system-admin\students.blade.php ENDPATH**/ ?>