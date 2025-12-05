
<?php $__env->startSection('title', 'System Logs'); ?>
<?php $__env->startSection('page-title', 'System Logs'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .logs-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .logs-header h2 {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.5rem;
        color: #1f2937;
    }
    
    .logs-header h2 i {
        color: #053d86;
    }
    
    /* Stats Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    @media (max-width: 1200px) {
        .stats-row {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    .stat-box {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-left: 4px solid;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .stat-box.critical { border-color: #dc2626; }
    .stat-box.warning { border-color: #f59e0b; }
    .stat-box.info { border-color: #3b82f6; }
    .stat-box.success { border-color: #10b981; }
    .stat-box.total { border-color: #053d86; }
    
    .stat-box .number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
    }
    
    .stat-box.critical .number { color: #dc2626; }
    .stat-box.warning .number { color: #f59e0b; }
    .stat-box.info .number { color: #3b82f6; }
    .stat-box.success .number { color: #10b981; }
    .stat-box.total .number { color: #053d86; }
    
    .stat-box .label {
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 500;
    }
    
    /* Filters Panel */
    .filters-panel {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .filters-panel h3 {
        font-size: 1rem;
        color: #374151;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .filters-panel h3 i {
        color: #053d86;
    }
    
    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }
    
    .filter-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 0.6rem 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: white;
    }
    
    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: #053d86;
        box-shadow: 0 0 0 3px rgba(5, 61, 134, 0.1);
    }
    
    .filter-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    
    .btn-filter {
        padding: 0.6rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-filter.primary {
        background: #053d86;
        color: white;
    }
    
    .btn-filter.primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(5, 61, 134, 0.4);
        background: #0a4a9e;
    }
    
    .btn-filter.secondary {
        background: #f3f4f6;
        color: #4b5563;
    }
    
    .btn-filter.secondary:hover {
        background: #e5e7eb;
    }
    
    /* Logs Table */
    .logs-table-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    
    .logs-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .logs-table thead {
        background: #f8fafc;
    }
    
    .logs-table th {
        padding: 1rem;
        text-align: left;
        font-size: 0.8rem;
        font-weight: 700;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .logs-table td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.9rem;
        color: #374151;
    }
    
    .logs-table tbody tr {
        transition: background-color 0.15s;
    }
    
    .logs-table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    /* Level Badges */
    .level-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .level-badge.emergency,
    .level-badge.critical {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
    }
    
    .level-badge.alert,
    .level-badge.error {
        background: linear-gradient(135deg, #ffedd5, #fed7aa);
        color: #9a3412;
    }
    
    .level-badge.warning {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
    }
    
    .level-badge.notice,
    .level-badge.info {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1e40af;
    }
    
    .level-badge.debug {
        background: #f3f4f6;
        color: #4b5563;
    }
    
    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-badge.resolved {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
    }
    
    .status-badge.unresolved {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
    }
    
    /* Category Badge */
    .category-badge {
        display: inline-block;
        padding: 0.3rem 0.6rem;
        background: #f3f4f6;
        border-radius: 6px;
        font-size: 0.8rem;
        color: #4b5563;
        font-weight: 500;
    }
    
    /* Log Message */
    .log-message {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #1f2937;
    }
    
    /* Time Display */
    .log-time {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }
    
    .log-time .date {
        font-weight: 600;
        color: #374151;
    }
    
    .log-time .time {
        font-size: 0.8rem;
        color: #6b7280;
    }
    
    /* View Button */
    .view-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1rem;
        background: #053d86;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .view-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(5, 61, 134, 0.4);
        background: #0a4a9e;
    }
    
    /* Empty State */
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }
    
    .empty-state h3 {
        font-size: 1.25rem;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #6b7280;
    }
    
    /* Pagination */
    .pagination-wrapper {
        padding: 1.5rem;
        display: flex;
        justify-content: center;
        border-top: 1px solid #f3f4f6;
    }
    
    /* School Info */
    .school-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .school-info .school-icon {
        width: 28px;
        height: 28px;
        background: #053d86;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    .school-info .school-name {
        font-weight: 500;
    }
    
    .school-info.system .school-icon {
        background: #042e66;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="logs-header">
    <h2>
        <i class="fas fa-clipboard-list"></i>
        System Activity Logs
    </h2>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-box critical">
        <div class="number"><?php echo e($stats['critical']); ?></div>
        <div class="label">Critical Errors</div>
    </div>
    <div class="stat-box warning">
        <div class="number"><?php echo e($stats['unresolved']); ?></div>
        <div class="label">Unresolved</div>
    </div>
    <div class="stat-box success">
        <div class="number"><?php echo e($stats['today']); ?></div>
        <div class="label">Today</div>
    </div>
    <div class="stat-box info">
        <div class="number"><?php echo e($stats['this_week']); ?></div>
        <div class="label">This Week</div>
    </div>
    <div class="stat-box total">
        <div class="number"><?php echo e($stats['total']); ?></div>
        <div class="label">Total Logs</div>
    </div>
</div>

<!-- Filters Panel -->
<div class="filters-panel">
    <h3><i class="fas fa-filter"></i> Filter Logs</h3>
    <form method="GET">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Level</label>
                <select name="level">
                    <option value="">All Levels</option>
                    <option value="emergency" <?php echo e(request('level') === 'emergency' ? 'selected' : ''); ?>>Emergency</option>
                    <option value="critical" <?php echo e(request('level') === 'critical' ? 'selected' : ''); ?>>Critical</option>
                    <option value="alert" <?php echo e(request('level') === 'alert' ? 'selected' : ''); ?>>Alert</option>
                    <option value="error" <?php echo e(request('level') === 'error' ? 'selected' : ''); ?>>Error</option>
                    <option value="warning" <?php echo e(request('level') === 'warning' ? 'selected' : ''); ?>>Warning</option>
                    <option value="notice" <?php echo e(request('level') === 'notice' ? 'selected' : ''); ?>>Notice</option>
                    <option value="info" <?php echo e(request('level') === 'info' ? 'selected' : ''); ?>>Info</option>
                    <option value="debug" <?php echo e(request('level') === 'debug' ? 'selected' : ''); ?>>Debug</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="database" <?php echo e(request('category') === 'database' ? 'selected' : ''); ?>>Database</option>
                    <option value="authentication" <?php echo e(request('category') === 'authentication' ? 'selected' : ''); ?>>Authentication</option>
                    <option value="authorization" <?php echo e(request('category') === 'authorization' ? 'selected' : ''); ?>>Authorization</option>
                    <option value="validation" <?php echo e(request('category') === 'validation' ? 'selected' : ''); ?>>Validation</option>
                    <option value="file_upload" <?php echo e(request('category') === 'file_upload' ? 'selected' : ''); ?>>File Upload</option>
                    <option value="system" <?php echo e(request('category') === 'system' ? 'selected' : ''); ?>>System</option>
                    <option value="other" <?php echo e(request('category') === 'other' ? 'selected' : ''); ?>>Other</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>School</label>
                <select name="school_id">
                    <option value="">All Schools</option>
                    <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($school->id); ?>" <?php echo e(request('school_id') == $school->id ? 'selected' : ''); ?>>
                            <?php echo e($school->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Status</label>
                <select name="resolved">
                    <option value="">All Status</option>
                    <option value="no" <?php echo e(request('resolved') === 'no' ? 'selected' : ''); ?>>Unresolved</option>
                    <option value="yes" <?php echo e(request('resolved') === 'yes' ? 'selected' : ''); ?>>Resolved</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>From Date</label>
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>">
            </div>
            
            <div class="filter-group">
                <label>To Date</label>
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>">
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn-filter primary">
                <i class="fas fa-search"></i> Apply Filters
            </button>
            <a href="<?php echo e(route('system-admin.logs')); ?>" class="btn-filter secondary">
                <i class="fas fa-times"></i> Clear
            </a>
        </div>
    </form>
</div>

<!-- Logs Table -->
<div class="logs-table-wrapper">
    <?php if($logs->count() > 0): ?>
        <table class="logs-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Level</th>
                    <th>Category</th>
                    <th>School</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <div class="log-time">
                                <span class="date"><?php echo e($log->created_at->format('M d, Y')); ?></span>
                                <span class="time"><?php echo e($log->created_at->format('h:i A')); ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="level-badge <?php echo e($log->level); ?>">
                                <?php switch($log->level):
                                    case ('emergency'): ?>
                                    <?php case ('critical'): ?>
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php break; ?>
                                    <?php case ('alert'): ?>
                                    <?php case ('error'): ?>
                                        <i class="fas fa-times-circle"></i>
                                        <?php break; ?>
                                    <?php case ('warning'): ?>
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <?php break; ?>
                                    <?php case ('notice'): ?>
                                    <?php case ('info'): ?>
                                        <i class="fas fa-info-circle"></i>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <i class="fas fa-bug"></i>
                                <?php endswitch; ?>
                                <?php echo e(ucfirst($log->level)); ?>

                            </span>
                        </td>
                        <td>
                            <span class="category-badge"><?php echo e(ucfirst(str_replace('_', ' ', $log->category))); ?></span>
                        </td>
                        <td>
                            <?php if($log->school): ?>
                                <div class="school-info">
                                    <div class="school-icon"><?php echo e(strtoupper(substr($log->school->name, 0, 2))); ?></div>
                                    <span class="school-name"><?php echo e($log->school->name); ?></span>
                                </div>
                            <?php else: ?>
                                <div class="school-info system">
                                    <div class="school-icon"><i class="fas fa-cog"></i></div>
                                    <span class="school-name">System</span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="log-message" title="<?php echo e($log->message); ?>">
                                <?php echo e($log->message); ?>

                            </div>
                        </td>
                        <td>
                            <?php if($log->resolved_at): ?>
                                <span class="status-badge resolved">
                                    <i class="fas fa-check-circle"></i> Resolved
                                </span>
                            <?php else: ?>
                                <span class="status-badge unresolved">
                                    <i class="fas fa-clock"></i> Open
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo e(route('system-admin.logs.show', $log)); ?>" class="view-btn">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        
        <div class="pagination-wrapper">
            <?php echo e($logs->appends(request()->query())->links()); ?>

        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-check"></i>
            <h3>No logs found</h3>
            <p>No system logs match your current filters. Try adjusting your search criteria.</p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.system-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\system-admin\logs.blade.php ENDPATH**/ ?>