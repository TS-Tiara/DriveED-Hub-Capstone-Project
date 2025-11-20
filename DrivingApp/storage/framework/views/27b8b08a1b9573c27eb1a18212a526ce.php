<!DOCTYPE html>
<html>
<head>
    <?php
        $school = $school ?? $currentSchool ?? null;
        $slug = $school?->slug ?? 'default';
        $backgroundImage = asset('images/bg' . $slug . '.jpg');
        $schoolName = $school->name ?? 'DriveEd Hub';
        
        // Get custom colors from school settings or use defaults
        $primaryColor = $school?->settings?->primary_color ?? '#2563eb';
        $secondaryColor = $school?->settings?->secondary_color ?? '#f59e0b';
    ?>
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

        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 80px);
            padding: 20px;
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
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8px;
            position: relative;
        }

        .login-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 25px;
        }

        .login-title::after {
            content: "";
            position: absolute;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--secondary-gradient);
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
            margin-bottom: 18px;
            text-align: left;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
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
            margin-bottom: 20px;
            font-size: 13px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .remember-me input[type="checkbox"] {
            width: 15px;
            height: 15px;
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
            padding: 12px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
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
    <style>
        :root {
            --school-bg: url('<?php echo e($backgroundImage); ?>');
            --primary-gradient: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($primaryColor); ?>dd 100%);
            --secondary-gradient: linear-gradient(135deg, <?php echo e($secondaryColor); ?> 0%, <?php echo e($secondaryColor); ?>dd 100%);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-section">
            <?php echo e($schoolName); ?>

        </div>
        <div class="welcome-section">
            <h1>Welcome to <?php echo e($schoolName); ?>!</h1>
            <p>Select your role below</p>
        </div>
    </div>

    <div class="main-content">
        <div class="login-container">
            <h2 class="login-title">Login</h2>
            <p class="login-subtitle">Admin • Instructor • Student</p>

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
            
            <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <p style="color: #666; margin-bottom: 10px;">Don't have an account?</p>
                <a href="<?php echo e(route('schools.registration.form', $school)); ?>" style="color: #2563eb; text-decoration: none; font-weight: 600; font-size: 14px;">
                    Register for Student Account →
                </a>
            </div>
        </div>
    </div>
</body>
</html><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/school/login.blade.php ENDPATH**/ ?>