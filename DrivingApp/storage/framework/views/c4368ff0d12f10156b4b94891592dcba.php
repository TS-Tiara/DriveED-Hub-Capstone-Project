<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driving School Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }

        .header h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.95;
        }

        .section-title {
            color: white;
            font-size: 1.5rem;
            margin: 40px 0 20px 0;
            text-align: center;
        }

        .schools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .school-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: #333;
            display: block;
        }

        .school-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        .school-card h2 {
            font-size: 1.4rem;
            color: #667eea;
            margin-bottom: 10px;
            text-align: center;
        }

        .school-card .slug {
            font-size: 0.85rem;
            color: #9ca3af;
            text-align: center;
            margin-bottom: 20px;
        }

        .school-card .btn {
            display: block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s;
        }

        .school-card:hover .btn {
            transform: scale(1.05);
        }

        .admin-section {
            max-width: 500px;
            margin: 0 auto;
        }

        .admin-card {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
        }

        .admin-card h2 {
            color: white;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .admin-card p {
            color: #d1d5db;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .admin-card a {
            display: inline-block;
            background: white;
            color: #1f2937;
            padding: 12px 35px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .admin-card a:hover {
            background: #f9fafb;
            transform: scale(1.05);
        }

        .divider {
            height: 2px;
            background: rgba(255,255,255,0.2);
            margin: 50px 0;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .header p {
                font-size: 1rem;
            }
            
            .schools-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Driving School Management System</h1>
            <p>Welcome! Please select a driving school to continue</p>
        </div>

        <h3 class="section-title">Available Driving Schools</h3>
        
        <div class="schools-grid">
            <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('schools.login', ['school' => $school->slug])); ?>" class="school-card">
                <h2><?php echo e($school->name); ?></h2>
                <span class="btn">Enter School Portal</span>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="divider"></div>

        <div class="admin-section">
            <div class="admin-card">
                <h2>System Administrator</h2>
                <p>Global management portal to monitor all schools and manage the entire platform</p>
                <a href="<?php echo e(route('system-admin.login')); ?>">System Admin Login</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/welcome.blade.php ENDPATH**/ ?>