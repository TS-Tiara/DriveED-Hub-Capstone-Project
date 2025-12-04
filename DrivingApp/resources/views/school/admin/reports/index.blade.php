@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<?php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $accentColor = $settings?->accent_color ?? '#1e40af';
    $useGradient = $settings?->use_gradient_header ?? true;
    $headerTextColor = $settings?->header_text_color ?? '#ffffff';
?>

@include('school.admin.partials.admin-styles')

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
        padding-bottom: 15px;
        border-bottom: 3px solid <?php echo $primaryColor; ?>;
    }
    
    .page-header h1 { 
        color: #1f2937; 
        font-size: 1.75rem; 
        margin: 0;
        font-weight: 600;
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
    
    /* Export Dropdown Styles */
    .export-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .export-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        <?php if($useGradient): ?>
            background: linear-gradient(135deg, <?php echo $primaryColor; ?> 0%, <?php echo $secondaryColor; ?> 100%);
        <?php else: ?>
            background: <?php echo $primaryColor; ?>;
        <?php endif; ?>
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }
    
    .export-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }
    
    .export-menu {
        position: absolute;
        right: 0;
        top: calc(100% + 5px);
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        min-width: 220px;
        z-index: 100;
        display: none;
        overflow: hidden;
    }
    
    .export-menu.show {
        display: block;
    }
    
    .export-menu-title {
        padding: 12px 16px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #f8f9fa;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .export-menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        color: #333;
        text-decoration: none;
        font-size: 0.9rem;
        transition: background 0.2s ease;
    }
    
    .export-menu a:hover {
        background: #f0f4ff;
    }
    
    .export-menu a span.icon {
        font-size: 1.1rem;
        min-width: 24px;
        text-align: center;
    }
</style>

<div class="reports-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Reports & Analytics</h1>
        <div class="export-dropdown">
            <button class="export-btn" onclick="toggleExportMenu(event)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: middle;">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                </svg>
                Export Data ▼
            </button>
            <div class="export-menu" id="exportMenu">
                <div class="export-menu-title">Download as Excel</div>
                <a href="{{ route('schools.admin.reports.export.students', $school) }}">
                    Students
                </a>
                <a href="{{ route('schools.admin.reports.export.instructors', $school) }}">
                    Instructors
                </a>
                <a href="{{ route('schools.admin.reports.export.bookings', $school) }}">
                    Bookings
                </a>
                <a href="{{ route('schools.admin.reports.export.payments', $school) }}">
                    Payments
                </a>
                <a href="{{ route('schools.admin.reports.export.courses', $school) }}">
                    Courses
                </a>
            </div>
        </div>
    </div>

    <!-- Key Metrics Summary (Always Visible) -->
    <div class="metrics-grid">
        <div class="metric-card info">
            <h3>Total Students</h3>
            <div class="value">{{ $analytics['total_students'] }}</div>
            <div class="subtitle">{{ $analytics['active_students'] }} active</div>
        </div>
        <div class="metric-card success">
            <h3>Total Instructors</h3>
            <div class="value">{{ $analytics['total_instructors'] }}</div>
        </div>
        <div class="metric-card warning">
            <h3>This Month Bookings</h3>
            <div class="value">{{ $analytics['total_bookings_this_month'] }}</div>
        </div>
        <div class="metric-card success">
            <h3>Completed Lessons</h3>
            <div class="value">{{ $analytics['completed_lessons_this_month'] }}</div>
        </div>
        <div class="metric-card {{ $analytics['completion_rate'] >= 70 ? 'success' : 'warning' }}">
            <h3>Completion Rate</h3>
            <div class="value">{{ number_format($analytics['completion_rate'], 1) }}%</div>
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
                    <div class="value">{{ $analytics['total_students'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Active</div>
                    <div class="value" style="color: #10b981;">{{ $analytics['active_students'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">This Month</div>
                    <div class="value">{{ $analytics['enrollments_this_month'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Growth</div>
                    <div class="value" style="color: {{ $analytics['enrollment_growth'] >= 0 ? '#10b981' : '#ef4444' }};">
                        {{ $analytics['enrollment_growth'] >= 0 ? '+' : '' }}{{ $analytics['enrollment_growth'] }}%
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
                    @php
                        $totalStudents = $analytics['total_students'];
                    @endphp
                    @forelse($analytics['students_by_status'] as $statusData)
                        @php
                            $percentage = $totalStudents > 0 ? ($statusData->count / $totalStudents) * 100 : 0;
                            $badgeClass = match($statusData->status) {
                                'active' => 'badge-success',
                                'inactive' => 'badge-warning',
                                'graduated' => 'badge-info',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <tr>
                            <td><span class="badge {{ $badgeClass }}">{{ ucfirst($statusData->status) }}</span></td>
                            <td>{{ $statusData->count }}</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                                </div>
                                {{ number_format($percentage, 1) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-state">No student data available</td>
                        </tr>
                    @endforelse
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
                    <div class="value">{{ $analytics['total_all_bookings'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">This Month</div>
                    <div class="value">{{ $analytics['total_bookings_this_month'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Completed</div>
                    <div class="value" style="color: #10b981;">{{ $analytics['completed_lessons_this_month'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Success Rate</div>
                    <div class="value">{{ number_format($analytics['completion_rate'], 1) }}%</div>
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
                    @php
                        $totalBookings = $analytics['total_all_bookings'];
                    @endphp
                    @forelse($analytics['bookings_by_status'] as $statusData)
                        @php
                            $percentage = $totalBookings > 0 ? ($statusData->count / $totalBookings) * 100 : 0;
                            $badgeClass = match($statusData->status) {
                                'completed' => 'badge-success',
                                'confirmed' => 'badge-info',
                                'pending' => 'badge-warning',
                                'cancelled', 'no-show' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <tr>
                            <td><span class="badge {{ $badgeClass }}">{{ ucfirst($statusData->status) }}</span></td>
                            <td>{{ $statusData->count }}</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                                </div>
                                {{ number_format($percentage, 1) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-state">No booking data available</td>
                        </tr>
                    @endforelse
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
                    @forelse($analytics['course_stats'] ?? [] as $course)
                        <tr>
                            <td><strong>{{ $course->title }}</strong></td>
                            <td><span class="badge badge-info">{{ $course->total_enrolled }}</span></td>
                            <td>₱{{ number_format($course->price ?? 0, 2) }}</td>
                            <td>
                                <span style="color: {{ $course->completion_rate >= 70 ? '#10b981' : '#f59e0b' }};">
                                    {{ number_format($course->completion_rate, 1) }}%
                                </span>
                            </td>
                            <td>
                                @if($course->average_rating)
                                    <span style="color: #f59e0b;">★</span> {{ number_format($course->average_rating, 1) }}
                                @else
                                    <span style="color: #9ca3af;">No ratings</span>
                                @endif
                            </td>
                            <td>₱{{ number_format($course->total_revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No course data available</td>
                        </tr>
                    @endforelse
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
                    @forelse($analytics['top_instructors'] as $instructor)
                        <tr>
                            <td><strong>{{ $instructor->name }}</strong></td>
                            <td>{{ $instructor->total_sessions }}</td>
                            <td>{{ $instructor->completed_sessions }}</td>
                            <td>
                                @if($instructor->average_rating)
                                    <span style="color: #f59e0b;">★</span> {{ number_format($instructor->average_rating, 1) }}
                                @else
                                    <span style="color: #9ca3af;">No ratings yet</span>
                                @endif
                            </td>
                            <td>
                                <span style="color: {{ $instructor->completion_rate >= 80 ? '#10b981' : '#f59e0b' }};">
                                    {{ number_format($instructor->completion_rate, 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">No instructor data available</td>
                        </tr>
                    @endforelse
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
                    <div class="value" style="color: #10b981;">{{ number_format($analytics['attendance']['rate'] ?? 0, 1) }}%</div>
                </div>
                <div class="stat-box">
                    <div class="label">Attended</div>
                    <div class="value">{{ $analytics['attendance']['attended'] ?? 0 }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Missed</div>
                    <div class="value" style="color: #ef4444;">{{ $analytics['attendance']['missed'] ?? 0 }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Cancellations</div>
                    <div class="value" style="color: #f59e0b;">{{ $analytics['cancellations']['total'] ?? 0 }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">No-Shows</div>
                    <div class="value" style="color: #ef4444;">{{ $analytics['cancellations']['no_show'] }}</div>
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
                    <div class="value">{{ $analytics['total_bookings_this_month'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Completed</div>
                    <div class="value" style="color: #10b981;">{{ $analytics['completed_lessons_this_month'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Completion Rate</div>
                    <div class="value" style="color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};">{{ number_format($analytics['completion_rate'], 1) }}%</div>
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
                    @forelse($analytics['lessons_by_status'] as $statusData)
                        <tr>
                            <td>
                                <span class="badge {{ match($statusData->status) {
                                    'completed' => 'badge-success',
                                    'confirmed' => 'badge-info',
                                    'pending' => 'badge-warning',
                                    'cancelled' => 'badge-danger',
                                    default => 'badge-secondary'
                                } }}">{{ ucfirst($statusData->status) }}</span>
                            </td>
                            <td>{{ $statusData->count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="empty-state">No lesson data available</td>
                        </tr>
                    @endforelse
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
                    @forelse($analytics['lessons_by_instructor'] as $instructor)
                        <tr>
                            <td>{{ $instructor->instructor_name }}</td>
                            <td>{{ $instructor->total_lessons }}</td>
                            <td>{{ $instructor->completed_lessons }}</td>
                            <td>
                                <span style="color: {{ $instructor->completion_rate >= 70 ? '#10b981' : '#f59e0b' }};">
                                    {{ number_format($instructor->completion_rate, 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">No instructor data available</td>
                        </tr>
                    @endforelse
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
                    <div class="value" style="color: #f59e0b;">{{ $analytics['cancellations']['total'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">No-Shows</div>
                    <div class="value" style="color: #ef4444;">{{ $analytics['cancellations']['no_show'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Total Issues</div>
                    <div class="value" style="color: #ef4444;">{{ $analytics['cancellations']['total'] + $analytics['cancellations']['no_show'] }}</div>
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
                    @forelse($analytics['cancellation_details'] as $booking)
                        <tr>
                            <td>{{ $booking->scheduled_at ? $booking->scheduled_at->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $booking->student->name ?? 'N/A' }}</td>
                            <td>{{ $booking->instructor->name ?? 'Unassigned' }}</td>
                            <td>{{ $booking->course->title ?? 'N/A' }}</td>
                            <td>
                                <span class="badge {{ $booking->status == 'cancelled' ? 'badge-warning' : 'badge-danger' }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">No cancellations or no-shows</td>
                        </tr>
                    @endforelse
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
                    <div class="value" style="color: #10b981;">₱{{ number_format($analytics['financial']['total_revenue'], 2) }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Pending Payments</div>
                    <div class="value" style="color: #f59e0b;">₱{{ number_format($analytics['financial']['pending_payments'], 2) }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Total Expected</div>
                    <div class="value" style="color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};">₱{{ number_format($analytics['financial']['total_revenue'] + $analytics['financial']['pending_payments'], 2) }}</div>
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
                    @forelse($analytics['financial']['payments_by_method'] as $payment)
                        <tr>
                            <td><strong>{{ ucfirst($payment->method ?? 'N/A') }}</strong></td>
                            <td>₱{{ number_format($payment->total, 2) }}</td>
                            <td>{{ $payment->count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-state">No payment data available</td>
                        </tr>
                    @endforelse
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
                    @forelse($analytics['student_progress'] as $student)
                        <tr>
                            <td><strong>{{ $student->name }}</strong></td>
                            <td>{{ $student->email }}</td>
                            <td>
                                <span class="badge {{ match($student->status) {
                                    'active' => 'badge-success',
                                    'inactive' => 'badge-warning',
                                    'graduated' => 'badge-info',
                                    default => 'badge-secondary'
                                } }}">{{ ucfirst($student->status) }}</span>
                            </td>
                            <td>{{ $student->total_lessons }}</td>
                            <td>{{ $student->completed_lessons }}</td>
                            <td>
                                <span style="color: {{ $student->progress_rate >= 70 ? '#10b981' : '#f59e0b' }};">
                                    {{ number_format($student->progress_rate, 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No student progress data available</td>
                        </tr>
                    @endforelse
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

function toggleExportMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('exportMenu');
    menu.classList.toggle('show');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('exportMenu');
    const dropdown = document.querySelector('.export-dropdown');
    if (!dropdown.contains(event.target)) {
        menu.classList.remove('show');
    }
});
</script>

@endsection
