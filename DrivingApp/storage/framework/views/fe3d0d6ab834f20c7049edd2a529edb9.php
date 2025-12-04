

<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
?>

<style>
    /* Basic container - your designer can restyle this */
    .dashboard-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1600px;
    }
    
    .page-header {
        margin-bottom: 30px;
    }
    
    .page-title {
        font-size: 2rem;
        color: #333;
        margin: 0 0 10px 0;
    }
    
    .page-subtitle {
        color: #666;
        font-size: 0.95rem;
    }
    
    /* Stats Grid - Designer can change layout/colors */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 8px;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #111827;
        margin-bottom: 8px;
    }
    
    .stat-detail {
        font-size: 0.875rem;
        color: #9ca3af;
    }
    
    /* Quick Actions - Designer can restyle */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .action-btn {
        padding: 15px 20px;
        background: var(--btn-primary-bg);
        color: var(--btn-primary-text);
        text-decoration: none;
        border-radius: var(--button-border-radius);
        text-align: center;
        font-weight: 500;
        transition: all 0.3s ease;
        display: block;
    }
    
    .action-btn:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        color: var(--btn-primary-text);
    }
    
    /* Content Sections */
    .content-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        margin-bottom: 20px;
    }
    
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 15px;
    }
    
    /* Activity List */
    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .activity-item {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-name {
        font-weight: 500;
        color: #111827;
    }
    
    .activity-email {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .activity-date {
        font-size: 0.875rem;
        color: #9ca3af;
    }
    
    .badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }
    
    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }
    
    .badge-info {
        background: #dbeafe;
        color: #1e3a8a;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #9ca3af;
    }
    
    /* Chart container */
    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 20px;
    }
</style>

<div class="dashboard-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome to <?php echo e($schoolName); ?> Admin Dashboard</p>
    </div>

    <?php if(session('success')): ?>
        <div style="background-color: #d1fae5; color: #065f46; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #10b981;">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Key Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Students</div>
            <div class="stat-value"><?php echo e($totalStudents); ?></div>
            <div class="stat-detail"><?php echo e($activeStudents); ?> Active · <?php echo e($inactiveStudents); ?> Inactive</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">Total Instructors</div>
            <div class="stat-value"><?php echo e($totalInstructors); ?></div>
            <div class="stat-detail"><?php echo e($availableInstructors); ?> Available · <?php echo e($activeInstructors); ?> Active</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value"><?php echo e($totalStudents + $totalInstructors); ?></div>
            <div class="stat-detail">Combined student & instructor count</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">Active Users</div>
            <div class="stat-value"><?php echo e($activeStudents + $activeInstructors); ?></div>
            <div class="stat-detail">Currently active accounts</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="content-section">
        <h2 class="section-title">Quick Actions</h2>
        <div class="quick-actions">
            <a href="<?php echo e($schoolRoute('admin.userManagement')); ?>" class="action-btn" onclick="loadContent(this.href); return false;">
                Manage Users
            </a>
            <a href="<?php echo e($schoolRoute('admin.schedules')); ?>" class="action-btn" onclick="loadContent(this.href); return false;">
                View Schedules
            </a>
            <a href="<?php echo e($schoolRoute('admin.courses')); ?>" class="action-btn" onclick="loadContent(this.href); return false;">
                Manage Courses
            </a>
            <a href="<?php echo e($schoolRoute('admin.bookings.index')); ?>" class="action-btn" onclick="loadContent(this.href); return false;">
                View Bookings
            </a>
            <a href="<?php echo e($schoolRoute('admin.reports.index')); ?>" class="action-btn" onclick="loadContent(this.href); return false;">
                View Reports
            </a>
        </div>
    </div>

    <!-- Two Column Layout for Recent Activity -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
        <!-- Recent Students -->
        <div class="content-section">
            <h2 class="section-title">Recent Students</h2>
            <?php if($recentStudents->count() > 0): ?>
                <ul class="activity-list">
                    <?php $__currentLoopData = $recentStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="activity-item">
                        <div>
                            <div class="activity-name"><?php echo e($student->name); ?></div>
                            <div class="activity-email"><?php echo e($student->email); ?></div>
                        </div>
                        <div>
                            <span class="badge badge-<?php echo e($student->status === 'active' ? 'success' : 'warning'); ?>">
                                <?php echo e(ucfirst($student->status)); ?>

                            </span>
                        </div>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <div style="margin-top: 15px; text-align: center;">
                    <a href="<?php echo e($schoolRoute('admin.userManagement')); ?>" onclick="loadContent(this.href); return false;" style="color: #667eea; text-decoration: none; font-size: 0.875rem;">
                        View All Students →
                    </a>
                </div>
            <?php else: ?>
                <div class="empty-state">No students yet</div>
            <?php endif; ?>
        </div>

        <!-- Recent Instructors -->
        <div class="content-section">
            <h2 class="section-title">Recent Instructors</h2>
            <?php if($recentInstructors->count() > 0): ?>
                <ul class="activity-list">
                    <?php $__currentLoopData = $recentInstructors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instructor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="activity-item">
                        <div>
                            <div class="activity-name"><?php echo e($instructor->name); ?></div>
                            <div class="activity-email"><?php echo e($instructor->email); ?></div>
                        </div>
                        <div>
                            <span class="badge badge-<?php echo e($instructor->availability === 'available' ? 'success' : 'warning'); ?>">
                                <?php echo e(ucfirst($instructor->availability)); ?>

                            </span>
                        </div>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <div style="margin-top: 15px; text-align: center;">
                    <a href="<?php echo e($schoolRoute('admin.userManagement')); ?>" onclick="loadContent(this.href); return false;" style="color: #667eea; text-decoration: none; font-size: 0.875rem;">
                        View All Instructors →
                    </a>
                </div>
            <?php else: ?>
                <div class="empty-state">No instructors yet</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Enrollment Trend Chart -->
    <div class="content-section">
        <h2 class="section-title">Student Enrollment Trend (Last 30 Days)</h2>
        <div class="chart-container">
            <canvas id="enrollmentChart"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Enrollment Trend Chart
    const enrollmentData = <?php echo json_encode($enrollmentData, 15, 512) ?>;
    const labels = enrollmentData.map(item => item.date);
    const data = enrollmentData.map(item => item.count);

    const ctx = document.getElementById('enrollmentChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'New Students',
                data: data,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/school/admin/dashboard.blade.php ENDPATH**/ ?>