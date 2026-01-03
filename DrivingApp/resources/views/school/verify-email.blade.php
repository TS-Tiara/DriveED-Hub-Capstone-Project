<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $settings = $school?->schoolSetting;
        $schoolName = $school->name ?? 'DriveEd Hub';
        $primaryColor = $settings?->primary_color ?? '#2563eb';
        $secondaryColor = $settings?->secondary_color ?? '#f59e0b';
        
        // Header settings
        $headerLayout = $settings?->login_header_layout ?? 'horizontal';
        $logoImage = $settings?->login_logo_image;
        $logoPosition = $settings?->login_logo_position ?? 'left';
        $logoSize = $settings?->login_logo_size ?? 40;
        $schoolNameText = $settings?->login_school_name_text ?? $schoolName;
        $showSchoolName = $settings?->login_show_school_name ?? true;
        $headerHeight = $settings?->login_header_height ?? 60;
        $headerTextColor = $settings?->login_header_text_color ?? '#ffffff';
        $headerShadow = $settings?->login_header_shadow ?? true;
        $useGradient = $settings?->use_gradient_header ?? false;
        
        // Page background
        $pageBgType = $settings?->login_page_bg_type ?? 'color';
        $pageBgColor = $settings?->login_page_bg_color ?? '#f5f5f5';
        $pageBgImage = $settings?->login_page_bg_image;
        $pageBgOpacity = $settings?->login_page_bg_opacity ?? 100;
        
        // Generate header background
        if ($useGradient) {
            $headerBackground = "linear-gradient(135deg, {$primaryColor} 0%, {$secondaryColor} 100%)";
        } else {
            $headerBackground = $primaryColor;
        }
        
        // Generate page background
        if ($pageBgType === 'image' && $pageBgImage) {
            $pageBackground = "url('" . asset('storage/' . $pageBgImage) . "')";
        } else {
            $pageBackground = $pageBgColor;
        }
    @endphp
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $schoolName }} - Email Verification</title>
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
            @if($pageBgType === 'image' && $pageBgImage)
            background: {{ $pageBackground }} no-repeat center center fixed;
            background-size: cover;
            @else
            background: {{ $pageBackground }};
            @endif
            opacity: {{ $pageBgOpacity / 100 }};
            z-index: -1;
        }

        /* Header */
        .login-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: {{ $headerHeight }}px;
            background: {{ $headerBackground }};
            color: {{ $headerTextColor }};
            z-index: 1000;
            @if($headerShadow)
            box-shadow: 0 3px 20px rgba(0,0,0,0.15);
            @endif
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 25px;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        @if($logoImage)
        .header-logo {
            height: {{ $logoSize }}px;
            width: auto;
        }
        @endif

        .header-school-name {
            font-size: {{ $schoolNameSize ?? 24 }}px;
            font-weight: 600;
            color: {{ $headerTextColor }};
        }

        /* Main container with top padding for fixed header */
        .verify-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: {{ $headerHeight + 40 }}px 20px 40px;
        }

        .verify-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 480px;
            width: 100%;
        }

        .verify-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .verify-icon {
            width: 80px;
            height: 80px;
            background: {{ $primaryColor }};
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }

        h1 {
            color: #1f2937;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 14px;
        }

        .email-display {
            background: #f3f4f6;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            color: {{ $primaryColor }};
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
        }

        input[type="text"] {
            width: 100%;
            padding: 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 24px;
            text-align: center;
            letter-spacing: 8px;
            font-weight: 600;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: {{ $primaryColor }};
        }

        .error {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
        }

        .success {
            color: #10b981;
            font-size: 13px;
            margin-top: 6px;
        }

        .info {
            color: #3b82f6;
            font-size: 13px;
            margin-top: 6px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: {{ $primaryColor }};
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .resend-section {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .resend-text {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .resend-btn {
            background: none;
            border: none;
            color: {{ $primaryColor }};
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            font-size: 14px;
        }

        .resend-btn:hover {
            opacity: 0.8;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .verify-container {
                padding: 24px;
            }

            h1 {
                font-size: 20px;
            }

            input[type="text"] {
                font-size: 20px;
                letter-spacing: 4px;
            }

            .login-header {
                height: 50px;
                padding: 0 15px;
            }

            .header-school-name {
                font-size: 16px;
            }

            .verify-wrapper {
                padding: 70px 15px 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="login-header">
        <div class="header-content">
            @if($logoImage && $showSchoolName)
                <img src="{{ asset('storage/' . $logoImage) }}" alt="{{ $schoolName }}" class="header-logo">
            @endif
            @if($showSchoolName)
                <span class="header-school-name">{{ $schoolNameText }}</span>
            @endif
        </div>
    </div>

    <div class="verify-wrapper">
        <div class="verify-container">
        <div class="verify-header">
            <div class="verify-icon">✉️</div>
            <h1>Verify Your Email</h1>
            <p class="subtitle">We sent a 6-digit code to:</p>
        </div>

        <div class="email-display">{{ $email ?? 'your email' }}</div>

        @if(session('success'))
            <div class="success" style="text-align: center; margin-bottom: 20px;">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="info" style="text-align: center; margin-bottom: 20px;">
                ℹ️ {{ session('info') }}
            </div>
        @endif

        <form method="POST" action="{{ route('schools.verification.verify', $school) }}">
            @csrf
            
            <div class="form-group">
                <label for="code">Enter Verification Code</label>
                <input 
                    type="text" 
                    id="code" 
                    name="code" 
                    maxlength="6" 
                    pattern="[0-9]{6}"
                    placeholder="000000"
                    required 
                    autofocus
                    inputmode="numeric"
                >
                @error('code')
                    <div class="error">{{ $message }}</div>
                @enderror
                @error('error')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="submit-btn">Verify Email</button>
        </form>

        <div class="resend-section">
            <p class="resend-text">Didn't receive the code?</p>
            <form method="POST" action="{{ route('schools.verification.resend', $school) }}" style="display: inline;">
                @csrf
                <button type="submit" class="resend-btn">Resend Code</button>
            </form>
        </div>

        <div class="back-link">
            <a href="{{ route('schools.login', $school) }}">← Back to Login</a>
        </div>
    </div>
    </div>

    <script>
        // Auto-format code input
        const codeInput = document.getElementById('code');
        codeInput.addEventListener('input', function(e) {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Auto-submit when 6 digits entered
        codeInput.addEventListener('input', function(e) {
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    </script>
</body>
</html>
