@extends('layouts.system-admin')

@section('title', 'Dashboard')
@section('page-title', 'Platform Overview')

@section('content')
<!-- Platform Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Registered Schools</h3>
        <div class="value" style="color: #053d86;">{{ $stats['total_schools'] }}</div>
        <div class="subtext">Driving schools on platform</div>
    </div>

    <div class="stat-card">
        <h3>School Admins</h3>
        <div class="value" style="color: #0a4a9e;">{{ $stats['total_school_admins'] }}</div>
        <div class="subtext">Managing their schools</div>
    </div>

    <div class="stat-card">
        <h3>Total Users</h3>
        <div class="value" style="color: #10b981;">{{ $stats['total_users'] }}</div>
        <div class="subtext">{{ $stats['total_students'] }} students, {{ $stats['total_instructors'] }} instructors</div>
    </div>

    <div class="stat-card">
        <h3>System Logs</h3>
        <div class="value" style="color: #f97316;">{{ $stats['total_logs'] }}</div>
        <div class="subtext">{{ $stats['error_logs'] }} errors, {{ $stats['warning_logs'] }} warnings</div>
    </div>
</div>

<!-- Schools Overview -->
<div class="card">
    <div class="card-header">
        <h3>Registered Schools</h3>
        <a href="{{ route('system-admin.schools') }}" class="btn btn-primary" style="font-size: 0.875rem;">View All</a>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>School Name</th>
                        <th>Slug</th>
                        <th>Students</th>
                        <th>Instructors</th>
                        <th>Admins</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schools as $school)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $school->name }}</div>
                        </td>
                        <td>
                            <code style="background: #f3f4f6; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">{{ $school->slug }}</code>
                        </td>
                        <td>{{ $school->students_count }}</td>
                        <td>{{ $school->instructors_count }}</td>
                        <td>{{ $school->admins_count }}</td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <p class="empty-state-title">No schools registered yet</p>
                                <p class="empty-state-text">Schools will appear here once they're added to the system</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent System Activities -->
<div class="card">
    <div class="card-header">
        <h3>Recent System Logs</h3>
        <a href="{{ route('system-admin.logs') }}" class="btn btn-primary" style="font-size: 0.875rem;">View All Logs</a>
    </div>
    <div class="card-body">
        @forelse($recentActivities->take(10) as $activity)
        <div style="display: flex; align-items: start; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #f3f4f6; margin-bottom: 16px;">
            <div style="width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; flex-shrink: 0; background: 
                @if($activity->level === 'critical' || $activity->level === 'error') #ef4444
                @elseif($activity->level === 'warning') #f59e0b
                @else #10b981
                @endif;">
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 0.875rem; color: #1f2937; word-break: break-word;">{{ Str::limit($activity->message, 100) }}</div>
                <div style="margin-top: 4px; display: flex; flex-wrap: wrap; gap: 8px; font-size: 0.75rem; color: #9ca3af;">
                    <span class="badge" style="background: #f3f4f6; color: #6b7280;">{{ $activity->level }}</span>
                    <span>{{ $activity->school ? $activity->school->name : 'System' }}</span>
                    <span>•</span>
                    <span>{{ $activity->category }}</span>
                    <span>•</span>
                    <span>{{ $activity->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="empty-state-title">No recent activity</p>
            <p class="empty-state-text">Activity logs will appear here as actions are performed</p>
        </div>
        @endforelse
    </div>
</div>
@endsection