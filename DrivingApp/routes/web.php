<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminTimeSlotController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\InstructorTimeSlotController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\EnrollmentRequestController;
use App\Http\Controllers\SystemAdminController;
use App\Models\School;

Route::get('/', function () {
    // Eager load schoolSetting to prevent N+1 queries
    $schools = \App\Models\School::with('schoolSetting')->orderBy('name')->get();
    return view('welcome', compact('schools'));
})->name('welcome');

// System Admin Routes (Global - Not School Specific)
Route::prefix('system-admin')->name('system-admin.')->group(function () {
    // Login routes (no auth required)
    Route::get('/login', [SystemAdminController::class, 'showLogin'])->name('login');
    Route::post('/login', [SystemAdminController::class, 'login'])->name('login.submit');
    
    // Protected routes (system admin only)
    Route::middleware(['system.admin'])->group(function () {
        Route::get('/', [SystemAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard', [SystemAdminController::class, 'dashboard']);
        
        // Schools management
        Route::get('/schools', [SystemAdminController::class, 'schools'])->name('schools');
        Route::post('/schools', [SystemAdminController::class, 'storeSchool'])->name('schools.store');
        Route::patch('/schools/{school}/toggle-status', [SystemAdminController::class, 'toggleSchoolStatus'])->name('schools.toggle-status');
        Route::delete('/schools/{school}', [SystemAdminController::class, 'deleteSchool'])->name('schools.delete');
        
        // School Admins management
        Route::get('/admins', [SystemAdminController::class, 'admins'])->name('admins');
        Route::post('/admins', [SystemAdminController::class, 'storeAdmin'])->name('admins.store');
        Route::patch('/admins/{admin}/toggle-status', [SystemAdminController::class, 'toggleAdminStatus'])->name('admins.toggle-status');
        Route::delete('/admins/{admin}', [SystemAdminController::class, 'deleteAdmin'])->name('admins.delete');
        
        // Users management
        Route::get('/users', [SystemAdminController::class, 'users'])->name('users');
        Route::patch('/users/{type}/{id}/toggle-status', [SystemAdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
        Route::delete('/users/{type}/{id}', [SystemAdminController::class, 'deleteUser'])->name('users.delete');
        
        // Logs
        Route::get('/logs', [SystemAdminController::class, 'logs'])->name('logs');
        Route::get('/logs/{log}', [SystemAdminController::class, 'showLog'])->name('logs.show');
        Route::post('/logs/{log}/resolve', [SystemAdminController::class, 'resolveLog'])->name('logs.resolve');
        Route::post('/logs/cleanup', [SystemAdminController::class, 'cleanupLogs'])->name('logs.cleanup');
        
        Route::post('/logout', [SystemAdminController::class, 'logout'])->name('logout');
    });
});

Route::prefix('{school:slug}')
    ->as('schools.')
    ->middleware(['school.context'])
    ->scopeBindings()
    ->group(function (): void {
        Route::controller(AuthController::class)->group(function (): void {
            Route::get('/', 'showLogin')->name('login');
            Route::get('/login', 'showLogin');
            Route::post('/login', 'login')->name('login.submit');
            Route::post('/logout', 'logout')->name('logout');
        });

        // Public guest registration (Main registration entry point)
        Route::get('/register', [GuestController::class, 'showRegistrationForm'])->name('registration.form');
        Route::post('/register', [GuestController::class, 'register'])->name('registration.submit');

        // Guest-authenticated routes (must have guest role)
        Route::prefix('guest')->name('guest.')->group(function (): void {
            Route::middleware(['auth:student', 'guest.role'])->group(function (): void {
                Route::get('/dashboard', [GuestController::class, 'dashboard'])->name('dashboard');
                Route::get('/courses', [GuestController::class, 'courses'])->name('courses');
                Route::post('/enroll/{course}', [GuestController::class, 'enroll'])->name('enroll');
                Route::get('/enrollment-requests', [GuestController::class, 'enrollmentRequests'])->name('enrollmentRequests');
            });
        });

        Route::prefix('admin')->name('admin.')->middleware(['auth:admin', 'ajax', 'redirect.system.admin'])->group(function (): void {
            Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

            Route::get('/user-management', [AdminController::class, 'userManagement'])->name('userManagement');

            Route::post('/store-account', [AdminController::class, 'storeAccount'])->name('storeAccount');

            Route::put('/students/{id}', [AdminController::class, 'updateStudent'])->name('students.update');
            Route::patch('/students/{id}/toggle-status', [AdminController::class, 'toggleStudentStatus'])->name('students.toggleStatus');

            Route::post('/instructors', [AdminController::class, 'storeAccount'])->name('instructors.store');
            Route::put('/instructors/{id}', [AdminController::class, 'updateInstructor'])->name('instructors.update');
            Route::patch('/instructors/{id}/toggle-status', [AdminController::class, 'toggleInstructorStatus'])->name('instructors.toggleStatus');
            Route::patch('/instructors/{id}/availability', [AdminController::class, 'toggleAvailability'])->name('instructors.availability');

            // Schedule management (unified system: admin creates, can pre-assign, instructors self-select remaining spots)
            Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules');
            Route::post('/schedules/create', [AdminController::class, 'createSchedule'])->name('schedules.create');
            Route::put('/schedules/{id}', [AdminController::class, 'updateSchedule'])->name('schedules.update');
            Route::delete('/schedules/{id}', [AdminController::class, 'deleteSchedule'])->name('schedules.delete');

            // Instructor removal requests
            Route::get('/removal-requests', [AdminController::class, 'removalRequests'])->name('removalRequests');
            Route::post('/removal-requests/{id}/approve', [AdminController::class, 'approveRemovalRequest'])->name('removalRequests.approve');
            Route::post('/removal-requests/{id}/reject', [AdminController::class, 'rejectRemovalRequest'])->name('removalRequests.reject');

            // Enrollment requests management (Guest → Student promotion)
            Route::prefix('enrollment-requests')->name('enrollmentRequests.')->group(function () {
                Route::get('/', [EnrollmentRequestController::class, 'index'])->name('index');
                Route::post('/{enrollmentRequest}/approve', [EnrollmentRequestController::class, 'approve'])->name('approve');
                Route::post('/{enrollmentRequest}/reject', [EnrollmentRequestController::class, 'reject'])->name('reject');
                Route::post('/{enrollmentRequest}/payment-status', [EnrollmentRequestController::class, 'updatePaymentStatus'])->name('paymentStatus');
            });

            // Course management with packages
            Route::get('/courses', [AdminController::class, 'courses'])->name('courses');
            Route::post('/courses', [AdminController::class, 'storeCourse'])->name('courses.store');
            Route::put('/courses/{id}', [AdminController::class, 'updateCourse'])->name('courses.update');
            Route::delete('/courses/{id}', [AdminController::class, 'deleteCourse'])->name('courses.delete');
            
            Route::post('/courses/{courseId}/packages', [AdminController::class, 'storePackage'])->name('courses.packages.store');
            Route::put('/courses/{courseId}/packages/{packageId}', [AdminController::class, 'updatePackage'])->name('courses.packages.update');
            Route::delete('/courses/{courseId}/packages/{packageId}', [AdminController::class, 'deletePackage'])->name('courses.packages.delete');

            // School settings
            Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
            Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

            Route::get('/reports/students', [AdminController::class, 'studentReports'])->name('reports.students');
            Route::get('/reports/instructors', [AdminController::class, 'instructorReports'])->name('reports.instructors');
            Route::get('/reports/logs', [AdminController::class, 'logs'])->name('reports.logs');
            
            // Reports - consolidated in single index view
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            
            // Report exports (CSV)
            Route::get('/reports/export/students', [ReportController::class, 'exportStudents'])->name('reports.export.students');
            Route::get('/reports/export/instructors', [ReportController::class, 'exportInstructors'])->name('reports.export.instructors');
            Route::get('/reports/export/bookings', [ReportController::class, 'exportBookings'])->name('reports.export.bookings');
            Route::get('/reports/export/payments', [ReportController::class, 'exportPayments'])->name('reports.export.payments');
            Route::get('/reports/export/courses', [ReportController::class, 'exportCourses'])->name('reports.export.courses');
            
            Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
            Route::put('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
            Route::post('/profile/picture', [AdminController::class, 'updateProfilePicture'])->name('profile.picture');

            // Bookings management (no separate create/edit views - handled via modals)
            Route::resource('bookings', BookingController::class)->except(['create', 'edit']);
            Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.updateStatus');

            // Payments management (no separate create/edit views - handled via modals)
            Route::resource('payments', PaymentController::class)->except(['create', 'edit']);
            Route::get('/payments/statistics', [PaymentController::class, 'statistics'])->name('payments.statistics');
            
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });

        Route::prefix('instructor')->name('instructor.')->middleware(['auth:instructor', 'ajax'])->group(function (): void {
            Route::get('/', [InstructorController::class, 'dashboard'])->name('dashboard');

            Route::get('/my-schedule', [InstructorTimeSlotController::class, 'mySchedule'])->name('schedule');
            Route::post('/timeslots/{id}/toggle', [InstructorTimeSlotController::class, 'toggle'])->name('timeslots.toggle');
            Route::post('/timeslots/{id}/request-removal', [InstructorTimeSlotController::class, 'requestRemoval'])->name('timeslots.requestRemoval');
            
            Route::get('/profile', [InstructorTimeSlotController::class, 'profile'])->name('profile');
            Route::put('/profile', [InstructorTimeSlotController::class, 'updateProfile'])->name('profile.update');
            Route::post('/profile/picture', [InstructorTimeSlotController::class, 'updateProfilePicture'])->name('profile.picture');

            // Instructor attendance and feedback (used from schedule page)
            Route::post('/bookings/{booking}/attendance', [InstructorTimeSlotController::class, 'updateAttendance'])->name('bookings.attendance');
            Route::post('/bookings/{booking}/feedback', [InstructorTimeSlotController::class, 'updateFeedback'])->name('bookings.feedback');
            
            // Instructor lesson details
            Route::get('/lessons/{booking}', [InstructorTimeSlotController::class, 'getLessonDetails'])->name('lessons.details');
            Route::post('/lessons/{booking}/update', [InstructorTimeSlotController::class, 'updateLessonDetails'])->name('lessons.update');

            // Instructor students
            Route::get('/students', [InstructorController::class, 'myStudents'])->name('students.index');
            Route::get('/students/{id}', [InstructorController::class, 'showStudent'])->name('students.show');

            // Instructor progress updates
            Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
            Route::get('/progress/create', [ProgressController::class, 'create'])->name('progress.create');
            Route::post('/progress', [ProgressController::class, 'store'])->name('progress.store');
            Route::get('/progress/{progress}', [ProgressController::class, 'show'])->name('progress.show');
            Route::get('/progress/{progress}/edit', [ProgressController::class, 'edit'])->name('progress.edit');
            Route::put('/progress/{progress}', [ProgressController::class, 'update'])->name('progress.update');
            Route::delete('/progress/{progress}', [ProgressController::class, 'destroy'])->name('progress.destroy');
            
            // Instructor performance reports
            Route::get('/reports', [InstructorController::class, 'reports'])->name('reports');
            
            // Instructor grade management
            Route::get('/grades', [InstructorController::class, 'grades'])->name('grades');
            
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });

        Route::prefix('student')->name('student.')->middleware(['auth:student', 'student.role', 'ajax'])->group(function (): void {
            Route::get('/', [StudentController::class, 'dashboard'])->name('dashboard');

            Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
            Route::put('/profile', [StudentController::class, 'updateProfile'])->name('profile.update');
            Route::post('/profile/picture', [StudentController::class, 'updateProfilePicture'])->name('profile.picture');

            // Student courses
            Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
            Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

            // Booking queue management (used in schedule page)
            Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
            Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirmBooking'])->name('bookings.confirm');
            Route::delete('/bookings/{booking}/queue', [BookingController::class, 'removeFromQueue'])->name('bookings.removeQueue');

            // Student progress (single page view - no individual progress detail page)
            Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');

            // Student payments
            Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

            // Student schedule
            Route::get('/schedule', [StudentController::class, 'schedule'])->name('schedule');
            
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });