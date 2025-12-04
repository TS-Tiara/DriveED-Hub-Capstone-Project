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
        
        // Login Header Customization Settings (shared with registration)
        $headerLayout = $settings?->login_header_layout ?? 'horizontal';
        $logoImage = $settings?->login_logo_image;
        $logoPosition = $settings?->login_logo_position ?? 'left';
        $logoSize = $settings?->login_logo_size ?? 40;
        $schoolNameText = $settings?->login_school_name_text ?? $schoolName;
        $showSchoolName = $settings?->login_show_school_name ?? true;
        $schoolNamePosition = $settings?->login_school_name_position ?? 'left';
        $schoolNameSize = $settings?->login_school_name_size ?? 24;
        // Use registration-specific text or fall back to default
        $welcomeText = $settings?->register_welcome_text ?? 'Student Registration';
        $subtitleText = $settings?->register_subtitle_text;
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
    <title><?php echo e($schoolName); ?> - Guest Registration</title>
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
            <?php if($pageBgType === 'image' && $pageBgImage): ?>
            background: <?php echo e($pageBackground); ?> no-repeat center center fixed;
            background-size: cover;
            <?php else: ?>
            background: <?php echo e($pageBackground); ?>;
            <?php endif; ?>
            opacity: <?php echo e($pageBgOpacity / 100); ?>;
            z-index: -1;
        }

        /* Customizable Registration Header Styles */
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
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .header-subtitle {
            font-size: <?php echo e(max(12, $welcomeSize - 4)); ?>px;
            font-weight: 400;
            opacity: 0.9;
            margin-top: 2px;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - <?php echo e($headerHeight); ?>px);
            margin-top: <?php echo e($headerHeight); ?>px;
            padding: 15px;
        }

        .registration-card {
            background: rgba(255, 255, 255, 0.98);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 500px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        h2 {
            font-size: 22px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8px;
            text-align: center;
        }

        .subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 12px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #374151;
            font-weight: 500;
            font-size: 13px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 9px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
        }

        textarea {
            min-height: 60px;
            resize: vertical;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }

        .error {
            color: #e74c3c;
            font-size: 11px;
            margin-top: 4px;
        }

        .success {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            text-align: left;
        }

        .alert-error {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            text-align: left;
        }

        .submit-btn {
            width: 100%;
            padding: 10px;
            <?php if($school->schoolSetting->use_gradient_header ?? false): ?>
                background: linear-gradient(135deg, <?php echo e($school->schoolSetting->primary_color ?? '#2563eb'); ?> 0%, <?php echo e($school->schoolSetting->secondary_color ?? '#1e40af'); ?> 100%);
            <?php else: ?>
                background: <?php echo e($school->schoolSetting->primary_color ?? '#2563eb'); ?>;
            <?php endif; ?>
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }

        .submit-btn:hover {
            opacity: 0.85;
        }

        .back-to-login {
            text-align: center;
            margin-top: 15px;
        }

        .back-to-login a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            font-size: 12px;
        }

        .back-to-login a:hover {
            text-decoration: underline;
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

            .container {
                padding: 15px 10px;
                margin: <?php echo e(max(50, $headerHeight - 10) + 20); ?>px auto 15px auto;
            }

            .registration-card {
                padding: 25px 20px;
                border-radius: 12px;
            }

            h2 {
                font-size: 1.4rem;
            }

            .subtitle {
                font-size: 12px;
            }

            input[type="text"],
            input[type="email"],
            input[type="password"],
            textarea {
                padding: 12px;
                font-size: 16px;
            }

            .submit-btn {
                padding: 14px;
                font-size: 16px;
            }

            .form-group label {
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

            .container {
                padding: 10px;
                margin: <?php echo e(max(45, $headerHeight - 15) + 20); ?>px auto 10px auto;
            }

            .registration-card {
                padding: 14px 18px;
                width: 280px;
                max-width: 90%;
                border-radius: 8px;
            }

            h2 {
                font-size: 18px;
                margin-bottom: 6px;
            }

            .subtitle {
                font-size: 10px;
                margin-bottom: 8px;
            }

            .form-group {
                margin-bottom: 6px;
            }

            .form-group label {
                font-size: 10px;
                margin-bottom: 2px;
            }

            input[type="text"],
            input[type="email"],
            input[type="password"],
            textarea {
                padding: 6px 8px;
                font-size: 12px;
                border: 1px solid #d1d5db;
                border-radius: 5px;
            }

            textarea {
                min-height: 50px;
            }

            .error {
                font-size: 11px;
                margin-top: 2px;
            }

            .success {
                padding: 6px 8px;
                border-radius: 5px;
                margin-bottom: 6px;
                font-size: 11px;
            }

            .alert-error {
                padding: 6px 8px;
                border-radius: 5px;
                margin-bottom: 6px;
                font-size: 11px;
            }

            .submit-btn {
                padding: 8px;
                font-size: 12px;
                border-radius: 5px;
            }

            .back-to-login {
                margin-top: 10px;
            }

            .back-to-login a {
                font-size: 10px;
            }
        }
                padding: 13px;
                font-size: 16px;
            }

            .error {
                font-size: 12px;
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

            .registration-card {
                padding: 18px 12px;
            }

            h2 {
                font-size: 1.2rem;
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
<body style="--school-bg: url('<?php echo e($backgroundImage); ?>')">
    <!-- Customizable Registration Header -->
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

                        <?php if($subtitleText): ?>
                            <span class="header-subtitle"><?php echo e($subtitleText); ?></span>
                        <?php endif; ?>
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

                        <?php if($subtitleText): ?>
                            <span class="header-subtitle"><?php echo e($subtitleText); ?></span>
                        <?php endif; ?>
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

    <div class="container">
        <div class="registration-card">
            <h2>Create Your Account</h2>
            <p class="subtitle">Register to browse courses and start your driving journey</p>

            <?php if(session('success')): ?>
                <div class="success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="error" style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('schools.registration.submit', $school)); ?>">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo e(old('name')); ?>" required>
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>" required>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="contact">Contact Number *</label>
                    <input type="text" id="contact" name="contact" value="<?php echo e(old('contact')); ?>" required>
                    <?php $__errorArgs = ['contact'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="<?php echo e(old('address')); ?>">
                    <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <br>
                <button type="submit" class="submit-btn">Create Account</button>
            </form>

            <div class="back-to-login">
                <a href="<?php echo e(route('schools.login', $school)); ?>">← Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\register.blade.php ENDPATH**/ ?>