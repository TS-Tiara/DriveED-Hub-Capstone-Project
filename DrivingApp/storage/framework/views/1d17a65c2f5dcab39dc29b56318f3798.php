<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log #<?php echo e($log->id); ?> - System Administrator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 2rem;
        }

        .back-btn {
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .log-detail {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 20px;
        }

        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 14px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-right: 10px;
        }

        .badge-emergency,
        .badge-critical {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-alert,
        .badge-error {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-info,
        .badge-notice {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-debug {
            background: #e5e7eb;
            color: #374151;
        }

        .badge-resolved {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-unresolved {
            background: #fee2e2;
            color: #991b1b;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }

        .info-item label {
            display: block;
            font-weight: 600;
            color: #6b7280;
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .info-item p {
            color: #111827;
            font-size: 1rem;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h3 {
            font-size: 1.2rem;
            color: #374151;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }

        .message-box {
            padding: 20px;
            background: #f9fafb;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .code-block {
            background: #1f2937;
            color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .context-table {
            width: 100%;
            border-collapse: collapse;
        }

        .context-table th,
        .context-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .context-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .resolve-form {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }

        .resolve-form h3 {
            margin-bottom: 15px;
            color: #374151;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.95rem;
            resize: vertical;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .resolved-info {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .resolved-info h4 {
            color: #065f46;
            margin-bottom: 10px;
        }

        .resolved-info p {
            color: #047857;
            margin-bottom: 5px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-file-alt"></i> Log #<?php echo e($log->id); ?> Details</h1>
                <p style="margin-top: 5px; opacity: 0.9;"><i class="fas fa-user"></i> <?php echo e(Auth::guard('admin')->user()->name); ?></p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="<?php echo e(route('system-admin.logs')); ?>" class="back-btn">← Back to Logs</a>
                <form method="POST" action="<?php echo e(route('system-admin.logout')); ?>" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="back-btn" style="border: none; cursor: pointer;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-error">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="log-detail">
            <!-- Header -->
            <div class="log-header">
                <div>
                    <span class="badge badge-<?php echo e($log->level); ?>"><?php echo e(strtoupper($log->level)); ?></span>
                    <span class="badge badge-<?php echo e($log->resolved_at ? 'resolved' : 'unresolved'); ?>">
                        <?php echo e($log->resolved_at ? 'Resolved' : 'Unresolved'); ?>

                    </span>
                </div>
                <div>
                    <strong><?php echo e($log->created_at->format('F d, Y H:i:s')); ?></strong>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="info-grid">
                <div class="info-item">
                    <label>Category</label>
                    <p><?php echo e(ucfirst($log->category)); ?></p>
                </div>
                
                <?php if($log->school): ?>
                    <div class="info-item">
                        <label>School</label>
                        <p><?php echo e($log->school->name); ?></p>
                    </div>
                <?php endif; ?>

                <?php if($log->user): ?>
                    <div class="info-item">
                        <label>User</label>
                        <p><?php echo e($log->user->name); ?> (<?php echo e(ucfirst($log->user_type)); ?>)</p>
                    </div>
                <?php endif; ?>

                <?php if($log->action): ?>
                    <div class="info-item">
                        <label>Action</label>
                        <p><?php echo e($log->action); ?></p>
                    </div>
                <?php endif; ?>

                <div class="info-item">
                    <label>IP Address</label>
                    <p><?php echo e($log->ip_address ?? 'N/A'); ?></p>
                </div>

                <div class="info-item">
                    <label>Method</label>
                    <p><?php echo e($log->method ?? 'N/A'); ?></p>
                </div>
            </div>

            <!-- Message -->
            <div class="section">
                <h3>Error Message</h3>
                <div class="message-box">
                    <?php echo e($log->message); ?>

                </div>
            </div>

            <!-- URL -->
            <?php if($log->url): ?>
                <div class="section">
                    <h3>Request URL</h3>
                    <div class="message-box">
                        <?php echo e($log->url); ?>

                    </div>
                </div>
            <?php endif; ?>

            <!-- Exception -->
            <?php if($log->exception_class): ?>
                <div class="section">
                    <h3>Exception Class</h3>
                    <div class="message-box">
                        <?php echo e($log->exception_class); ?>

                    </div>
                </div>
            <?php endif; ?>

            <!-- Stack Trace -->
            <?php if($log->stack_trace): ?>
                <div class="section">
                    <h3>Stack Trace</h3>
                    <div class="code-block"><?php echo e($log->stack_trace); ?></div>
                </div>
            <?php endif; ?>

            <!-- Context -->
            <?php if($log->context && count($log->context) > 0): ?>
                <div class="section">
                    <h3>Additional Context</h3>
                    <table class="context-table">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $log->context; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><strong><?php echo e($key); ?></strong></td>
                                    <td><?php echo e(is_array($value) ? json_encode($value) : $value); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- User Agent -->
            <?php if($log->user_agent): ?>
                <div class="section">
                    <h3>User Agent</h3>
                    <div class="message-box" style="font-size: 0.9rem;">
                        <?php echo e($log->user_agent); ?>

                    </div>
                </div>
            <?php endif; ?>

            <!-- Resolution -->
            <?php if($log->resolved_at): ?>
                <div class="resolved-info">
                    <h4><i class="fas fa-check-circle"></i> This log has been resolved</h4>
                    <p><strong>Resolved by:</strong> <?php echo e($log->resolvedBy->name ?? 'Unknown'); ?></p>
                    <p><strong>Resolved at:</strong> <?php echo e($log->resolved_at->format('F d, Y H:i:s')); ?></p>
                    <?php if($log->resolution_notes): ?>
                        <p><strong>Notes:</strong> <?php echo e($log->resolution_notes); ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="section">
                    <div class="resolve-form">
                        <h3>Mark as Resolved</h3>
                        <form method="POST" action="<?php echo e(route('system-admin.logs.resolve', $log)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label for="resolution_notes">Resolution Notes (Optional)</label>
                                <textarea 
                                    id="resolution_notes" 
                                    name="resolution_notes" 
                                    rows="4"
                                    placeholder="Describe what was done to resolve this issue..."
                                ></textarea>
                            </div>
                            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Mark as Resolved</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\system-admin\log-detail.blade.php ENDPATH**/ ?>