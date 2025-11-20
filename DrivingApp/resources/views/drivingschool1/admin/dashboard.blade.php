<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    @endphp
    <title>Admin Dashboard - {{ $schoolName }}</title>
</head>
<body>
    <h1>Admin Dashboard - {{ $schoolName }}</h1>

    @if(session('success'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <h2>Manage Users</h2>
    <ul>
    <li><a href="{{ route('schools.admin.createAccount', $school) }}">Create Account</a></li>
    <li><a href="{{ route('schools.admin.students', $school) }}">View Students</a></li>
    <li><a href="{{ route('schools.admin.instructors', $school) }}">View Instructors</a></li>
    </ul>

    <h2>Manage Schedules</h2>
    <ul>
    <li><a href="{{ route('schools.admin.schedules.create', $school) }}">Create Schedule</a></li>
    <li><a href="{{ route('schools.admin.schedules', $school) }}">View Schedules</a></li>
    <li><a href="{{ route('schools.admin.timeslots.index', $school) }}">Manage Time Slots</a></li>
    </ul>

    <h2>Reports</h2>
    <ul>
    <li><a href="{{ route('schools.admin.reports.students', $school) }}">Student Progress Report</a></li>
    <li><a href="{{ route('schools.admin.reports.instructors', $school) }}">Instructor Report</a></li>
    <li><a href="{{ route('schools.admin.reports.logs', $school) }}">System Logs</a></li>
    </ul>

    <h2>Account Settings</h2>
    <ul>
        <li><a href="{{ route('schools.admin.profile', $school) }}">Profile</a></li>
        <li>
            <form method="POST" action="{{ route('schools.logout', $school) }}" style="display: inline;">
                @csrf
                <button type="submit" style="background: none; border: none; color: blue; text-decoration: underline; cursor: pointer;">
                    Logout
                </button>
            </form>
        </li>
    </ul>
</body>
</html>