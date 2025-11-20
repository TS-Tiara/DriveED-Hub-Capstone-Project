<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $slug = $school?->slug ?? 'default';
        $backgroundImage = asset('images/bg' . $slug . '.jpg');
        $schoolName = $school->name ?? 'DriveEd Hub';
        
        // Get custom colors from school settings or use defaults
        $primaryColor = $school?->settings?->primary_color ?? '#2563eb';
        $secondaryColor = $school?->settings?->secondary_color ?? '#f59e0b';
    @endphp
    <title>{{ $schoolName }} - Guest Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
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
            position: fixed;
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
            background: var(--primary-gradient);
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
            background: var(--primary-gradient);
            transform: skew(-20deg);
            z-index: -1;
        }

        .welcome-section {
            background: var(--secondary-gradient);
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

        .container {
            max-width: 500px;
            margin: 20px auto;
            padding: 15px;
        }

        .registration-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 30px;
        }

        h2 {
            color: #333;
            margin-bottom: 8px;
            text-align: center;
            font-size: 1.6rem;
        }

        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 13px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .error {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 5px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(37, 99, 235, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .back-to-login {
            text-align: center;
            margin-top: 15px;
        }

        .back-to-login a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .header {
                height: auto;
                flex-direction: column;
            }

            .logo-section::after {
                display: none;
            }

            .welcome-section {
                padding-left: 0;
            }

            .welcome-section h1 {
                font-size: 20px;
            }

            .welcome-section p {
                font-size: 14px;
            }

            .container {
                padding: 10px;
            }

            .registration-card {
                padding: 25px;
            }
        }
    </style>
    <style>
        :root {
            --school-bg: url('{{ $backgroundImage }}');
            --primary-gradient: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $primaryColor }}dd 100%);
            --secondary-gradient: linear-gradient(135deg, {{ $secondaryColor }} 0%, {{ $secondaryColor }}dd 100%);
        }
    </style>
</head>
<body style="--school-bg: url('{{ $backgroundImage }}')">
    <div class="header">
        <div class="logo-section">
            {{ $schoolName }}
        </div>
        <div class="welcome-section">
            <h1>Student Registration</h1>
            <p>Join us today and start your journey</p>
        </div>
    </div>

    <div class="container">
        <div class="registration-card">
            <h2>Create Your Account</h2>
            <p class="subtitle">Register to browse courses and start your driving journey</p>

            @if (session('success'))
                <div class="success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="error" style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('schools.registration.submit', $school) }}">
                @csrf

                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="contact">Contact Number *</label>
                    <input type="text" id="contact" name="contact" value="{{ old('contact') }}" required>
                    @error('contact')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="{{ old('address') }}">
                    @error('address')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <button type="submit" class="submit-btn">Create Account</button>
            </form>

            <div class="back-to-login">
                <a href="{{ route('schools.login', $school) }}">← Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
