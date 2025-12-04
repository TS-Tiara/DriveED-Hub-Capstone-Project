<?php $__env->startSection('title', 'Reports & Analytics'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $accentColor = $settings?->accent_color ?? '#1e40af';
    $useGradient = $settings?->use_gradient_header ?? true;
    $headerTextColor = $settings?->header_text_color ?? '#ffffff';
?>
<style>
    .reports-container { 
        padding: 20px; 
        margin: 20px auto; 
        max-width: 1600px; 
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 4px solid <?php echo $primaryColor; ?>;
    }
    
    .page-header h1 { 
        color: #333; 
        font-size: 2rem; 
        margin: 0;
        font-weight: 400;
    }
    
    .metrics-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 20px; 
        margin-bottom: 30px; 
    }
    
    .metric-card { 
        <?php if($useGradient): ?>
            background: linear-gradient(135deg, <?php echo $primaryColor; ?> 0%, <?php echo $secondaryColor; ?> 100%);
        <?php else: ?>
            background: <?php echo $primaryColor; ?>;
        <?php endif; ?>
        padding: 20px; 
        border-radius: 10px; 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        color: white;
        transition: transform 0.2s ease;
    }
    
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    
    .metric-card.success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .metric-card.warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .metric-card.info { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
    
    .metric-card h3 { 
        color: rgba(255, 255, 255, 0.9); 
        font-size: 0.875rem; 
        font-weight: 500; 
        margin-bottom: 8px; 
        text-transform: uppercase; 
    }
    
    .metric-card .value { font-size: 2rem; font-weight: bold; color: white; }
    .metric-card .subtitle { color: rgba(255, 255, 255, 0.8); font-size: 0.8rem; margin-top: 5px; }
    
    /* Collapsible Section Styles */
    .collapsible-section {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .section-header {
        padding: 12px 18px;
        <?php if($useGradient): ?>
            background: linear-gradient(135deg, <?php echo $primaryColor; ?> 0%, <?php echo $secondaryColor; ?> 100%);
        <?php else: ?>
            background: <?php echo $primaryColor; ?>;
        <?php endif; ?>
        color: <?php echo $headerTextColor; ?>;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.3s ease;
        user-select: none;
    }
    
    .section-header:hover {
        opacity: 0.85;
    }
    
    .section-header h2 { 
        color: <?php echo $headerTextColor; ?>; 
        font-size: 0.95rem; 
        font-weight: 600; 
        margin: 0; 
    }
    
    .collapse-icon {
        font-size: 1.1rem;
        transition: transform 0.3s ease;
    }
    
    .collapse-icon.collapsed {
        transform: rotate(-90deg);
    }
    
    .section-content {
        padding: 20px;
        display: block;
        transition: all 0.3s ease;
    }
    
    .section-content.collapsed {
        display: none;
    }
    
    .reports-table { 
        width: 100%; 
        border-collapse: collapse; 
    }
    
    .reports-table th, .reports-table td { 
        padding: 10px; 
        text-align: left; 
        border-bottom: 1px solid #e5e7eb; 
        font-size: 14px; 
    }
    
    .reports-table th { 
        background: #f8f9fa; 
        color: #333; 
        font-weight: 600; 
    }
    
    .reports-table tr:hover { 
        background: #f8f9fa; 
    }
    
    .badge { 
        display: inline-block; 
        padding: 4px 10px; 
        border-radius: 12px; 
        font-size: 11px; 
        font-weight: 600; 
    }
    
    .badge-success { background: #d4edda; color: #155724; }
    .badge-warning { background: #fff3cd; color: #856404; }
    .badge-danger { background: #f8d7da; color: #721c24; }
    .badge-info { background: #d1ecf1; color: #0c5460; }
    
    .empty-state { 
        text-align: center; 
        padding: 40px; 
        color: #95a5a6; 
    }
    
    .stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .stat-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
    }
    
    .stat-box .label {
        color: #7f8c8d;
        font-size: 12px;
        margin-bottom: 5px;
    }
    
    .stat-box .value {
        color: #2c3e50;
        font-size: 1.5rem;
        font-weight: bold;
    }

    .detailed-reports-section {
        margin-bottom: 30px;
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .detailed-reports-section h2 {
        color: #333;
        font-size: 1.3rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 15px;
    }

    .report-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
        border-radius: 8px;
        text-decoration: none;
        color: #333;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .report-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        border-color: <?php echo $primaryColor; ?>;
        background: white;
    }

    .report-icon {
        font-size: 1.8rem;
        min-width: 40px;
        text-align: center;
    }

    .report-info {
        flex: 1;
    }

    .report-title {
        font-weight: 600;
        font-size: 1rem;
        color: #2c3e50;
        margin-bottom: 3px;
    }

    .report-description {
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>

<div class="reports-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Reports & Analytics</h1>
    </div>

    <!-- Key Metrics Summary (Always Visible) -->
    <div class="metrics-grid">
        <div class="metric-card info">
            <h3>Total Students</h3>
            <div class="value"><?php echo e($analytics['total_students']); ?></div>
            <div class="subtitle"><?php echo e($analytics['active_students']); ?> active</div>
        </div>
        <div class="metric-card success">
            <h3>Total Instructors</h3>
            <div class="value"><?php echo e($analytics['total_instructors']); ?></div>
        </div>
        <div class="metric-card warning">
            <h3>This Month Bookings</h3>
            <div class="value"><?php echo e($analytics['total_bookings_this_month']); ?></div>
        </div>
        <div class="metric-card success">
            <h3>Completed Lessons</h3>
            <div class="value"><?php echo e($analytics['completed_lessons_this_month']); ?></div>
        </div>
        <div class="metric-card <?php echo e($analytics['completion_rate'] >= 70 ? 'success' : 'warning'); ?>">
            <h3>Completion Rate</h3>
            <div class="value"><?php echo e(number_format($analytics['completion_rate'], 1)); ?>%</div>
        </div>
    </div>

    <!-- Student Enrollment Section (Collapsible) -->
    <div class="collapsible-section">
        <div class="section-header" onclick="toggleSection(this)">
            <h2>Student Enrollment Overview</h2>
            <span class="collapse-icon">▼</span>
        </div>
        <div class="section-content">
            <div class="stats-summary">
                <div class="stat-box">
                    <div class="label">Total Enrolled</div>
                    <div class="value"><?php echo e($analytics['total_students']); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Active</div>
                    <div class="value" style="color: #10b981;"><?php echo e($analytics['active_students']); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">This Month</div>
                    <div class="value"><?php echo e($analytics['enrollments_this_month']); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Growth</div>
                    <div class="value" style="color: <?php echo e($analytics['enrollment_growth'] >= 0 ? '#10b981' : '#ef4444'); ?>;">
                        <?php echo e($analytics['enrollment_growth'] >= 0 ? '+' : ''); ?><?php echo e($analytics['enrollment_growth']); ?>%
                    </div>
                </div>
            </div>
            
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $totalStudents = $analytics['total_students'];
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = $analytics['students_by_status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $percentage = $totalStudents > 0 ? ($statusData->count / $totalStudents) * 100 : 0;
                            $badgeClass = match($statusData->status) {
                                'active' => 'badge-success',
                                'inactive' => 'badge-warning',
                                'graduated' => 'badge-info',
                                default => 'badge-secondary'
                            };
                        ?>
                        <tr>
                            <td><span class="badge <?php echo e($badgeClass); ?>"><?php echo e(ucfirst($statusData->status)); ?></span></td>
                            <td><?php echo e($statusData->count); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo e($percentage); ?>%"></div>
                                </div>
                                <?php echo e(number_format($percentage, 1)); ?>%
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="empty-state">No student data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Booking Analytics Section (Collapsible) -->
    <div class="collapsible-section">
        <div class="section-header" onclick="toggleSection(this)">
            <h2>Booking Analytics</h2>
            <span class="collapse-icon">▼</span>
        </div>
        <div class="section-content">
            <div class="stats-summary">
                <div class="stat-box">
                    <div class="label">Total Bookings</div>
                    <div class="value"><?php echo e($analytics['total_all_bookings']); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">This Month</div>
                    <div class="value"><?php echo e($analytics['total_bookings_this_month']); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Completed</div>
                    <div class="value" style="color: #10b981;"><?php echo e($analytics['completed_lessons_this_month']); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Success Rate</div>
                    <div class="value"><?php echo e(number_format($analytics['completion_rate'], 1)); ?>%</div>
                </div>
            </div>
            
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $totalBookings = $analytics['total_all_bookings'];
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = $analytics['bookings_by_status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $percentage = $totalBookings > 0 ? ($statusData->count / $totalBookings) * 100 : 0;
                            $badgeClass = match($statusData->status) {
                                'completed' => 'badge-success',
                                'confirmed' => 'badge-info',
                                'pending' => 'badge-warning',
                                'cancelled', 'no-show' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                        ?>
                        <tr>
                            <td><span class="badge <?php echo e($badgeClass); ?>"><?php echo e(ucfirst($statusData->status)); ?></span></td>
                            <td><?php echo e($statusData->count); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo e($percentage); ?>%"></div>
                                </div>
                                <?php echo e(number_format($percentage, 1)); ?>%
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="empty-state">No booking data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Course Performance Section (Merged Analytics & Performance) -->
    <div class="collapsible-section">
        <div class="section-header" 
             style="<?php echo $useGradient ? "background: linear-gradient(135deg, {$primaryColor} 0%, {$secondaryColor} 100%);" : "background: {$primaryColor};"; ?>"
             onclick="toggleSection(this)">
            <h2>Course Performance & Analytics</h2>
            <span class="collapse-icon">▼</span>
        </div>
        <div class="section-content collapsed">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Total Enrolled</th>
                        <th>Price</th>
                        <th>Completion Rate</th>
                        <th>Average Rating</th>
                        <th>Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $analytics['course_stats'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($course->title); ?></strong></td>
                            <td><span class="badge badge-info"><?php echo e($course->total_enrolled); ?></span></td>
                            <td>₱<?php echo e(number_format($course->price ?? 0, 2)); ?></td>
                            <td>
                                <span style="color: <?php echo e($course->completion_rate >= 70 ? '#10b981' : '#f59e0b'); ?>;">
                                    <?php echo e(number_format($course->completion_rate, 1)); ?>%
                                </span>
                            </td>
                            <td>
                                <?php if($course->average_rating): ?>
                                    <span style="color: #f59e0b;">★</span> <?php echo e(number_format($course->average_rating, 1)); ?>

                                <?php else: ?>
                                    <span style="color: #9ca3af;">No ratings</span>
                                <?php endif; ?>
                            </td>
                            <td>₱<?php echo e(number_format($course->total_revenue, 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="empty-state">No course data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Instructor Performance Section (Collapsible) -->
    <div class="collapsible-section">
        <div class="section-header" onclick="toggleSection(this)">
            <h2>Instructor Performance</h2>
            <span class="collapse-icon collapsed">▼</span>
        </div>
        <div class="section-content collapsed">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Instructor</th>
                        <th>Total Sessions</th>
                        <th>Completed</th>
                        <th>Rating</th>
                        <th>Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $analytics['top_instructors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instructor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($instructor->name); ?></strong></td>
                            <td><?php echo e($instructor->total_sessions); ?></td>
                            <td><?php echo e($instructor->completed_sessions); ?></td>
                            <td>
                                <?php if($instructor->average_rating): ?>
                                    <span style="color: #f59e0b;">★</span> <?php echo e(number_format($instructor->average_rating, 1)); ?>

                                <?php else: ?>
                                    <span style="color: #9ca3af;">No ratings yet</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="color: <?php echo e($instructor->completion_rate >= 80 ? '#10b981' : '#f59e0b'); ?>;">
                                    <?php echo e(number_format($instructor->completion_rate, 1)); ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="empty-state">No instructor data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Attendance & Performance Section (Collapsible) -->
    <div class="collapsible-section">
        <div class="section-header" onclick="toggleSection(this)">
            <h2>Attendance & Performance</h2>
            <span class="collapse-icon collapsed">▼</span>
        </div>
        <div class="section-content collapsed">
            <div class="stats-summary">
                <div class="stat-box">
                    <div class="label">Attendance Rate</div>
                    <div class="value" style="color: #10b981;"><?php echo e(number_format($analytics['attendance']['rate'] ?? 0, 1)); ?>%</div>
                </div>
                <div class="stat-box">
                    <div class="label">Attended</div>
                    <div class="value"><?php echo e($analytics['attendance']['attended'] ?? 0); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Missed</div>
                    <div class="value" style="color: #ef4444;"><?php echo e($analytics['attendance']['missed'] ?? 0); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Cancellations</div>
                    <div class="value" style="color: #f59e0b;"><?php echo e($analytics['cancellations']['total'] ?? 0); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">No-Shows</div>
                    <div class="value" style="color: #ef4444;"><?php echo e($analytics['cancellations']['no_show']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lessons Report Section (Driving + Practical Merged) -->
    <div class="collapsible-section">
        <div class="section-header" 
             style="<?php echo $useGradient ? "background: linear-gradient(135deg, {$primaryColor} 0%, {$secondaryColor} 100%);" : "background: {$primaryColor};"; ?>"
             onclick="toggleSection(this)">
            <h2>Lessons Report</h2>
            <span class="collapse-icon">▼</span>
        </div>
        <div class="section-content collapsed">
            <div class="stats-summary">
                <div class="stat-box">
                    <div class="label">Total Lessons</div>
                    <div class="value"><?php echo e($analytics['total_bookings_this_month']); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Completed</div>
                    <div class="value" style="color: #10b981;"><?php echo e($analytics['completed_lessons_this_month']); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Completion Rate</div>
                    <div class="value" style="color: <?php echo e($school->schoolSetting->primary_color ?? '#667eea'); ?>;"><?php echo e(number_format($analytics['completion_rate'], 1)); ?>%</div>
                </div>
            </div>

            <h3 style="margin-top: 25px; margin-bottom: 15px;">Lessons by Status</h3>
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $analytics['lessons_by_status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="badge <?php echo e(match($statusData->status) {
                                    'completed' => 'badge-success',
                                    'confirmed' => 'badge-info',
                                    'pending' => 'badge-warning',
                                    'cancelled' => 'badge-danger',
                                    default => 'badge-secondary'
                                }); ?>"><?php echo e(ucfirst($statusData->status)); ?></span>
                            </td>
                            <td><?php echo e($statusData->count); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="2" class="empty-state">No lesson data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3 style="margin-top: 25px; margin-bottom: 15px;">Lessons by Instructor</h3>
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Instructor</th>
                        <th>Total Lessons</th>
                        <th>Completed</th>
                        <th>Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $analytics['lessons_by_instructor']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instructor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($instructor->instructor_name); ?></td>
                            <td><?php echo e($instructor->total_lessons); ?></td>
                            <td><?php echo e($instructor->completed_lessons); ?></td>
                            <td>
                                <span style="color: <?php echo e($instructor->completion_rate >= 70 ? '#10b981' : '#f59e0b'); ?>;">
                                    <?php echo e(number_format($instructor->completion_rate, 1)); ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="empty-state">No instructor data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bookings & Cancellations Report (Merged) -->
    <div class="collapsible-section">
        <div class="section-header" 
             style="<?php echo $useGradient ? "background: linear-gradient(135deg, {$primaryColor} 0%, {$secondaryColor} 100%);" : "background: {$primaryColor};"; ?>"
             onclick="toggleSection(this)">
            <h2>Bookings & Cancellations</h2>
            <span class="collapse-icon">▼</span>
        </div>
        <div class="section-content collapsed">
            <div class="stats-summary">
                <div class="stat-box">
                    <div class="label">Total Cancellations</div>
                    <div class="value" style="color: #f59e0b;"><?php echo e($analytics['cancellations']['total']); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">No-Shows</div>
                    <div class="value" style="color: #ef4444;"><?php echo e($analytics['cancellations']['no_show']); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Total Issues</div>
                    <div class="value" style="color: #ef4444;"><?php echo e($analytics['cancellations']['total'] + $analytics['cancellations']['no_show']); ?></div>
                </div>
            </div>

            <h3 style="margin-top: 25px; margin-bottom: 15px;">Recent Cancellations & No-Shows</h3>
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Instructor</th>
                        <th>Course</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $analytics['cancellation_details']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($booking->scheduled_at ? $booking->scheduled_at->format('M d, Y') : 'N/A'); ?></td>
                            <td><?php echo e($booking->student->name ?? 'N/A'); ?></td>
                            <td><?php echo e($booking->instructor->name ?? 'Unassigned'); ?></td>
                            <td><?php echo e($booking->course->title ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge <?php echo e($booking->status == 'cancelled' ? 'badge-warning' : 'badge-danger'); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $booking->status))); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="empty-state">No cancellations or no-shows</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Financial Report Section -->
    <div class="collapsible-section">
        <div class="section-header" 
             style="<?php echo $useGradient ? "background: linear-gradient(135deg, {$primaryColor} 0%, {$secondaryColor} 100%);" : "background: {$primaryColor};"; ?>"
             onclick="toggleSection(this)">
            <h2>Financial Report</h2>
            <span class="collapse-icon">▼</span>
        </div>
        <div class="section-content collapsed">
            <div class="stats-summary">
                <div class="stat-box">
                    <div class="label">Total Revenue</div>
                    <div class="value" style="color: #10b981;">₱<?php echo e(number_format($analytics['financial']['total_revenue'], 2)); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Pending Payments</div>
                    <div class="value" style="color: #f59e0b;">₱<?php echo e(number_format($analytics['financial']['pending_payments'], 2)); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Total Expected</div>
                    <div class="value" style="color: <?php echo e($school->schoolSetting->primary_color ?? '#667eea'); ?>;">₱<?php echo e(number_format($analytics['financial']['total_revenue'] + $analytics['financial']['pending_payments'], 2)); ?></div>
                </div>
            </div>

            <h3 style="margin-top: 25px; margin-bottom: 15px;">Payments by Method</h3>
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Total Amount</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $analytics['financial']['payments_by_method']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e(ucfirst($payment->method ?? 'N/A')); ?></strong></td>
                            <td>₱<?php echo e(number_format($payment->total, 2)); ?></td>
                            <td><?php echo e($payment->count); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="empty-state">No payment data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Student Progress Report Section -->
    <div class="collapsible-section">
        <div class="section-header" 
             style="<?php echo $useGradient ? "background: linear-gradient(135deg, {$primaryColor} 0%, {$secondaryColor} 100%);" : "background: {$primaryColor};"; ?>"
             onclick="toggleSection(this)">
            <h2>Student Progress Report</h2>
            <span class="collapse-icon">▼</span>
        </div>
        <div class="section-content collapsed">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Total Lessons</th>
                        <th>Completed</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $analytics['student_progress']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($student->name); ?></strong></td>
                            <td><?php echo e($student->email); ?></td>
                            <td>
                                <span class="badge <?php echo e(match($student->status) {
                                    'active' => 'badge-success',
                                    'inactive' => 'badge-warning',
                                    'graduated' => 'badge-info',
                                    default => 'badge-secondary'
                                }); ?>"><?php echo e(ucfirst($student->status)); ?></span>
                            </td>
                            <td><?php echo e($student->total_lessons); ?></td>
                            <td><?php echo e($student->completed_lessons); ?></td>
                            <td>
                                <span style="color: <?php echo e($student->progress_rate >= 70 ? '#10b981' : '#f59e0b'); ?>;">
                                    <?php echo e(number_format($student->progress_rate, 1)); ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="empty-state">No student progress data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleSection(header) {
    const content = header.nextElementSibling;
    const icon = header.querySelector('.collapse-icon');
    
    content.classList.toggle('collapsed');
    icon.classList.toggle('collapsed');
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/school/admin/reports/index.blade.php ENDPATH**/ ?>