@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $useGradient = $settings?->use_gradient_header ?? true;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
    }
    
    .quick-action-btn {
        padding: 15px 20px;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        text-decoration: none;
        border-radius: 10px;
        text-align: center;
        font-weight: 500;
        transition: all 0.3s ease;
        display: block;
    }
    
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        color: white;
    }
    
    /* Activity List */
    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .activity-item {
        padding: 14px 0;
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
        color: #1f2937;
        margin-bottom: 2px;
    }
    
    .activity-email {
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    /* Section Title */
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 15px;
    }
    
    /* Chart container */
    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 15px;
    }
    
    /* View All Link */
    .view-all-link {
        display: block;
        text-align: center;
        margin-top: 15px;
        color: {{ $primaryColor }};
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .view-all-link:hover {
        text-decoration: underline;
    }
    
    /* Two Column Grid */
    .two-column-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    @media (max-width: 860px) {
        .two-column-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome to {{ $schoolName }} Admin Dashboard</p>
        </div>
    </div>

    @if(session('success'))
    <div class="flash-message success">
        <div class="flash-icon">✓</div>
        <div class="flash-content">
            <div class="flash-title">Success!</div>
            <div class="flash-text">{{ session('success') }}</div>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif

    <!-- Key Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Students</div>
            <div class="stat-value">{{ $totalStudents }}</div>
            <div class="stat-detail">{{ $activeStudents }} Active · {{ $inactiveStudents }} Inactive</div>
        </div>
        
        <div class="stat-card info">
            <div class="stat-label">Total Instructors</div>
            <div class="stat-value">{{ $totalInstructors }}</div>
            <div class="stat-detail">{{ $availableInstructors }} Available · {{ $activeInstructors }} Active</div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ $totalStudents + $totalInstructors }}</div>
            <div class="stat-detail">Combined student & instructor count</div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-label">Active Users</div>
            <div class="stat-value">{{ $activeStudents + $activeInstructors }}</div>
            <div class="stat-detail">Currently active accounts</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="content-card">
        <div class="content-card-header">Quick Actions</div>
        <div class="content-card-body">
            <div class="quick-actions">
                <a href="{{ $schoolRoute('admin.userManagement') }}" class="quick-action-btn" onclick="loadContent(this.href); return false;">
                    Manage Users
                </a>
                <a href="{{ $schoolRoute('admin.schedules') }}" class="quick-action-btn" onclick="loadContent(this.href); return false;">
                    View Schedules
                </a>
                <a href="{{ $schoolRoute('admin.courses') }}" class="quick-action-btn" onclick="loadContent(this.href); return false;">
                    Manage Courses
                </a>
                <a href="{{ $schoolRoute('admin.bookings.index') }}" class="quick-action-btn" onclick="loadContent(this.href); return false;">
                    View Bookings
                </a>
                <a href="{{ $schoolRoute('admin.reports.index') }}" class="quick-action-btn" onclick="loadContent(this.href); return false;">
                    View Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Two Column Layout for Recent Activity -->
    <div class="two-column-grid">
        <!-- Recent Students -->
        <div class="content-card">
            <div class="content-card-header">Recent Students</div>
            <div class="content-card-body">
                @if($recentStudents->count() > 0)
                    <ul class="activity-list">
                        @foreach($recentStudents as $student)
                        <li class="activity-item">
                            <div>
                                <div class="activity-name">{{ $student->name }}</div>
                                <div class="activity-email">{{ $student->email }}</div>
                            </div>
                            <div>
                                <span class="badge badge-{{ $student->status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ $schoolRoute('admin.userManagement') }}" onclick="loadContent(this.href); return false;" class="view-all-link">
                        View All Students →
                    </a>
                @else
                    <div class="empty-state">
                        <div class="empty-state-text">No students yet</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Instructors -->
        <div class="content-card">
            <div class="content-card-header">Recent Instructors</div>
            <div class="content-card-body">
                @if($recentInstructors->count() > 0)
                    <ul class="activity-list">
                        @foreach($recentInstructors as $instructor)
                        <li class="activity-item">
                            <div>
                                <div class="activity-name">{{ $instructor->name }}</div>
                                <div class="activity-email">{{ $instructor->email }}</div>
                            </div>
                            <div>
                                <span class="badge badge-{{ $instructor->availability === 'available' ? 'success' : 'warning' }}">
                                    {{ ucfirst($instructor->availability) }}
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ $schoolRoute('admin.userManagement') }}" onclick="loadContent(this.href); return false;" class="view-all-link">
                        View All Instructors →
                    </a>
                @else
                    <div class="empty-state">
                        <div class="empty-state-text">No instructors yet</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Enrollment Trend Chart -->
    <div class="content-card">
        <div class="content-card-header">Student Enrollment Trend (Last 30 Days)</div>
        <div class="content-card-body">
            <div class="chart-container">
                <canvas id="enrollmentChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Enrollment Trend Chart
    const enrollmentData = @json($enrollmentData);
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
                borderColor: '{{ $primaryColor }}',
                backgroundColor: '{{ $primaryColor }}20',
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

@endsection

