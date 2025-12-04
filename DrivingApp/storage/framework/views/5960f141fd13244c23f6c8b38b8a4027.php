
<?php $__env->startSection('title', 'School Admins'); ?>
<?php $__env->startSection('page-title', 'School Administrators'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px 20px;
        background: #f8f9fa;
        border-radius: 8px;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .search-box {
        position: relative;
        flex: 1;
        max-width: 350px;
    }
    
    .search-box input {
        width: 100%;
        padding: 10px 40px 10px 15px;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.3s;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: #053d86;
    }
    
    .search-box button {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: #053d86;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 6px 12px;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .search-box button:hover {
        background: #0a4a9e;
    }

    .filter-select {
        padding: 10px 15px;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        font-size: 0.95rem;
        min-width: 200px;
    }

    .filter-select:focus {
        outline: none;
        border-color: #053d86;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        background: #053d86;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1rem;
    }

    .school-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #e0e7ff;
        color: #3730a3;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .btn-primary {
        background: #053d86;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
    }

    .btn-primary:hover {
        background: #0a4a9e;
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }

    .modal-header {
        padding: 20px 25px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        color: #1f2937;
        font-size: 1.25rem;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #6b7280;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }

    .modal-close:hover {
        color: #1f2937;
    }

    .modal-body {
        padding: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #053d86;
    }

    .modal-footer {
        padding: 20px 25px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    /* Action Buttons */
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
        border-radius: 6px;
        cursor: pointer;
        border: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }

    .btn-toggle {
        background: #f3f4f6;
        color: #374151;
    }

    .btn-toggle:hover {
        background: #e5e7eb;
    }

    .btn-toggle.btn-active {
        background: #dcfce7;
        color: #166534;
    }

    .btn-toggle.btn-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-danger:hover {
        background: #fecaca;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .status-badge.active {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .actions-cell {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    /* Delete Modal */
    .modal-danger .modal-header {
        background: #fef2f2;
        border-bottom: 1px solid #fecaca;
    }

    .modal-danger .modal-header h3 {
        color: #dc2626;
    }

    .btn-delete {
        background: #dc2626;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
    }

    .btn-delete:hover {
        background: #b91c1c;
    }

    .warning-text {
        background: #fef3c7;
        color: #92400e;
        padding: 12px 15px;
        border-radius: 8px;
        margin-top: 15px;
        font-size: 0.9rem;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Action Bar -->
<div class="action-bar">
    <form method="GET" class="search-box">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search by name or email...">
        <?php if(request('school_id')): ?>
            <input type="hidden" name="school_id" value="<?php echo e(request('school_id')); ?>">
        <?php endif; ?>
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    
    <div style="display: flex; gap: 10px; align-items: center;">
        <form method="GET">
            <?php if(request('search')): ?>
                <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
            <?php endif; ?>
            <select name="school_id" class="filter-select" onchange="this.form.submit()">
                <option value="">All Schools</option>
                <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($school->id); ?>" <?php echo e(request('school_id') == $school->id ? 'selected' : ''); ?>>
                        <?php echo e($school->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>
        
        <button type="button" class="btn-primary" onclick="openModal('createAdminModal')">
            <i class="fas fa-plus"></i> Add School Admin
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-user-tie" style="margin-right: 0.5rem; color: #053d86;"></i>
            School Admins (<?php echo e($admins->total()); ?>)
        </h3>
    </div>
    <div class="card-body">
        <?php if($admins->count() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>School</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div class="user-avatar">
                                <?php echo e(strtoupper(substr($admin->name, 0, 1))); ?>

                            </div>
                            <strong><?php echo e($admin->name); ?></strong>
                        </div>
                    </td>
                    <td><?php echo e($admin->email); ?></td>
                    <td>
                        <?php if($admin->school): ?>
                            <span class="school-badge">
                                <i class="fas fa-school"></i>
                                <?php echo e($admin->school->name); ?>

                            </span>
                        <?php else: ?>
                            <span style="color: #9ca3af;">No school</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge <?php echo e($admin->is_active ? 'active' : 'inactive'); ?>">
                            <i class="fas fa-circle" style="font-size: 6px;"></i>
                            <?php echo e($admin->is_active ? 'Active' : 'Inactive'); ?>

                        </span>
                    </td>
                    <td><?php echo e($admin->created_at->format('M d, Y')); ?></td>
                    <td>
                        <div class="actions-cell">
                            <form action="<?php echo e(route('system-admin.admins.toggle-status', $admin->id)); ?>" method="POST" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <button type="submit" class="btn-sm btn-toggle <?php echo e($admin->is_active ? 'btn-active' : 'btn-inactive'); ?>" 
                                        title="<?php echo e($admin->is_active ? 'Deactivate' : 'Activate'); ?>">
                                    <i class="fas <?php echo e($admin->is_active ? 'fa-toggle-on' : 'fa-toggle-off'); ?>"></i>
                                    <?php echo e($admin->is_active ? 'Deactivate' : 'Activate'); ?>

                                </button>
                            </form>
                            <button type="button" class="btn-sm btn-danger" 
                                    onclick="confirmDeleteAdmin('<?php echo e($admin->id); ?>', '<?php echo e($admin->name); ?>')"
                                    title="Delete Admin">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php echo e($admins->appends(request()->query())->links()); ?>

        <?php else: ?>
        <div style="text-align: center; padding: 3rem; color: #6b7280;">
            <i class="fas fa-user-tie" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
            <p>No school admins found.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create School Admin Modal -->
<div class="modal-overlay" id="createAdminModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus" style="color: #053d86; margin-right: 8px;"></i>Add School Admin</h3>
            <button type="button" class="modal-close" onclick="closeModal('createAdminModal')">&times;</button>
        </div>
        <form action="<?php echo e(route('system-admin.admins.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="school_id">Assign to School <span style="color: #dc2626;">*</span></label>
                    <select name="school_id" id="school_id" required>
                        <option value="">Select a school...</option>
                        <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($school->id); ?>"><?php echo e($school->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="name">Admin Name <span style="color: #dc2626;">*</span></label>
                    <input type="text" name="name" id="name" required placeholder="Enter admin name">
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span style="color: #dc2626;">*</span></label>
                    <input type="email" name="email" id="email" required placeholder="admin@example.com">
                </div>
                <div class="form-group">
                    <label for="password">Password <span style="color: #dc2626;">*</span></label>
                    <input type="password" name="password" id="password" required placeholder="Minimum 8 characters" minlength="8">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password <span style="color: #dc2626;">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Confirm password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('createAdminModal')">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Create Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Admin Confirmation Modal -->
<div class="modal-overlay" id="deleteAdminModal">
    <div class="modal-content modal-danger">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>Delete Admin</h3>
            <button type="button" class="modal-close" onclick="closeModal('deleteAdminModal')">&times;</button>
        </div>
        <form id="deleteAdminForm" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteAdminName"></strong>?</p>
                <div class="warning-text">
                    <i class="fas fa-exclamation-triangle"></i>
                    This action cannot be undone. The admin will be permanently removed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('deleteAdminModal')">Cancel</button>
                <button type="submit" class="btn-delete"><i class="fas fa-trash"></i> Delete Admin</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function confirmDeleteAdmin(id, name) {
    document.getElementById('deleteAdminName').textContent = name;
    document.getElementById('deleteAdminForm').action = '<?php echo e(url("system-admin/admins")); ?>/' + id;
    openModal('deleteAdminModal');
}

// Close modal when clicking outside
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.system-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/system-admin/admins.blade.php ENDPATH**/ ?>