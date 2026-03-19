<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $slug = $school?->slug ?? 'default';
        $settings = $school?->schoolSetting;
        
        // School branding
        $schoolName = $school->name ?? 'DriveEd Hub';
        
        // Custom colors
        $primaryColor = $settings?->primary_color ?? '#2563eb';
        $secondaryColor = $settings?->secondary_color ?? '#f59e0b';
        
        // Header settings
        $headerHeight = $settings?->login_header_height ?? 60;
        $headerTextColor = $settings?->login_header_text_color ?? '#ffffff';
        $headerShadow = $settings?->login_header_shadow ?? true;
        $headerBgType = $settings?->login_header_bg_type ?? 'gradient';
        $headerBgColor = $settings?->login_header_bg_color;
        $headerBgImage = $settings?->login_header_bg_image;
        $useGradient = $settings?->use_gradient_header ?? false;

        if ($headerBgType === 'solid' && $headerBgColor) {
            $headerBackground = $headerBgColor;
        } elseif ($headerBgType === 'image' && $headerBgImage) {
            $headerBackground = "url('" . asset('storage/' . $headerBgImage) . "')";
        } elseif ($useGradient) {
            $headerBackground = "linear-gradient(135deg, {$primaryColor} 0%, {$secondaryColor} 100%)";
        } else {
            $headerBackground = $primaryColor;
        }
        
        // Page background
        $pageBgType = $settings?->login_page_bg_type ?? 'color';
        $pageBgColor = $settings?->login_page_bg_color ?? '#f5f5f5';
        $pageBgImage = $settings?->login_page_bg_image;
        $pageBgOpacity = $settings?->login_page_bg_opacity ?? 100;
        
        if ($pageBgType === 'image' && $pageBgImage) {
            $pageBackground = "url('" . asset('storage/' . $pageBgImage) . "')";
        } else {
            $pageBackground = $pageBgColor;
        }
    @endphp
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $schoolName }} - Forgot Password</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            @if($pageBgType === 'image' && $pageBgImage)
            background: {{ $pageBackground }} no-repeat center center fixed;
            background-size: cover;
            @else
            background: {{ $pageBackground }};
            @endif
            opacity: {{ $pageBgOpacity / 100 }};
            z-index: -1;
        }
        .login-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: {{ $headerHeight }}px;
            background: {{ $headerBackground }};
            color: {{ $headerTextColor }};
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 25px;
            @if($headerShadow) box-shadow: 0 3px 20px rgba(0,0,0,0.15); @endif
        }
        .header-school-name { font-size: 20px; font-weight: 600; text-shadow: 0 1px 3px rgba(0,0,0,0.3); }
        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - {{ $headerHeight }}px);
            margin-top: {{ $headerHeight }}px;
            padding: 15px;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.98);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 400px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .login-title { font-size: 22px; font-weight: bold; color: #1f2937; margin-bottom: 10px; }
        .login-subtitle { font-size: 13px; color: #6b7280; margin-bottom: 25px; line-height: 1.5; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; text-align: left; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.9);
        }
        input[type="email"]:focus { outline: none; border-color: {{ $primaryColor }}; box-shadow: 0 0 0 3px {{ $primaryColor }}20; }
        .login-button {
            width: 100%;
            padding: 14px;
            background: {{ $primaryColor }};
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .login-button:hover { opacity: 0.9; }
        .back-link { margin-top: 25px; padding-top: 15px; border-top: 1px solid #e5e7eb; }
        .back-link a { color: #2563eb; text-decoration: none; font-size: 13px; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <nav class="login-header">
        <div class="header-school-name">{{ $schoolName }}</div>
    </nav>

    <div class="main-content">
        <div class="login-container">
            <h2 class="login-title">Forgot Password</h2>
            <p class="login-subtitle">
                Enter your registered email address and we'll send you a secure link to reset your password.
            </p>

            @include('partials.toast-notifications')

            <form method="POST" action="{{ route('schools.password.email', $school) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" required autofocus>
                </div>
                <button type="submit" class="login-button">Send Reset Link</button>
            </form>
            
            <div class="back-link">
                <a href="{{ route('schools.login', $school) }}">← Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
