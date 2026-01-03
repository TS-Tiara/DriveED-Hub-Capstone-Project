

<?php $__env->startSection('title', 'Review Theoretical Completion'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $schoolRoute = function($routeName, $params = []) use ($school) {
        return route('schools.' . $routeName, array_merge(['school' => $school->slug], $params));
    };
    
    $totalHours = $enrollment->sessionCompletions->sum('hours_completed');
    $requiredHours = $enrollment->course->theoretical_hours ?? 15;
    $progress = $requiredHours > 0 ? min(100, round(($totalHours / $requiredHours) * 100)) : 0;
    $primaryColor = $school->schoolSetting->primary_color ?? '#667eea';
?>

<?php echo $__env->make('school.admin.partials.admin-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<style>
    .theoretical-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1400px;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid <?php echo e($primaryColor); ?>;
    }
    
    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    
    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }
    
    .info-card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
    }
    
    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f3f4f6;
    }
    
    .info-row {
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid #f9fafb;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 500;
        color: #6b7280;
        min-width: 130px;
    }
    
    .info-value {
        color: #1f2937;
    }
</style>

<div class="theoretical-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Review Theoretical Completion</h1>
            <p class="page-subtitle"><?php echo e($enrollment->student->first_name); ?> <?php echo e($enrollment->student->last_name); ?> - <?php echo e($enrollment->course->course_name); ?></p>
        </div>
        <a href="<?php echo e($schoolRoute('admin.theoretical.index')); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Left Column -->
        <div class="col-md-4">
            <!-- Student Info -->
            <div class="info-card">
                <h5 class="card-title"><i class="fas fa-user me-2"></i>Student Information</h5>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?php echo e($enrollment->student->first_name); ?> <?php echo e($enrollment->student->last_name); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo e($enrollment->student->email); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Student Type:</span>
                    <span class="info-value">
                        <span class="badge bg-info"><?php echo e(ucfirst(str_replace('_', ' ', $enrollment->student->student_type))); ?></span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">License Type:</span>
                    <span class="info-value">
                        <span class="badge bg-secondary"><?php echo e(ucfirst(str_replace('_', ' ', $enrollment->student->license_type))); ?></span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Enrolled:</span>
                    <span class="info-value"><?php echo e($enrollment->enrolled_at->format('M d, Y')); ?></span>
                </div>
            </div>

            <!-- Course Info -->
            <div class="info-card">
                <h5 class="card-title"><i class="fas fa-book me-2"></i>Course Details</h5>
                <div class="info-row">
                    <span class="info-label">Course:</span>
                    <span class="info-value fw-bold"><?php echo e($enrollment->course->course_name); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value">
                        <span class="badge bg-primary"><?php echo e(ucfirst($enrollment->course->course_type)); ?></span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">License:</span>
                    <span class="info-value">
                        <span class="badge bg-secondary"><?php echo e(ucfirst(str_replace('_', ' ', $enrollment->course->license_type))); ?></span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Required Hours:</span>
                    <span class="info-value fw-bold"><?php echo e($requiredHours); ?> hours</span>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-8">
            <!-- Progress -->
            <div class="info-card">
                <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Training Progress</h5>
                <div class="row text-center mb-3">
                    <div class="col-md-4">
                        <h3 class="mb-0"><?php echo e($totalHours); ?></h3>
                        <small class="text-muted">Hours Completed</small>
                    </div>
                    <div class="col-md-4">
                        <h3 class="mb-0"><?php echo e($requiredHours); ?></h3>
                        <small class="text-muted">Required Hours</small>
                    </div>
                    <div class="col-md-4">
                        <h3 class="mb-0 text-<?php echo e($progress >= 100 ? 'success' : 'warning'); ?>"><?php echo e($progress); ?>%</h3>
                        <small class="text-muted">Progress</small>
                    </div>
                </div>
                <div class="progress" style="height: 30px;">
                    <div class="progress-bar bg-<?php echo e($progress >= 100 ? 'success' : 'primary'); ?>" 
                         style="width: <?php echo e($progress); ?>%">
                        <?php echo e($progress); ?>%
                    </div>
                </div>
            </div>

            <!-- Session History -->
            <div class="info-card">
                <h5 class="card-title"><i class="fas fa-history me-2"></i>Session History</h5>
                <?php if($enrollment->sessionCompletions->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Instructor</th>
                                    <th>Hours</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $enrollment->sessionCompletions->sortByDesc('session_date'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($session->session_date->format('M d, Y')); ?></td>
                                        <td><?php echo e($session->session_time); ?></td>
                                        <td><?php echo e($session->instructor->first_name); ?> <?php echo e($session->instructor->last_name); ?></td>
                                        <td><span class="badge bg-success"><?php echo e($session->hours_completed); ?>h</span></td>
                                        <td><span class="badge bg-info"><?php echo e(ucfirst($session->session_type)); ?></span></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3 mb-0">No sessions recorded yet</p>
                <?php endif; ?>
            </div>

            <!-- Mark as Passed -->
            <div class="info-card">
                <h5 class="card-title">
                    <i class="fas fa-<?php echo e($validation['allowed'] ? 'check-circle' : 'exclamation-triangle'); ?> me-2"></i>
                    Mark as Passed
                </h5>
                <?php if($validation['allowed']): ?>
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle me-2"></i><?php echo e($validation['message']); ?>

                    </div>
                    <form action="<?php echo e($schoolRoute('admin.theoretical.markAsPassed')); ?>" 
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to mark this student as passed theoretical training?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="enrollment_id" value="<?php echo e($enrollment->id); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Add any additional notes..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check-circle me-2"></i>Mark as Passed Theoretical
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo e($validation['message']); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/school/admin/theoretical/show.blade.php ENDPATH**/ ?>