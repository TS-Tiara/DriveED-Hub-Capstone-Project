<!DOCTYPE html>
<html>
<head>
    <?php
        $school = $school ?? $currentSchool ?? null;
        $slug = $school?->slug ?? 'default';
        $backgroundImage = asset('images/bg' . $slug . '.jpg');
        $settings = $school?->schoolSetting;
        
        // School branding
        $schoolName = $school->name ?? 'DriveEd Hub';
        
        // Get custom colors from school settings or use defaults
        $primaryColor = $settings?->primary_color ?? '#2563eb';
        $secondaryColor = $settings?->secondary_color ?? '#f59e0b';
        
        // Login Header Customization Settings
        $headerLayout = $settings?->login_header_layout ?? 'horizontal';
        $logoImage = $settings?->login_logo_image;
        $logoPosition = $settings?->login_logo_position ?? 'left';
        $logoSize = $settings?->login_logo_size ?? 40;
        $schoolNameText = $settings?->login_school_name_text ?? $schoolName;
        $showSchoolName = $settings?->login_show_school_name ?? true;
        $schoolNamePosition = $settings?->login_school_name_position ?? 'left';
        $schoolNameSize = $settings?->login_school_name_size ?? 24;
        $welcomeText = $settings?->login_welcome_text ?? 'Welcome to ' . $schoolName . '!';
        $showWelcomeText = $settings?->login_show_welcome_text ?? false;
        $welcomePosition = $settings?->login_welcome_position ?? 'right';
        $welcomeSize = $settings?->login_welcome_size ?? 16;
        $headerBgType = $settings?->login_header_bg_type ?? 'gradient';
        $headerBgColor = $settings?->login_header_bg_color;
        $headerBgImage = $settings?->login_header_bg_image;
        $headerHeight = $settings?->login_header_height ?? 60;
        $headerTextColor = $settings?->login_header_text_color ?? '#ffffff';
        $headerShadow = $settings?->login_header_shadow ?? true;
        
        // Check if gradient is enabled
        $useGradient = $settings?->use_gradient_header ?? false;
        
        // Generate header background
        if ($headerBgType === 'solid' && $headerBgColor) {
            $headerBackground = $headerBgColor;
        } elseif ($headerBgType === 'image' && $headerBgImage) {
            $headerBackground = "url('" . asset('storage/' . $headerBgImage) . "')";
        } elseif ($useGradient) {
            $headerBackground = "linear-gradient(135deg, {$primaryColor} 0%, {$secondaryColor} 100%)";
        } else {
            $headerBackground = $primaryColor;
        }
        
        // Login Page Background Settings (same as main app)
        $pageBgType = $settings?->login_page_bg_type ?? 'color';
        $pageBgColor = $settings?->login_page_bg_color ?? '#f5f5f5';
        $pageBgImage = $settings?->login_page_bg_image;
        $pageBgOpacity = $settings?->login_page_bg_opacity ?? 100;
        
        // Generate page background
        if ($pageBgType === 'image' && $pageBgImage) {
            $pageBackground = "url('" . asset('storage/' . $pageBgImage) . "')";
        } else {
            $pageBackground = $pageBgColor;
        }
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo e($schoolName); ?> - Login</title>
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
            <?php if($pageBgType === 'image' && $pageBgImage): ?>
            background: <?php echo e($pageBackground); ?> no-repeat center center fixed;
            background-size: cover;
            <?php else: ?>
            background: <?php echo e($pageBackground); ?>;
            <?php endif; ?>
            opacity: <?php echo e($pageBgOpacity / 100); ?>;
            z-index: -1;
        }

        /* Customizable Login Header Styles */
        .login-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: <?php echo e($headerHeight); ?>px;
            background: <?php echo e($headerBgType === 'image' && $headerBgImage ? 'transparent' : $headerBackground); ?>;
            <?php if($headerBgType === 'image' && $headerBgImage): ?>
            background-image: <?php echo e($headerBackground); ?>;
            background-size: cover;
            background-position: center;
            <?php endif; ?>
            color: <?php echo e($headerTextColor); ?>;
            z-index: 1000;
            <?php if($headerShadow): ?>
            box-shadow: 0 3px 20px rgba(0,0,0,0.15);
            <?php endif; ?>
            box-sizing: border-box;
        }
        
        /* Horizontal Layout */
        .login-header-horizontal {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
        }
        
        .header-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-left {
            justify-content: flex-start;
            flex: 1;
        }
        
        .header-center {
            justify-content: center;
            flex: 1;
        }
        
        .header-right {
            justify-content: flex-end;
            flex: 1;
        }
        
        /* Vertical Layout */
        .login-header-vertical {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px 25px;
        }
        
        .header-vertical-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        /* Centered Layout */
        .login-header-centered {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px 25px;
        }
        
        .header-centered-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .header-main {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        /* Logo Only Layout */
        .login-header-logo-only {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 25px;
        }
        
        .header-logo-only {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Header Elements */
        .header-logo .logo-image {
            height: <?php echo e($logoSize); ?>px;
            width: auto;
            object-fit: contain;
        }
        
        .header-school-name {
            font-size: <?php echo e($schoolNameSize); ?>px;
            font-weight: 600;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
            white-space: nowrap;
        }
        
        .header-welcome {
            font-size: <?php echo e($welcomeSize); ?>px;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - <?php echo e($headerHeight); ?>px);
            margin-top: <?php echo e($headerHeight); ?>px;
            padding: 15px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.98);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 380px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .login-title {
            font-size: 22px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 18px;
            padding-bottom: 12px;
            position: relative;
        }

        .login-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .login-title::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--secondary-gradient);
            border-radius: 2px;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
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
            margin-bottom: 14px;
            text-align: left;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            background: rgba(255, 255, 255, 0.9);
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
            background: white;
        }

        input::placeholder {
            color: #9ca3af;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .remember-me input[type="checkbox"] {
            width: 14px;
            height: 14px;
            accent-color: #2563eb;
        }

        .forgot-password {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            font-size: 12px;
        }

        .forgot-password:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            padding: 10px;
            <?php if($school->schoolSetting->use_gradient_header ?? false): ?>
                background: linear-gradient(135deg, <?php echo e($school->schoolSetting->primary_color ?? '#3b82f6'); ?> 0%, <?php echo e($school->schoolSetting->secondary_color ?? '#2563eb'); ?> 100%);
            <?php else: ?>
                background: <?php echo e($school->schoolSetting->primary_color ?? '#3b82f6'); ?>;
            <?php endif; ?>
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.25);
            transition: opacity 0.2s ease;
        }

        .login-button:hover {
            opacity: 0.85;
        }

        .error-message {
            color: #dc2626;
            font-size: 11px;
            margin-top: 4px;
            text-align: left;
        }

        @media (max-width: 768px) {
            .login-header {
                height: <?php echo e(max(50, $headerHeight - 10)); ?>px;
                padding: 0 15px;
            }
            
            .header-school-name {
                font-size: <?php echo e(max(18, $schoolNameSize - 6)); ?>px;
            }
            
            .header-welcome {
                font-size: <?php echo e(max(14, $welcomeSize - 2)); ?>px;
            }
            
            .header-logo .logo-image {
                height: <?php echo e(max(32, $logoSize - 8)); ?>px;
            }

            .main-content {
                padding: 15px;
                height: calc(100vh - <?php echo e(max(50, $headerHeight - 10)); ?>px);
                margin-top: <?php echo e(max(50, $headerHeight - 10)); ?>px;
            }

            .login-container {
                width: 100%;
                max-width: 400px;
                padding: 25px 20px;
                border-radius: 15px;
            }

            .login-title {
                font-size: 24px;
            }

            .login-subtitle {
                font-size: 12px;
            }

            input[type="email"],
            input[type="password"] {
                padding: 14px;
                font-size: 16px;
            }

            .login-button {
                padding: 14px;
                font-size: 16px;
            }

            .form-options {
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .login-header {
                height: <?php echo e(max(45, $headerHeight - 15)); ?>px;
                padding: 0 12px;
            }
            
            .login-header-horizontal .header-right,
            .login-header-horizontal .header-center {
                display: none;
            }
            
            .header-school-name {
                font-size: <?php echo e(max(16, $schoolNameSize - 8)); ?>px;
            }
            
            .header-welcome {
                display: none;
            }
            
            .header-logo .logo-image {
                height: <?php echo e(max(28, $logoSize - 12)); ?>px;
            }

            .main-content {
                padding: 10px;
                height: calc(100vh - <?php echo e(max(45, $headerHeight - 15)); ?>px);
                margin-top: <?php echo e(max(45, $headerHeight - 15)); ?>px;
            }

            .login-container {
                padding: 14px 18px;
                width: 230px;
                max-width: 90%;
                border-radius: 8px;
            }

            .login-title {
                font-size: 18px;
            }

            .login-subtitle {
                font-size: 10px;
                margin-bottom: 10px;
            }

            .alert {
                font-size: 11px;
                padding: 6px 8px;
                border-radius: 5px;
                margin-bottom: 8px;
            }

            .form-group {
                margin-bottom: 8px;
            }

            input[type="email"],
            input[type="password"] {
                padding: 7px 9px;
                font-size: 12px;
                border: 1px solid #d1d5db;
                border-radius: 5px;
            }

            input::placeholder {
                font-size: 11px;
            }

            .form-options {
                font-size: 10px;
                margin-bottom: 10px;
            }

            .remember-me {
                font-size: 10px;
                gap: 3px;
            }

            .remember-me input[type="checkbox"] {
                width: 12px;
                height: 12px;
            }

            .forgot-password {
                font-size: 10px;
            }

            .login-button {
                padding: 8px;
                font-size: 12px;
                border-radius: 5px;
                box-shadow: 0 1px 4px rgba(59, 130, 246, 0.2);
            }

            .error-message {
                font-size: 10px;
                margin-top: 2px;
            }

            /* Register link section for mobile */
            .login-container > div[style*="text-align: center"] {
                margin-top: 14px !important;
                padding-top: 14px !important;
            }

            .login-container > div[style*="text-align: center"] p {
                font-size: 10px !important;
                margin-bottom: 8px !important;
            }

            .login-container > div[style*="text-align: center"] a {
                font-size: 11px !important;
            }
        }

        @media (max-width: 360px) {
            .login-header {
                height: <?php echo e(max(42, $headerHeight - 18)); ?>px;
                padding: 0 10px;
            }
            
            .header-school-name {
                font-size: <?php echo e(max(14, $schoolNameSize - 10)); ?>px;
            }
            
            .header-logo .logo-image {
                height: <?php echo e(max(24, $logoSize - 16)); ?>px;
            }

            .login-container {
                padding: 10px 14px;
            }

            .login-title {
                font-size: 20px;
            }
        }
    </style>
    <style>
        :root {
            --school-bg: url('<?php echo e($backgroundImage); ?>');
            --primary-gradient: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($primaryColor); ?>dd 100%);
            --secondary-gradient: linear-gradient(135deg, <?php echo e($secondaryColor); ?> 0%, <?php echo e($secondaryColor); ?>dd 100%);
        }
    </style>
</head>
<body>
    <!-- Customizable Login Header -->
    <nav class="login-header login-header-<?php echo e($headerLayout); ?>">
        <?php if($headerLayout === 'horizontal'): ?>
            <div class="header-section header-left">
                <?php if($logoImage && $logoPosition === 'left'): ?>
                    <div class="header-logo">
                        <img src="<?php echo e(asset('storage/' . $logoImage)); ?>" alt="Logo" class="logo-image">
                    </div>
                <?php endif; ?>
                <?php if($showSchoolName && $schoolNamePosition === 'left'): ?>
                    <div class="header-school-name">
                        <?php echo e($schoolNameText); ?>

                    </div>
                <?php endif; ?>
            </div>
            
            <div class="header-section header-center">
                <?php if($logoImage && $logoPosition === 'center'): ?>
                    <div class="header-logo">
                        <img src="<?php echo e(asset('storage/' . $logoImage)); ?>" alt="Logo" class="logo-image">
                    </div>
                <?php endif; ?>
                <?php if($showSchoolName && $schoolNamePosition === 'center'): ?>
                    <div class="header-school-name">
                        <?php echo e($schoolNameText); ?>

                    </div>
                <?php endif; ?>
            </div>
            
            <div class="header-section header-right">
                <?php if($logoImage && $logoPosition === 'right'): ?>
                    <div class="header-logo">
                        <img src="<?php echo e(asset('storage/' . $logoImage)); ?>" alt="Logo" class="logo-image">
                    </div>
                <?php endif; ?>
                <?php if($showSchoolName && $schoolNamePosition === 'right'): ?>
                    <div class="header-school-name">
                        <?php echo e($schoolNameText); ?>

                    </div>
                <?php endif; ?>
                <?php if($showWelcomeText && $welcomePosition === 'right'): ?>
                    <div class="header-welcome">
                        <?php echo e($welcomeText); ?>

                    </div>
                <?php endif; ?>
            </div>
        <?php elseif($headerLayout === 'vertical'): ?>
            <div class="header-vertical-content">
                <?php if($logoImage): ?>
                    <div class="header-logo">
                        <img src="<?php echo e(asset('storage/' . $logoImage)); ?>" alt="Logo" class="logo-image">
                    </div>
                <?php endif; ?>
                <?php if($showSchoolName): ?>
                    <div class="header-school-name">
                        <?php echo e($schoolNameText); ?>

                    </div>
                <?php endif; ?>
                <?php if($showWelcomeText): ?>
                    <div class="header-welcome">
                        <?php echo e($welcomeText); ?>

                    </div>
                <?php endif; ?>
            </div>
        <?php elseif($headerLayout === 'centered'): ?>
            <div class="header-centered-content">
                <div class="header-main">
                    <?php if($logoImage): ?>
                        <div class="header-logo">
                            <img src="<?php echo e(asset('storage/' . $logoImage)); ?>" alt="Logo" class="logo-image">
                        </div>
                    <?php endif; ?>
                    <?php if($showSchoolName): ?>
                        <div class="header-school-name">
                            <?php echo e($schoolNameText); ?>

                        </div>
                    <?php endif; ?>
                </div>
                <?php if($showWelcomeText): ?>
                    <div class="header-welcome">
                        <?php echo e($welcomeText); ?>

                    </div>
                <?php endif; ?>
            </div>
        <?php elseif($headerLayout === 'logo-only'): ?>
            <div class="header-logo-only">
                <?php if($logoImage): ?>
                    <img src="<?php echo e(asset('storage/' . $logoImage)); ?>" alt="Logo" class="logo-image">
                <?php else: ?>
                    <div class="header-school-name"><?php echo e($schoolNameText); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </nav>

    <div class="main-content">
        <div class="login-container">
            <h2 class="login-title">Login</h2>

            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-error">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('schools.login.submit', $school)); ?>">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <input 
                        type="email" 
                        name="email" 
                        placeholder="Email Address" 
                        value="<?php echo e(old('email')); ?>"
                        required>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="error-message"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <input 
                        type="password" 
                        name="password" 
                        placeholder="Password" 
                        required>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="error-message"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
            
            <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                <p style="color: #666; margin-bottom: 6px; font-size: 12px;">Don't have an account?</p>
                <a href="<?php echo e(route('schools.registration.form', $school)); ?>" style="color: #2563eb; text-decoration: none; font-weight: 600; font-size: 12px;">
                    Register for Student Account →
                </a>
            </div>
        </div>
    </div>
</body>
</html><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\login.blade.php ENDPATH**/ ?>