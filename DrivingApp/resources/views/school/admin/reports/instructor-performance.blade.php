<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Performance Report - {{ config('app.name') }}</title>
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
        .form-group input {
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
            background: linear-gradient(to right, #3498db, #2980b9);
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-high {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .badge-medium {
            background: #fff3e0;
            color: #ef6c00;
        }
        .badge-low {
            background: #ffebee;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⭐ Instructor Performance Report</h1>
            <a href="{{ route('schools.admin.reports.index', ['school' => $school->slug]) }}" class="btn btn-secondary">← Back to Reports</a>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('schools.admin.reports.instructor-performance', ['school' => $school->slug]) }}" class="filter-form">
                <div class="form-group">
                    <label for="date_from">From Date</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from', now()->subMonths(3)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label for="date_to">To Date</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>
                <button type="submit" class="btn">Generate Report</button>
            </form>
        </div>

        <!-- Instructor Performance Table -->
        <div class="chart-section">
            <h2>Instructor Performance Metrics</h2>
            <table>
                <thead>
                    <tr>
                        <th>Instructor</th>
                        <th>Total Lessons</th>
                        <th>Completed</th>
                        <th>Completion Rate</th>
                        <th>Avg Rating</th>
                        <th>Students Taught</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['instructors'] as $instructor)
                        <tr>
                            <td>{{ $instructor->instructor_name }}</td>
                            <td>{{ $instructor->total_lessons }}</td>
                            <td>{{ $instructor->completed_lessons }}</td>
                            <td>
                                @php
                                    $completionRate = $instructor->total_lessons > 0 
                                        ? ($instructor->completed_lessons / $instructor->total_lessons) * 100 
                                        : 0;
                                    $badgeClass = $completionRate >= 80 ? 'badge-high' : ($completionRate >= 50 ? 'badge-medium' : 'badge-low');
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ number_format($completionRate, 1) }}%</span>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $completionRate }}%"></div>
                                </div>
                            </td>
                            <td>
                                {{ number_format($instructor->avg_rating, 1) }}
                                <span class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= round($instructor->avg_rating))
                                            ★
                                        @else
                                            ☆
                                        @endif
                                    @endfor
                                </span>
                            </td>
                            <td>{{ $instructor->students_taught }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
