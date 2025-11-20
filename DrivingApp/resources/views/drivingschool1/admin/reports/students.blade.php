<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    @endphp
    <title>Student Reports - {{ $schoolName }}</title>
</head>
<body>
    <h1>Student Reports - {{ $schoolName }}</h1>
    <p>This is where the admin can review student performance and progress metrics for {{ $schoolName }}.</p>

    <p><a href="{{ $schoolRoute('admin.dashboard') }}">← Back to Dashboard</a></p>
</body>
</html>