

<?php $__env->startSection('title', 'Browse Courses'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    
    $primaryColor = $settings?->primary_color ?? '#0d6efd';
    $secondaryColor = $settings?->secondary_color ?? '#6c757d';
    $accentColor = $settings?->accent_color ?? '#8b5cf6';
    
    // Get enrolled course IDs for this guest
    $enrolledCourseIds = [];
    $enrollmentStatuses = [];
    if (auth()->guard('student')->check()) {
        $enrollments = auth()->guard('student')->user()
            ->enrollmentRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->get();
        $enrolledCourseIds = $enrollments->pluck('course_id')->toArray();
        $enrollmentStatuses = $enrollments->pluck('status', 'course_id')->toArray();
    }
?>

<style>
    .courses-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .courses-header {
        margin-bottom: 20px;
        border-bottom: 4px solid <?php echo $primaryColor; ?>;
        padding-bottom: 15px;
    }
    
    .courses-header h1 {
        font-size: 2rem;
        font-weight: 400;
        margin: 0;
        color: #1a202c;
    }
    
    .courses-header p {
        color: #6b7280;
        margin: 8px 0 0 0;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffc107;
    }
    
    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid #17a2b8;
    }
    
    .alert-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: inherit;
        opacity: 0.6;
    }
    
    .alert-close:hover {
        opacity: 1;
    }
    
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }
    
    .course-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.2s;
    }
    
    .course-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    
    .course-banner {
        height: 160px;
        background: linear-gradient(135deg, <?php echo $primaryColor; ?> 0%, <?php echo $secondaryColor; ?> 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    
    .course-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .course-banner-icon {
        color: rgba(255,255,255,0.9);
    }
    
    .featured-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .course-body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .course-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .badge-type {
        padding: 4px 10px;
        background: #e0e7ff;
        color: #3730a3;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-vehicle {
        padding: 4px 10px;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .course-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 10px 0;
    }
    
    .course-description {
        color: #6b7280;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 15px;
    }
    
    .course-features {
        list-style: none;
        padding: 0;
        margin: 0 0 15px 0;
    }
    
    .course-features li {
        padding: 5px 0 5px 22px;
        position: relative;
        color: #374151;
        font-size: 0.85rem;
    }
    
    .course-features li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 14px;
        height: 14px;
        background: <?php echo $primaryColor; ?>;
        border-radius: 50%;
    }
    
    .course-info {
        background: #f8fafc;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        color: #64748b;
        font-size: 0.85rem;
    }
    
    .info-value {
        font-weight: 600;
        color: #1e293b;
    }
    
    .packages-section {
        background: #fafafa;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #e5e7eb;
    }
    
    .packages-title {
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    
    .package-item {
        background: white;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
        border: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .package-item:last-child {
        margin-bottom: 0;
    }
    
    .package-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    
    .package-tag {
        font-size: 0.65rem;
        padding: 2px 6px;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .tag-manual {
        background: #fef3c7;
        color: #92400e;
    }
    
    .tag-automatic {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .tag-popular {
        background: #fef3c7;
        color: #b45309;
    }
    
    .package-details {
        font-size: 0.8rem;
        color: #6b7280;
    }
    
    .package-price {
        font-weight: 700;
        color: <?php echo $primaryColor; ?>;
        font-size: 1rem;
    }
    
    .more-packages {
        text-align: center;
        color: <?php echo $primaryColor; ?>;
        font-weight: 600;
        font-size: 0.8rem;
        padding-top: 6px;
    }
    
    .btn-enroll {
        width: 100%;
        padding: 12px;
        background: <?php echo $primaryColor; ?>;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: opacity 0.2s;
        margin-top: auto;
    }
    
    .btn-enroll:hover {
        opacity: 0.9;
    }
    
    .btn-enroll:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }
    
    .btn-enrolled {
        background: #10b981;
    }
    
    .btn-pending {
        background: #f59e0b;
    }
    
    .enrollment-status {
        text-align: center;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    
    .empty-state svg {
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .empty-state p {
        margin: 5px 0;
    }
    
    @media (max-width: 768px) {
        .courses-container {
            padding: 15px;
        }
        
        .courses-header h1 {
            font-size: 1.5rem;
        }
        
        .courses-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .course-banner {
            height: 140px;
        }
        
        .course-body {
            padding: 15px;
        }
        
        .package-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .package-price {
            align-self: flex-end;
        }
    }
</style>

<div class="courses-container">
    <div class="courses-header">
        <h1>Available Courses</h1>
        <p>Browse our courses and submit an enrollment request to get started</p>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <span>✓ <?php echo e(session('success')); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        <div class="alert alert-error">
            <span>✕ <?php echo e(session('error')); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if(session('warning')): ?>
        <div class="alert alert-warning">
            <span>⚠ <?php echo e(session('warning')); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if(session('info')): ?>
        <div class="alert alert-info">
            <span>ℹ <?php echo e(session('info')); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>

    <div class="courses-grid">
        <?php $activeCourses = $courses->where('status', 'active'); ?>
        
        <?php $__empty_1 = true; $__currentLoopData = $activeCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="course-card">
            <div class="course-banner">
                <?php if($course->banner_image && file_exists(public_path($course->banner_image))): ?>
                    <img src="<?php echo e(asset($course->banner_image)); ?>" alt="<?php echo e($course->title); ?>">
                <?php else: ?>
                    <svg class="course-banner-icon" xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679c.033.161.049.325.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.807.807 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2ZM6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2H6ZM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17 1.247 0 3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z"/>
                    </svg>
                <?php endif; ?>
                
                <?php if($course->is_featured): ?>
                    <span class="featured-badge">⭐ Featured</span>
                <?php endif; ?>
            </div>
            
            <div class="course-body">
                <div class="course-badges">
                    <span class="badge-type"><?php echo e(ucfirst($course->type ?? 'Standard')); ?></span>
                    <?php if($course->vehicle_type): ?>
                        <span class="badge-vehicle"><?php echo e($course->vehicle_type); ?></span>
                    <?php endif; ?>
                </div>
                
                <h3 class="course-title"><?php echo e($course->title); ?></h3>
                
                <?php if($course->description): ?>
                    <p class="course-description"><?php echo e(Str::limit($course->description, 120)); ?></p>
                <?php endif; ?>
                
                <?php $features = $course->features; ?>
                <?php if($features && is_array($features) && count($features) > 0): ?>
                    <ul class="course-features">
                        <?php $__currentLoopData = array_slice($features, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($feature); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if(count($features) > 3): ?>
                            <li style="color: <?php echo $primaryColor; ?>; font-weight: 600;">+<?php echo e(count($features) - 3); ?> more</li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
                
                <?php if($course->packages && $course->packages->count() > 0): ?>
                    <div class="packages-section">
                        <div class="packages-title">Available Packages</div>
                        <?php $__currentLoopData = $course->packages->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="package-item">
                                <div>
                                    <div class="package-name">
                                        <?php echo e($package->name); ?>

                                        <?php if($package->transmission_type): ?>
                                            <span class="package-tag tag-<?php echo e($package->transmission_type); ?>"><?php echo e(strtoupper($package->transmission_type)); ?></span>
                                        <?php endif; ?>
                                        <?php if($package->is_popular): ?>
                                            <span class="package-tag tag-popular">POPULAR</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="package-details">
                                        <?php if($package->training_hours): ?><?php echo e($package->training_hours); ?> hours <?php endif; ?>
                                    </div>
                                </div>
                                <span class="package-price">₱<?php echo e(number_format($package->price, 2)); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($course->packages->count() > 2): ?>
                            <div class="more-packages">+<?php echo e($course->packages->count() - 2); ?> more packages</div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="course-info">
                        <?php if($course->duration_hours): ?>
                        <div class="info-row">
                            <span class="info-label">Duration</span>
                            <span class="info-value"><?php echo e($course->duration_hours); ?> hours</span>
                        </div>
                        <?php endif; ?>
                        <?php if($course->price > 0): ?>
                        <div class="info-row">
                            <span class="info-label">Price</span>
                            <span class="info-value">₱<?php echo e(number_format($course->price, 2)); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if(in_array($course->id, $enrolledCourseIds)): ?>
                    <?php $status = $enrollmentStatuses[$course->id] ?? 'pending'; ?>
                    <div class="enrollment-status status-<?php echo e($status); ?>">
                        <?php if($status === 'approved'): ?>
                            ✓ Enrollment Approved
                        <?php else: ?>
                            ⏳ Enrollment Request Pending
                        <?php endif; ?>
                    </div>
                    <button class="btn-enroll btn-<?php echo e($status === 'approved' ? 'enrolled' : 'pending'); ?>" disabled>
                        <?php echo e($status === 'approved' ? 'Already Enrolled' : 'Request Pending'); ?>

                    </button>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('schools.guest.enroll', ['school' => $school, 'course' => $course->id])); ?>" style="margin-top: auto;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-enroll">Request Enrollment</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917l-7.5-3.5Z"/>
                <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466 4.176 9.032Z"/>
            </svg>
            <p style="font-size: 1.1rem;">No courses available at the moment</p>
            <p style="font-size: 0.9rem;">Please check back later for new courses.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/school/guest/courses.blade.php ENDPATH**/ ?>