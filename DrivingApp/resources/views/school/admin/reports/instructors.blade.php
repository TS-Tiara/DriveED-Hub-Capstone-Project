<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    @endphp
    <title>Instructor Reports - {{ $schoolName }}</title>
</head>
<body>
    <h1>Instructor Reports - {{ $schoolName }}</h1>
    <p>Instructor performance, availability, and activity reports for {{ $schoolName }} appear here.</p>
</body>
</html>