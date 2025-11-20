<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driving Lessons Report - {{ config('app.name') }}</title>
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
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 5px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #27ae60, #2ecc71);
            border-radius: 10px;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚗 Driving Lessons Report</h1>
            <a href="{{ route('schools.admin.reports.index', ['school' => $school->slug]) }}" class="btn btn-secondary">← Back to Reports</a>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('schools.admin.reports.driving-lessons', ['school' => $school->slug]) }}" class="filter-form">
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

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="card">
                <h3>Total Lessons</h3>
                <div class="value">{{ $data['total_lessons'] }}</div>
            </div>
            <div class="card">
                <h3>Completed Lessons</h3>
                <div class="value">{{ $data['completed_lessons'] }}</div>
            </div>
            <div class="card">
                <h3>Completion Rate</h3>
                <div class="value">{{ number_format($data['completion_rate'], 1) }}%</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $data['completion_rate'] }}%"></div>
                </div>
            </div>
        </div>

        <!-- Lessons by Status -->
        <div class="chart-section">
            <h2>Lessons by Status</h2>
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalByStatus = $data['lessons_by_status']->sum('count');
                    @endphp
                    @foreach($data['lessons_by_status'] as $status)
                        <tr>
                            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $status->status) }}</td>
                            <td>{{ $status->count }}</td>
                            <td>{{ $totalByStatus > 0 ? number_format(($status->count / $totalByStatus) * 100, 1) : 0 }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Lessons by Instructor -->
        <div class="chart-section">
            <h2>Lessons by Instructor</h2>
            <table>
                <thead>
                    <tr>
                        <th>Instructor</th>
                        <th>Total Lessons</th>
                        <th>Completed</th>
                        <th>Pending</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['lessons_by_instructor'] as $instructor)
                        <tr>
                            <td>{{ $instructor->instructor_name }}</td>
                            <td>{{ $instructor->total_lessons }}</td>
                            <td>{{ $instructor->completed_lessons }}</td>
                            <td>{{ $instructor->total_lessons - $instructor->completed_lessons }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Daily Lessons -->
        <div class="chart-section">
            <h2>Lessons by Date</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Lessons Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['lessons_by_date'] as $lesson)
                        <tr>
                            <td>{{ $lesson->lesson_date }}</td>
                            <td>{{ $lesson->count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
