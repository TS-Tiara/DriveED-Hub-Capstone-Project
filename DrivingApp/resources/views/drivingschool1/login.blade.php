<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
    $slug = $school?->slug ?? 'drivingschool1';
        $backgroundImage = asset('images/bg' . $slug . '.jpg');
        $schoolName = $school->name ?? 'DriveEd Hub';
    @endphp
    <title>{{ $schoolName }} - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--school-bg) no-repeat center center fixed;
            background-size: cover;
            filter: blur(3px);
            z-index: -2;
        }

        body::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.1);
            z-index: -1;
        }

        .header {
            display: flex;
            width: 100%;
            height: 80px;
            position: relative;
            z-index: 10;
        }

        .logo-section {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: bold;
            position: relative;
        }

        .logo-section::before {
            content: "🎓";
            font-size: 32px;
            margin-right: 10px;
        }

        .logo-section::after {
            content: "";
            position: absolute;
            right: -20px;
            top: 0;
            bottom: 0;
            width: 40px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: skew(-20deg);
            z-index: -1;
        }

        .welcome-section {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #1f2937;
            padding-left: 30px;
        }

        .welcome-section h1 {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .welcome-section p {
            font-size: 16px;
            font-weight: 500;
        }

        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 80px);
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.98);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 400px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .login-title {
            font-size: 32px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            position: relative;
        }

        .login-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 30px;
        }

        .login-title::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
            border-radius: 2px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background: white;
        }

        input::placeholder {
            color: #9ca3af;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #2563eb;
        }

        .forgot-password {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .login-button:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .error-message {
            color: #dc2626;
            font-size: 13px;
            margin-top: 5px;
            text-align: left;
        }

        @media (max-width: 768px) {
            .header {
                height: 70px;
            }

            .logo-section, .welcome-section {
                font-size: 20px;
            }

            .welcome-section h1 {
                font-size: 22px;
            }

            .login-container {
                width: 90%;
                max-width: 400px;
                padding: 30px 25px;
            }

            .login-title {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .header {
                flex-direction: column;
                height: auto;
            }

            .logo-section::after {
                display: none;
            }

            .welcome-section {
                padding-left: 0;
            }

            .login-container {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body style="--school-bg: url('{{ $backgroundImage }}')">
    <div class="header">
        <div class="logo-section">
            DriveEd Hub
        </div>
        <div class="welcome-section">
            <h1>Welcome to {{ $schoolName }}!</h1>
            <p>Select your role below</p>
        </div>
    </div>

    <div class="main-content">
        <div class="login-container">
            <h2 class="login-title">Login</h2>
            <p class="login-subtitle">Admin • Instructor • Student</p>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('schools.login.submit', $school) }}">
                @csrf
                <div class="form-group">
                    <input 
                        type="email" 
                        name="email" 
                        placeholder="Email Address" 
                        value="{{ old('email') }}"
                        required>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <input 
                        type="password" 
                        name="password" 
                        placeholder="Password" 
                        required>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="login-button">Log In</button>
            </form>
        </div>
    </div>
</body>
</html>