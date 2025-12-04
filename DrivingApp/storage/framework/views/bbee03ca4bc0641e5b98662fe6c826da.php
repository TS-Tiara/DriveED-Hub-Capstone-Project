<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title'); ?> - System Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #053d86;
            --secondary-color: #0a4a9e;
            --accent-color: #0356b3;
            --sidebar-bg: #1f2937;
            --sidebar-text: #e5e7eb;
            --sidebar-hover: #374151;
            --sidebar-active: #053d86;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            background: #053d86;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
        }
        
        .sidebar-header p {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-top: 4px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background: var(--sidebar-hover);
            border-left-color: #053d86;
        }
        
        .menu-item.active {
            background: var(--sidebar-active);
            border-left-color: white;
            color: white;
        }
        
        .menu-item svg {
            width: 20px;
            height: 20px;
            margin-right: 12px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex: 1;
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: white;
            padding: 16px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h2 {
            font-size: 1.5rem;
            color: #1f2937;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .user-name {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .btn-logout {
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .btn-logout:hover {
            background: var(--accent-color);
        }
        
        /* Content Area */
        .content {
            padding: 24px;
        }
        
        /* Alert Messages */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
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
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .stat-card .subtext {
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        /* Card */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h3 {
            font-size: 1.125rem;
            color: #1f2937;
            margin: 0;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Table */
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f9fafb;
        }
        
        th {
            padding: 12px 16px;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        td {
            padding: 12px 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 0.875rem;
        }
        
        tr:hover {
            background: #f9fafb;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        
        /* Filters */
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--accent-color);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            gap: 8px;
            margin-top: 20px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            text-decoration: none;
            color: #374151;
        }
        
        .pagination a:hover {
            background: #f9fafb;
        }
        
        .pagination .active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>System Admin</h1>
                <p>Global Management Portal</p>
            </div>
            <nav class="sidebar-menu">
                <a href="<?php echo e(route('system-admin.dashboard')); ?>" class="menu-item <?php echo e(request()->routeIs('system-admin.dashboard') ? 'active' : ''); ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>
                <a href="<?php echo e(route('system-admin.schools')); ?>" class="menu-item <?php echo e(request()->routeIs('system-admin.schools') ? 'active' : ''); ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Schools
                </a>
                <a href="<?php echo e(route('system-admin.admins')); ?>" class="menu-item <?php echo e(request()->routeIs('system-admin.admins') ? 'active' : ''); ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    School Admins
                </a>
                <a href="<?php echo e(route('system-admin.users')); ?>" class="menu-item <?php echo e(request()->routeIs('system-admin.users') ? 'active' : ''); ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Users
                </a>
                <a href="<?php echo e(route('system-admin.logs')); ?>" class="menu-item <?php echo e(request()->routeIs('system-admin.logs*') ? 'active' : ''); ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    System Logs
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <h2><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h2>
                <div class="user-menu">
                    <span class="user-name"><?php echo e(Auth::guard('admin')->user()->name); ?></span>
                    <form action="<?php echo e(route('system-admin.logout')); ?>" method="POST" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-logout">Logout</button>
                    </form>
                </div>
            </header>

            <div class="content">
                <?php if(session('success')): ?>
                    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="alert alert-error"><?php echo e(session('error')); ?></div>
                <?php endif; ?>

                <?php if(session('info')): ?>
                    <div class="alert alert-info"><?php echo e(session('info')); ?></div>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>
    </div>
</body>
</html>
<?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/layouts/system-admin.blade.php ENDPATH**/ ?>