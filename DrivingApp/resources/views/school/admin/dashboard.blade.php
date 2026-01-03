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
        padding: 16px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s ease;
        border-radius: 8px;
        margin-bottom: 4px;
    }

    .activity-item:hover {
        background: #f9fafb;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 4px;
    }
    
    .activity-email {
        font-size: 0.875rem;
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
        padding: 12px;
        margin-top: 16px;
        color: {{ $primaryColor }};
        font-weight: 600;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .view-all-link:hover {
        background: #f9fafb;
        color: {{ $secondaryColor }};
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
        <a href="{{ $schoolRoute('admin.userManagement') }}" class="stat-card students" onclick="loadContent(this.href); return false;" style="text-decoration: none; cursor: pointer;">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Students</div>
                        <div class="stat-value">{{ $totalStudents }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">
                    <strong>{{ $activeStudents }}</strong> Active · <strong>{{ $inactiveStudents }}</strong> Inactive
                    @if(isset($studentGrowth))
                        <br>
                        <span style="color: {{ $studentGrowth >= 0 ? '#10b981' : '#ef4444' }}; font-weight: 600;">
                            {{ $studentGrowth >= 0 ? '↑' : '↓' }} {{ abs($studentGrowth) }}% this month
                        </span>
                    @endif
                </div>
            </div>
        </a>
        
        <a href="{{ $schoolRoute('admin.userManagement') }}" class="stat-card instructors" onclick="loadContent(this.href); return false;" style="text-decoration: none; cursor: pointer;">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Instructors</div>
                        <div class="stat-value">{{ $totalInstructors }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">
                    <strong>{{ $availableInstructors }}</strong> Available · <strong>{{ $activeInstructors }}</strong> Active
                    @if(isset($instructorGrowth))
                        <br>
                        <span style="color: {{ $instructorGrowth >= 0 ? '#10b981' : '#ef4444' }}; font-weight: 600;">
                            {{ $instructorGrowth >= 0 ? '↑' : '↓' }} {{ abs($instructorGrowth) }}% this month
                        </span>
                    @endif
                </div>
            </div>
        </a>
        
        <a href="{{ $schoolRoute('admin.userManagement') }}" class="stat-card growth" onclick="loadContent(this.href); return false;" style="text-decoration: none; cursor: pointer;">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">New Students</div>
                        <div class="stat-value">{{ $studentsThisMonth ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">Registered this month</div>
            </div>
        </a>
        
        <a href="{{ $schoolRoute('admin.userManagement') }}" class="stat-card active" onclick="loadContent(this.href); return false;" style="text-decoration: none; cursor: pointer;">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active Users</div>
                        <div class="stat-value">{{ $activeStudents + $activeInstructors }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">Currently active accounts</div>
            </div>
        </a>
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

