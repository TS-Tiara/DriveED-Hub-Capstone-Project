<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    @endphp
    <title>Edit Schedule - {{ $schoolName }}</title>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 20px;
            margin-right: 10px;
            cursor: pointer;
        }
        .btn-save {
            background-color: #4CAF50;
            color: white;
            border: none;
        }
        .btn-cancel {
            background-color: #f44336;
            color: white;
            border: none;
            display: inline-block;
            text-decoration: none;
            border-radius: 4px;
            padding: 10px 20px;
        }
        .error {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>Edit Schedule</h1>
    <a href="{{ $schoolRoute('admin.schedules') }}">← Back to Schedules</a>

    @if($errors->any())
        <div class="error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $schoolRoute('admin.schedules.update', ['id' => $schedule->id]) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Instructor:</label>
            <select name="instructor_id" required>
                @foreach($instructors as $instructor)
                    <option value="{{ $instructor->id }}" 
                        {{ $schedule->instructor_id == $instructor->id ? 'selected' : '' }}>
                        {{ $instructor->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Date:</label>
            <input type="date" name="date" value="{{ $schedule->date }}" required>
        </div>

        <div class="form-group">
            <label>Start Time:</label>
            <input type="time" name="start_time" value="{{ $schedule->start_time }}" required>
        </div>

        <div class="form-group">
            <label>End Time:</label>
            <input type="time" name="end_time" value="{{ $schedule->end_time }}" required>
        </div>

        <div class="form-group">
            <label>Status:</label>
            <select name="status" required>
                @foreach(['available', 'booked', 'removed'] as $option)
                    <option value="{{ $option }}" {{ $schedule->status === $option ? 'selected' : '' }}>
                        {{ ucfirst($option) }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-save">Update Schedule</button>
    <a href="{{ $schoolRoute('admin.schedules') }}" class="btn-cancel">Cancel</a>
    </form>
</body>
</html>