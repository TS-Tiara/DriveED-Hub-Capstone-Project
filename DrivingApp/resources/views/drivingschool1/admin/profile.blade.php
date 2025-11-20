<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    @endphp
    <title>Profile - {{ $schoolName }}</title>
</head>
<body>
    <h1>Admin Profile - {{ $schoolName }}</h1>
    <p>Manage account information and preferences for administrators of {{ $schoolName }}.</p>

    <p><a href="{{ $schoolRoute('admin.dashboard') }}">← Back to Dashboard</a></p>
</body>
</html>