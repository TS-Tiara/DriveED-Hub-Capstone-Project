<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
        $instructor = Auth::guard('instructor')->user();
    @endphp
    <title>Instructor Dashboard - {{ $schoolName }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .header h1 {
            color: #1f2937;
            font-size: 28px;
        }

        .welcome-text {
            color: #6b7280;
            font-size: 16px;
            margin-top: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-left: 10px;
        }

        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .card-links {
            list-style: none;
        }

        .card-links li {
            margin-bottom: 10px;
        }

        .card-links a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .card-links a:hover {
            background: rgba(255, 255, 255, 0.3);
            padding-left: 20px;
        }

        .card-links a::before {
            content: "→";
            margin-right: 10px;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .logout-btn {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>👨‍🏫 Instructor Dashboard - {{ $schoolName }}</h1>
                <p class="welcome-text">
                    Welcome back, {{ $instructor->name }}!
                    <span class="status-badge {{ $instructor->status === 'active' ? 'status-active' : 'status-inactive' }}">
                        {{ ucfirst($instructor->status) }}
                    </span>
                </p>
            </div>
            <form method="POST" action="{{ $schoolRoute('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="dashboard-grid">
            <div class="card">
                <h2 class="card-title">📅 My Schedule</h2>
                <ul class="card-links">
                    <li><a href="{{ $schoolRoute('instructor.timeslots.index') }}">Available Time Slots</a></li>
                    <li><a href="{{ $schoolRoute('instructor.schedule') }}">My Teaching Schedule</a></li>
                    <li><a href="#">Today's Classes</a></li>
                </ul>
            </div>

            <div class="card">
                <h2 class="card-title">👥 Students</h2>
                <ul class="card-links">
                    <li><a href="#">My Students</a></li>
                    <li><a href="#">Student Progress</a></li>
                    <li><a href="#">Attendance Records</a></li>
                </ul>
            </div>

            <div class="card">
                <h2 class="card-title">👤 My Account</h2>
                <ul class="card-links">
                    <li><a href="#">Profile Settings</a></li>
                    <li><a href="#">Availability Settings</a></li>
                    <li><a href="#">Performance Reports</a></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>