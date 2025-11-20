@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
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
        border-bottom: 2px solid #667eea;
    }
    
    .page-header h1 { 
        color: #333; 
        font-size: 2rem; 
        margin: 0; 
    }
    
    .metrics-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 20px; 
        margin-bottom: 30px; 
    }
    
    .metric-card { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px; 
        border-radius: 10px; 
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
        color: white;
        transition: transform 0.2s ease;
    }
    
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
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
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.3s ease;
        user-select: none;
    }
    
    .section-header:hover {
        background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
    }
    
    .section-header h2 { 
        color: white; 
        font-size: 1.25rem; 
        font-weight: 600; 
        margin: 0; 
    }
    
    .collapse-icon {
        font-size: 1.5rem;
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
            <h2>📚 Student Enrollment Overview</h2>
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
            <h2>📅 Booking Analytics</h2>
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

    <!-- Course Analytics Section (Collapsible) -->
    <div class="collapsible-section">
        <div class="section-header" onclick="toggleSection(this)">
            <h2>📖 Course Analytics</h2>
            <span class="collapse-icon">▼</span>
        </div>
        <div class="section-content collapsed">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Enrollments</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($analytics['courses'] as $course)
                        <tr>
                            <td>{{ $course->name }}</td>
                            <td><span class="badge badge-info">{{ $course->enrollments_count }}</span></td>
                            <td>${{ number_format($course->price, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-state">No course data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Instructor Performance Section (Collapsible) -->
    <div class="collapsible-section">
        <div class="section-header" onclick="toggleSection(this)">
            <h2>👨‍🏫 Instructor Performance</h2>
            <span class="collapse-icon collapsed">▼</span>
        </div>
        <div class="section-content collapsed">
            @forelse($analytics['top_instructors'] as $instructor)
    <div class="reports-card" style="margin-bottom: 25px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; margin: -20px -20px 20px -20px;">
            <h3 style="margin: 0 0 10px 0; font-size: 22px;">{{ $course->name }}</h3>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 15px;">
                <div>
                    <div style="opacity: 0.9; font-size: 12px;">Price</div>
                    <div style="font-size: 20px; font-weight: bold;">${{ number_format($course->price, 2) }}</div>
                </div>
                <div>
                    <div style="opacity: 0.9; font-size: 12px;">Duration</div>
                    <div style="font-size: 20px; font-weight: bold;">{{ $course->duration }}h</div>
                </div>
                <div>
                    <div style="opacity: 0.9; font-size: 12px;">Total Enrolled</div>
                    <div style="font-size: 20px; font-weight: bold;">{{ $course->total_enrolled }} students</div>
                </div>
                <div>
                    <div style="opacity: 0.9; font-size: 12px;">Completion Rate</div>
                    <div style="font-size: 20px; font-weight: bold;">{{ number_format($course->completion_rate, 1) }}%</div>
                </div>
            </div>
        </div>

        <!-- Enrollments by Time Period -->
        <div style="margin-bottom: 25px;">
            <h4 style="color: #2c3e50; margin-bottom: 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Enrollments by Period</h4>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="color: #27ae60; font-size: 24px; font-weight: bold;">{{ $course->enrollments_today }}</div>
                    <div style="color: #7f8c8d; font-size: 14px; margin-top: 5px;">Today</div>
                </div>
                <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="color: #2196f3; font-size: 24px; font-weight: bold;">{{ $course->enrollments_this_week }}</div>
                    <div style="color: #7f8c8d; font-size: 14px; margin-top: 5px;">This Week</div>
                </div>
                <div style="background: #fff3e0; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="color: #f57c00; font-size: 24px; font-weight: bold;">{{ $course->enrollments_this_month }}</div>
                    <div style="color: #7f8c8d; font-size: 14px; margin-top: 5px;">This Month</div>
                </div>
                <div style="background: #f3e5f5; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="color: #8e24aa; font-size: 24px; font-weight: bold;">{{ $course->enrollments_this_year }}</div>
                    <div style="color: #7f8c8d; font-size: 14px; margin-top: 5px;">This Year</div>
                </div>
            </div>
        </div>

        <!-- Lesson Statistics -->
        <div style="margin-bottom: 25px;">
            <h4 style="color: #2c3e50; margin-bottom: 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Lesson Statistics</h4>
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Total Bookings</th>
                        <th>Completed</th>
                        <th>Attended</th>
                        <th>Pending</th>
                        <th>Confirmed</th>
                        <th>Cancelled</th>
                        <th>No-Show</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge badge-info">{{ $course->total_bookings }}</span></td>
                        <td><span class="badge badge-success">{{ $course->completed_lessons }}</span></td>
                        <td><span class="badge badge-success">{{ $course->attended_lessons }}</span></td>
                        <td><span class="badge badge-warning">{{ $course->pending_lessons }}</span></td>
                        <td><span class="badge badge-info">{{ $course->confirmed_lessons }}</span></td>
                        <td><span class="badge badge-danger">{{ $course->cancelled_lessons }}</span></td>
                        <td><span class="badge badge-danger">{{ $course->no_show_lessons }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Performance Metrics -->
        <div>
                    <!-- Performance Metrics -->
        <div style="margin-bottom: 25px;">
            <h4 style="color: #2c3e50; margin-bottom: 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Performance Metrics</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: #7f8c8d;">Attendance Rate</span>
                        <span style="font-weight: bold; color: #27ae60;">{{ number_format($course->attendance_rate, 1) }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $course->attendance_rate }}%; background: #27ae60;"></div>
                    </div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: #7f8c8d;">Completion Rate</span>
                        <span style="font-weight: bold; color: #3498db;">{{ number_format($course->completion_rate, 1) }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $course->completion_rate }}%; background: #3498db;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="reports-card">
        <p class="empty-state">No course data available</p>
    </div>
    @endforelse

    <!-- Instructor Performance -->
    <div class="section-header">
        <h2>Instructor Performance</h2>
    </div>

    <div class="reports-card">
        <table class="reports-table">
            <thead>
                <tr>
                    <th>Instructor</th>
                    <th>Total Lessons</th>
                    <th>Completed</th>
                    <th>Completion Rate</th>
                    <th>Unique Students</th>
                </tr>
            </thead>
            <tbody>
                <table class="reports-table">
                <thead>
                    <tr>
                        <th>Instructor</th>
                        <th>Lessons</th>
                        <th>Completed</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($analytics['top_instructors'] as $instructor)
                        @php
                            $completionRate = $instructor->total_lessons > 0 
                                ? ($instructor->completed_lessons / $instructor->total_lessons) * 100 
                                : 0;
                            $rateClass = $completionRate >= 80 ? 'badge-success' : ($completionRate >= 60 ? 'badge-warning' : 'badge-danger');
                        @endphp
                        <tr>
                            <td>{{ $instructor->instructor_name }}</td>
                            <td>{{ $instructor->total_lessons }}</td>
                            <td>{{ $instructor->completed_lessons }}</td>
                            <td><span class="badge {{ $rateClass }}">{{ number_format($completionRate, 1) }}%</span></td>
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

    <!-- Attendance & Performance Section (Collapsible) -->
    <div class="collapsible-section">
        <div class="section-header" onclick="toggleSection(this)">
            <h2>📊 Attendance & Performance</h2>
            <span class="collapse-icon collapsed">▼</span>
        </div>
        <div class="section-content collapsed">
            <div class="stats-summary">
                <div class="stat-box">
                    <div class="label">Attendance Rate</div>
                    <div class="value" style="color: #10b981;">{{ number_format($analytics['attendance']['rate'], 1) }}%</div>
                </div>
                <div class="stat-box">
                    <div class="label">Attended</div>
                    <div class="value">{{ $analytics['attendance']['attended'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Missed</div>
                    <div class="value" style="color: #ef4444;">{{ $analytics['attendance']['missed'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">Cancellations</div>
                    <div class="value" style="color: #f59e0b;">{{ $analytics['cancellations']['total'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="label">No-Shows</div>
                    <div class="value" style="color: #ef4444;">{{ $analytics['cancellations']['no_show'] }}</div>
                </div>
            </div>
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

@endsection
