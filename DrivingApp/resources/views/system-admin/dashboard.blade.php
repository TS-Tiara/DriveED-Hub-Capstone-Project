@extends('layouts.system-admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Schools</h3>
        <div class="value" style="color: #8b5cf6;">{{ $stats['total_schools'] }}</div>
    </div>

    <div class="stat-card">
        <h3>Total Students</h3>
        <div class="value" style="color: #3b82f6;">{{ $stats['total_students'] }}</div>
        <div class="subtext">{{ $stats['active_students'] }} active</div>
    </div>

    <div class="stat-card">
        <h3>Total Instructors</h3>
        <div class="value" style="color: #10b981;">{{ $stats['total_instructors'] }}</div>
        <div class="subtext">{{ $stats['active_instructors'] }} active</div>
    </div>

    <div class="stat-card">
        <h3>Total Courses</h3>
        <div class="value" style="color: #f97316;">{{ $stats['total_courses'] }}</div>
    </div>

    <div class="stat-card">
        <h3>Total Bookings</h3>
        <div class="value" style="color: #6366f1;">{{ $stats['total_bookings'] }}</div>
        <div class="subtext">{{ $stats['pending_bookings'] }} pending</div>
    </div>

    <div class="stat-card">
        <h3>Completed Bookings</h3>
        <div class="value" style="color: #14b8a6;">{{ $stats['completed_bookings'] }}</div>
    </div>

    <div class="stat-card">
        <h3>Total Revenue</h3>
        <div class="value" style="color: #059669;">₱{{ number_format($stats['total_revenue'], 2) }}</div>
    </div>

    <div class="stat-card">
        <h3>Pending Payments</h3>
        <div class="value" style="color: #f59e0b;">₱{{ number_format($stats['pending_payments'], 2) }}</div>
    </div>
</div>

<!-- Schools Overview -->
<div class="card">
    <div class="card-header">
        <h3>Schools Overview</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>School Name</th>
                        <th>Students</th>
                        <th>Instructors</th>
                        <th>Admins</th>
                        <th>Courses</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schools as $school)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $school->name }}</div>
                            <div style="font-size: 0.75rem; color: #9ca3af;">{{ $school->slug }}</div>
                        </td>
                        <td>{{ $school->students_count }}</td>
                        <td>{{ $school->instructors_count }}</td>
                        <td>{{ $school->admins_count }}</td>
                        <td>{{ $school->courses_count }}</td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="card">
    <div class="card-header">
        <h3>Recent System Activities</h3>
    </div>
    <div class="card-body">
        @foreach($recentActivities->take(10) as $activity)
        <div style="display: flex; align-items: start; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #f3f4f6; margin-bottom: 16px;">
            <div style="width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; background: 
                @if($activity->level === 'critical' || $activity->level === 'error') #ef4444
                @elseif($activity->level === 'warning') #f59e0b
                @else #10b981
                @endif;">
            </div>
            <div style="flex: 1;">
                <div style="font-size: 0.875rem; color: #1f2937;">{{ $activity->message }}</div>
                <div style="margin-top: 4px; display: flex; gap: 8px; font-size: 0.75rem; color: #9ca3af;">
                    <span>{{ $activity->school ? $activity->school->name : 'System' }}</span>
                    <span>•</span>
                    <span>{{ $activity->category }}</span>
                    <span>•</span>
                    <span>{{ $activity->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection