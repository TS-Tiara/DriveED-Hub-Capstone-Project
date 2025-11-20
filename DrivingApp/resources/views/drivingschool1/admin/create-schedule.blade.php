<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    @endphp
    <title>Create Schedule - {{ $schoolName }}</title>
</head>
<body>
    <h1>Create Schedule</h1>
    <a href="{{ $schoolRoute('admin.dashboard') }}">Back to Dashboard</a>

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <ul style="color:red">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ $schoolRoute('admin.schedules.store') }}">
        @csrf
        <label>Instructor:</label>
        <select name="instructor_id">
            @foreach($instructors as $instructor)
                <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
            @endforeach
        </select>
        <br><br>

        <label>Date:</label>
        <input type="date" name="date" required>
        <br><br>

        <label>Start Time:</label>
        <input type="time" name="start_time" required>
        <br><br>

        <label>End Time:</label>
        <input type="time" name="end_time" required>
        <br><br>

        <button type="submit">Create Schedule</button>
    </form>
</body>
</html>
