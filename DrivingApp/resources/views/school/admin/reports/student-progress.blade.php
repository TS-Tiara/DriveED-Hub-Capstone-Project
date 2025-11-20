<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Progress Report - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        h1 {
            color: #2c3e50;
            font-size: 32px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-secondary {
            background: #95a5a6;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card h3 {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
        }
        .card .value {
            font-size: 36px;
            font-weight: bold;
            color: #3498db;
        }
        .card .subtitle {
            color: #95a5a6;
            font-size: 12px;
            margin-top: 5px;
        }
        .chart-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .chart-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .rating-stars {
            color: #f39c12;
            font-size: 16px;
        }
        .progress-bar {
            width: 100%;
            height: 15px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 3px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #27ae60, #2ecc71);
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        .progress-item {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        .progress-item h4 {
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .progress-item p {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Student Progress Report</h1>
            <a href="{{ route('schools.admin.reports.index', ['school' => $school->slug]) }}" class="btn btn-secondary">← Back to Reports</a>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('schools.admin.reports.student-progress', ['school' => $school->slug]) }}" class="filter-form">
                <div class="form-group">
                    <label for="student_id">Select Student (Optional)</label>
                    <select id="student_id" name="student_id">
                        <option value="">All Students</option>
                        <!-- You can populate this with actual students if needed -->
                    </select>
                </div>
                <button type="submit" class="btn">Generate Report</button>
            </form>
        </div>

        @if(isset($data['student']))
            <!-- Individual Student Report -->
            <div class="summary-cards">
                <div class="card">
                    <h3>Student Name</h3>
                    <div class="value" style="font-size: 24px;">{{ $data['student']->name }}</div>
                </div>
                <div class="card">
                    <h3>Total Lessons</h3>
                    <div class="value">{{ $data['total_lessons'] }}</div>
                </div>
                <div class="card">
                    <h3>Completed Lessons</h3>
                    <div class="value">{{ $data['completed_lessons'] }}</div>
                </div>
                <div class="card">
                    <h3>Average Performance</h3>
                    <div class="value">{{ number_format($data['avg_performance'], 1) }}</div>
                    <span class="rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($data['avg_performance']))
                                ★
                            @else
                                ☆
                            @endif
                        @endfor
                    </span>
                </div>
            </div>

            <!-- Progress Records -->
            <div class="chart-section">
                <h2>Progress Records</h2>
                @foreach($data['progress_records'] as $progress)
                    <div class="progress-item">
                        <h4>{{ $progress->lesson_type ?? 'General Practice' }} - {{ \Carbon\Carbon::parse($progress->created_at)->format('M d, Y') }}</h4>
                        <p><strong>Instructor:</strong> {{ $progress->instructor->name }}</p>
                        <p><strong>Performance Rating:</strong> 
                            {{ $progress->performance_rating }}
                            <span class="rating-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $progress->performance_rating)
                                        ★
                                    @else
                                        ☆
                                    @endif
                                @endfor
                            </span>
                        </p>
                        @if($progress->feedback)
                            <p><strong>Feedback:</strong> {{ $progress->feedback }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <!-- All Students Summary -->
            <div class="chart-section">
                <h2>Student Progress Summary</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Total Lessons</th>
                            <th>Completed</th>
                            <th>Progress</th>
                            <th>Avg Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['students'] as $student)
                            <tr>
                                <td>{{ $student->student_name }}</td>
                                <td>{{ $student->total_lessons }}</td>
                                <td>{{ $student->completed_lessons }}</td>
                                <td>
                                    @php
                                        $progressRate = $student->total_lessons > 0 
                                            ? ($student->completed_lessons / $student->total_lessons) * 100 
                                            : 0;
                                    @endphp
                                    {{ number_format($progressRate, 1) }}%
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: {{ $progressRate }}%"></div>
                                    </div>
                                </td>
                                <td>
                                    {{ number_format($student->avg_performance, 1) }}
                                    <span class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($student->avg_performance))
                                                ★
                                            @else
                                                ☆
                                            @endif
                                        @endfor
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</body>
</html>
