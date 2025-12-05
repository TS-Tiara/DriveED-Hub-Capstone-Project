

<?php $__env->startSection('title', 'Available Courses'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
?>

<style>
    .container {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
    
    h1 {
        color: #333;
        text-align: center;
        margin-bottom: 10px;
        font-size: 2.5rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .subtitle {
        text-align: center;
        color: #666;
        margin-bottom: 30px;
        font-size: 1.1rem;
    }
    
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }
    
    .course-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    
    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .course-header {
        <?php if($school->schoolSetting->use_gradient_header): ?>
            background: linear-gradient(135deg, <?php echo e($school->schoolSetting->primary_color); ?> 0%, <?php echo e($school->schoolSetting->secondary_color); ?> 100%);
        <?php else: ?>
            background: <?php echo e($school->schoolSetting->primary_color); ?>;
        <?php endif; ?>
        color: white;
        padding: 25px;
        text-align: center;
    }
    
    .course-icon {
        font-size: 3rem;
        margin-bottom: 10px;
    }
    
    .course-name {
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
    }
    
    .course-body {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .course-description {
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 20px;
        flex-grow: 1;
    }
    
    .course-details {
        margin-bottom: 20px;
    }
    
    .course-detail-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .course-detail-item:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        font-weight: 500;
        color: #6b7280;
    }
    
    .detail-value {
        font-weight: 600;
        color: #111827;
    }
    
    .price-tag {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 15px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .price-label {
        font-size: 0.85rem;
        opacity: 0.9;
        margin-bottom: 5px;
    }
    
    .price-amount {
        font-size: 2rem;
        font-weight: 700;
    }
    
    .enroll-btn {
        display: block;
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease;
        text-decoration: none;
        text-align: center;
    }
    
    .enroll-btn:hover {
        transform: translateY(-2px);
        color: white;
    }
    
    .enroll-btn:disabled,
    .enroll-btn.disabled {
        background: #9ca3af;
        cursor: not-allowed;
        transform: none;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 25px;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }
    
    .no-courses {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    
    .no-courses-icon {
        font-size: 5rem;
        margin-bottom: 20px;
    }
    
    .no-courses-text {
        font-size: 1.3rem;
        margin-bottom: 10px;
    }
    
    .enrollment-status {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 10px;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #dcfce7;
        color: #166534;
    }
</style>

<div class="container">
    <h1>Available Courses</h1>
    <p class="subtitle">Choose a course and submit an enrollment request</p>
    
    <?php if(session('success')): ?>
        <div class="alert alert-success">
            ✓ <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        <div class="alert alert-error">
            ✗ <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>
    
    <?php if(session('info')): ?>
        <div class="alert alert-info">
            ℹ <?php echo e(session('info')); ?>

        </div>
    <?php endif; ?>
    
    <?php
        $courses = \App\Models\Course::where('school_id', $school->id)
            ->where('status', 'active')
            ->get();
            
        $enrolledCourseIds = auth()->guard('student')->user()
            ->enrollmentRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('course_id')
            ->toArray();
    ?>
    
    <?php if($courses->count() > 0): ?>
        <div class="courses-grid">
            <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="course-card">
                    <div class="course-header">
                        <h2 class="course-name"><?php echo e($course->title); ?></h2>
                    </div>
                    
                    <div class="course-body">
                        <div class="course-description">
                            <?php echo e($course->description ?? 'Comprehensive driving course designed to help you become a confident and safe driver.'); ?>

                        </div>
                        
                        <div class="course-details">
                            <div class="course-detail-item">
                                <span class="detail-label">Duration</span>
                                <span class="detail-value"><?php echo e($course->duration_hours); ?> hours</span>
                            </div>
                            <div class="course-detail-item">
                                <span class="detail-label">Type</span>
                                <span class="detail-value"><?php echo e(ucfirst($course->type)); ?></span>
                            </div>
                        </div>
                        
                        <div class="price-tag">
                            <div class="price-label">Course Fee</div>
                            <div class="price-amount">₱<?php echo e(number_format($course->price, 2)); ?></div>
                        </div>
                        
                        <?php if(in_array($course->id, $enrolledCourseIds)): ?>
                            <?php
                                $enrollmentRequest = auth()->guard('student')->user()
                                    ->enrollmentRequests()
                                    ->where('course_id', $course->id)
                                    ->whereIn('status', ['pending', 'approved'])
                                    ->first();
                            ?>
                            <button class="enroll-btn disabled" disabled>
                                Already Enrolled
                            </button>
                            <span class="enrollment-status status-<?php echo e($enrollmentRequest->status); ?>">
                                Status: <?php echo e(ucfirst($enrollmentRequest->status)); ?>

                            </span>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('schools.guest.enroll', ['school' => $school, 'course' => $course->id])); ?>" style="margin: 0;">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="enroll-btn">
                                    Enroll in This Course
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="no-courses">
            <div class="no-courses-text">No courses available at the moment</div>
            <p>Please check back later for new courses.</p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\guest\courses.blade.php ENDPATH**/ ?>