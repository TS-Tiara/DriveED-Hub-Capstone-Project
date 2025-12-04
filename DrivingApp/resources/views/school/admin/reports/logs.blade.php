<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    @endphp
    <title>System Logs - {{ $schoolName }}</title>
</head>
<body>
    <h1>System Logs - {{ $schoolName }}</h1>
    <p>Recent activities and audit logs scoped to {{ $schoolName }} will appear in this section.</p>
</body>
</html>