@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Performance Reports')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
@endphp

<style>
    .reports-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 4px solid {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    .page-title {
        font-size: 2rem;
        color: #111827;
        margin: 0;
        font-weight: 400;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
        margin-bottom: 20px;
    }

    .back-button:hover {
        background: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
        color: white;
        transform: translateX(-5px);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-box {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
        transition: transform 0.2s;
        border-left: 4px solid {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    .stat-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .stat-label {
        font-size: 14px;
        color: #666;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 700;
        color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    .stat-subtext {
        font-size: 12px;
        color: #999;
        margin-top: 8px;
    }

    .trend-indicator {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 14px;
        font-weight: 600;
        margin-top: 10px;
    }

    .trend-up {
        color: #10b981;
    }

    .trend-down {
        color: #ef4444;
    }

    .trend-neutral {
        color: #f59e0b;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .chart-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .chart-card h3 {
        margin: 0 0 20px 0;
        font-size: 18px;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid {{ $school->schoolSetting->primary_color ?? '#667eea' }};
        padding-bottom: 10px;
    }

    .chart-container {
        height: 300px;
        position: relative;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #f3f4f6;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        color: #374151;
        border-bottom: 2px solid {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    .data-table td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    .data-table tr:hover {
        background: #f9fafb;
    }

    .student-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: {{ $school->schoolSetting->primary_color ?? '#667eea' }};
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        margin-right: 10px;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-scheduled {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-no-show {
        background: #fef3c7;
        color: #92400e;
    }

    .upcoming-lesson {
        background: #f9fafb;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 10px;
        border-left: 3px solid {{ $school->schoolSetting->primary_color ?? '#667eea' }};
    }

    .lesson-time {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 5px;
    }

    .lesson-student {
        font-size: 13px;
        color: #6b7280;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #9ca3af;
        font-style: italic;
    }

    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .charts-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .reports-container {
            padding: 15px;
        }

        .stats-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .stat-box {
            padding: 10px;
            text-align: center;
        }

        .stat-label {
            font-size: 0.65rem;
        }

        .stat-value {
            font-size: 1.25rem;
        }

        .stat-subtext {
            font-size: 0.55rem;
        }

        .page-header h1 {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .reports-container {
            padding: 10px;
        }

        .page-header h1 {
            font-size: 1.3rem;
        }

        .stats-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
        }

        .stat-box {
            padding: 8px 4px;
        }

        .stat-label {
            font-size: 0.55rem;
        }

        .stat-value {
            font-size: 1rem;
        }

        .stat-subtext {
            font-size: 0.5rem;
        }

        .chart-card {
            padding: 15px;
        }

        .chart-card h3 {
            font-size: 15px;
        }

        .activity-list {
            padding: 0 10px;
        }

        .activity-item {
            padding: 12px 0;
        }

        .activity-date {
            font-size: 11px;
        }

        .activity-details {
            font-size: 13px;
        }
    }

    @media (max-width: 360px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .stat-box {
            padding: 8px;
        }

        .stat-label {
            font-size: 0.6rem;
        }

        .stat-value {
            font-size: 1.1rem;
        }
    }
</style>

<div class="reports-container">
    <div class="page-header">
        <h1 class="page-title">Performance Reports</h1>
    </div>

    <!-- Overall Statistics -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-label">Total Lessons</div>
            <div class="stat-value">{{ $totalLessonsCompleted }}</div>
            <div class="stat-subtext">All time completed</div>
        </div>

        <div class="stat-box">
            <div class="stat-label">Total Hours</div>
            <div class="stat-value">{{ $totalHoursTaught }}</div>
            <div class="stat-subtext">Teaching time</div>
        </div>

        <div class="stat-box">
            <div class="stat-label">Students Taught</div>
            <div class="stat-value">{{ $totalStudentsTaught }}</div>
            <div class="stat-subtext">{{ $activeStudents }} active now</div>
        </div>

        <div class="stat-box">
            <div class="stat-label">Attendance Rate</div>
            <div class="stat-value">{{ $attendanceRate }}%</div>
            <div class="stat-subtext">Last 30 days</div>
        </div>
    </div>

    <!-- Monthly Comparison -->
    <div class="chart-card" style="margin-bottom: 30px;">
        <h3>Monthly Performance</h3>
        <div style="display: flex; justify-content: space-around; align-items: center; padding: 20px;">
            <div style="text-align: center;">
                <div style="font-size: 14px; color: #666; margin-bottom: 10px;">This Month</div>
                <div style="font-size: 42px; font-weight: 700; color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};">
                    {{ $thisMonthLessons }}
                </div>
                <div style="font-size: 12px; color: #999;">Completed Lessons</div>
            </div>
            <div style="font-size: 48px; color: #e5e7eb;">→</div>
            <div style="text-align: center;">
                <div style="font-size: 14px; color: #666; margin-bottom: 10px;">Last Month</div>
                <div style="font-size: 42px; font-weight: 700; color: #9ca3af;">
                    {{ $lastMonthLessons }}
                </div>
                <div style="font-size: 12px; color: #999;">Completed Lessons</div>
            </div>
            <div style="text-align: center;">
                @php
                    $difference = $thisMonthLessons - $lastMonthLessons;
                    $trend = $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'neutral');
                    $trendIcon = $difference > 0 ? '↑' : ($difference < 0 ? '↓' : '→');
                @endphp
                <div style="font-size: 14px; color: #666; margin-bottom: 10px;">Change</div>
                <div class="trend-indicator trend-{{ $trend }}">
                    {{ $trendIcon }} {{ abs($difference) }} lessons
                </div>
                @if($lastMonthLessons > 0)
                    <div style="font-size: 12px; color: #999; margin-top: 5px;">
                        ({{ round(($difference / $lastMonthLessons) * 100, 1) }}%)
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <!-- Lessons by Month Chart -->
        <div class="chart-card">
            <h3>Lessons Trend (6 Months)</h3>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Lessons by Status -->
        <div class="chart-card">
            <h3>Lessons by Status (30 Days)</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Tables -->
    <div class="charts-grid">
        <!-- Top Students -->
        <div class="chart-card">
            <h3>Top Students</h3>
            @if($topStudents->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Completed Lessons</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topStudents as $record)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <div class="student-avatar">
                                            {{ strtoupper(substr($record->student->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <span>{{ $record->student->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td><strong>{{ $record->lesson_count }}</strong> lessons</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">No student data available</div>
            @endif
        </div>

        <!-- Upcoming Lessons -->
        <div class="chart-card">
            <h3>Upcoming Schedule</h3>
            @if($upcomingLessons->count() > 0)
                <div style="max-height: 400px; overflow-y: auto;">
                    @foreach($upcomingLessons as $lesson)
                        <div class="upcoming-lesson">
                            <div class="lesson-time">
                                {{ $lesson->scheduled_at->format('M d, Y - g:i A') }}
                            </div>
                            <div class="lesson-student">
                                {{ $lesson->student->name ?? 'Unknown' }} 
                                | {{ $lesson->course->title ?? 'N/A' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">No upcoming lessons scheduled</div>
            @endif
        </div>
    </div>

    <!-- Average Grade Display -->
    @if($avgGrade)
        <div class="chart-card" style="margin-top: 20px;">
            <h3>Average Session Grade</h3>
            <div style="text-align: center; padding: 30px;">
                <div style="font-size: 72px; font-weight: 700; color: {{ $school->schoolSetting->primary_color ?? '#667eea' }};">
                    {{ number_format($avgGrade, 1) }}
                </div>
                <div style="font-size: 18px; color: #666; margin-top: 10px;">out of 100</div>
                <div style="margin-top: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px;">
                    <div style="font-size: 14px; color: #374151;">
                        Performance Rating: 
                        <strong style="color: {{ $avgGrade >= 90 ? '#10b981' : ($avgGrade >= 75 ? '#f59e0b' : '#ef4444') }};">
                            {{ $avgGrade >= 90 ? 'Excellent' : ($avgGrade >= 75 ? 'Good' : 'Needs Improvement') }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Monthly Lessons Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = @json($lessonsByMonth);
    
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => {
                const [year, month] = item.month.split('-');
                return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Completed Lessons',
                data: monthlyData.map(item => item.count),
                borderColor: '{{ $school->schoolSetting->primary_color ?? "#667eea" }}',
                backgroundColor: '{{ $school->schoolSetting->primary_color ?? "#667eea" }}20',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($lessonsByStatus);
    
    const statusColors = {
        'completed': '#10b981',
        'scheduled': '#3b82f6',
        'cancelled': '#ef4444',
        'no-show': '#f59e0b'
    };
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
            datasets: [{
                data: statusData.map(item => item.count),
                backgroundColor: statusData.map(item => statusColors[item.status] || '#6b7280')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

@endsection