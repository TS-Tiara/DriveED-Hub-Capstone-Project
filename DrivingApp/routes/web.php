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
    $schools = \App\Models\School::orderBy('name')->get();
    return view('welcome', compact('schools'));
});

// System Admin Routes (Global - Not School Specific)
Route::prefix('system-admin')->name('system-admin.')->group(function () {
    // Login routes (no auth required)
    Route::get('/login', [SystemAdminController::class, 'showLogin'])->name('login');
    Route::post('/login', [SystemAdminController::class, 'login'])->name('login.submit');
    
    // Protected routes (system admin only)
    Route::middleware(['system.admin'])->group(function () {
        Route::get('/', [SystemAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard', [SystemAdminController::class, 'dashboard']);
        Route::get('/schools', [SystemAdminController::class, 'schools'])->name('schools');
        Route::get('/students', [SystemAdminController::class, 'students'])->name('students');
        Route::get('/instructors', [SystemAdminController::class, 'instructors'])->name('instructors');
        Route::get('/courses', [SystemAdminController::class, 'courses'])->name('courses');
        Route::get('/bookings', [SystemAdminController::class, 'bookings'])->name('bookings');
        Route::get('/payments', [SystemAdminController::class, 'payments'])->name('payments');
        Route::get('/logs', [SystemAdminController::class, 'logs'])->name('logs');
        Route::get('/logs/{log}', [SystemAdminController::class, 'showLog'])->name('logs.show');
        Route::post('/logs/{log}/resolve', [SystemAdminController::class, 'resolveLog'])->name('logs.resolve');
        Route::post('/logs/cleanup', [SystemAdminController::class, 'cleanupLogs'])->name('logs.cleanup');
        Route::get('/statistics', [SystemAdminController::class, 'getStatistics'])->name('statistics');
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

            Route::get('/students/{id}/edit', [AdminController::class, 'editStudent'])->name('students.edit');
            Route::put('/students/{id}', [AdminController::class, 'updateStudent'])->name('students.update');
            Route::patch('/students/{id}/toggle-status', [AdminController::class, 'toggleStudentStatus'])->name('students.toggleStatus');

            Route::post('/instructors', [AdminController::class, 'storeAccount'])->name('instructors.store');
            Route::get('/instructors/{id}/edit', [AdminController::class, 'editInstructor'])->name('instructors.edit');
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
            
            // Comprehensive Reporting Module
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [ReportController::class, 'index'])->name('index');
                Route::get('/enrollment', [ReportController::class, 'enrollmentReport'])->name('enrollment');
                Route::get('/driving-lessons', [ReportController::class, 'drivingLessonsReport'])->name('driving-lessons');
                Route::get('/practical-lessons', [ReportController::class, 'practicalLessonsReport'])->name('practical-lessons');
                Route::get('/financial', [ReportController::class, 'financialReport'])->name('financial');
                Route::get('/attendance', [ReportController::class, 'attendanceReport'])->name('attendance');
                Route::get('/instructor-performance', [ReportController::class, 'instructorPerformanceReport'])->name('instructor-performance');
                Route::get('/student-progress', [ReportController::class, 'studentProgressReport'])->name('student-progress');
                Route::get('/booking-summary', [ReportController::class, 'bookingSummaryReport'])->name('booking-summary');
                Route::get('/cancellation', [ReportController::class, 'cancellationReport'])->name('cancellation');
            });
            
            Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
            Route::put('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
            Route::post('/profile/picture', [AdminController::class, 'updateProfilePicture'])->name('profile.picture');

            // Bookings management
            Route::resource('bookings', BookingController::class);
            Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.updateStatus');

            // Payments management
            Route::resource('payments', PaymentController::class);
            Route::get('/payments/statistics', [PaymentController::class, 'statistics'])->name('payments.statistics');

            // Progress tracking
            Route::resource('progress', ProgressController::class);
            Route::get('/progress/student/{student}/summary', [ProgressController::class, 'studentSummary'])->name('progress.studentSummary');
            
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });

        Route::prefix('instructor')->name('instructor.')->middleware(['auth:instructor', 'ajax'])->group(function (): void {
            Route::get('/', [InstructorController::class, 'dashboard'])->name('dashboard');

            Route::get('/timeslots', [InstructorTimeSlotController::class, 'index'])->name('timeslots.index');
            Route::post('/timeslots/{id}/toggle', [InstructorTimeSlotController::class, 'toggle'])->name('timeslots.toggle');
            Route::post('/timeslots/{id}/request-removal', [InstructorTimeSlotController::class, 'requestRemoval'])->name('timeslots.requestRemoval');
            Route::get('/my-schedule', [InstructorTimeSlotController::class, 'mySchedule'])->name('schedule');
            Route::get('/profile', [InstructorTimeSlotController::class, 'profile'])->name('profile');
            Route::put('/profile', [InstructorTimeSlotController::class, 'updateProfile'])->name('profile.update');

            // Instructor bookings
            Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
            Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
            Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.updateStatus');

            // Instructor students
            Route::get('/students', [InstructorController::class, 'myStudents'])->name('students.index');
            Route::get('/students/{id}', [InstructorController::class, 'showStudent'])->name('students.show');

            // Instructor progress updates
            Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
            Route::get('/progress/create', [ProgressController::class, 'create'])->name('progress.create');
            Route::post('/progress', [ProgressController::class, 'store'])->name('progress.store');
            Route::get('/progress/{progress}/edit', [ProgressController::class, 'edit'])->name('progress.edit');
            Route::put('/progress/{progress}', [ProgressController::class, 'update'])->name('progress.update');
            
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });

        Route::prefix('student')->name('student.')->middleware(['auth:student', 'student.role', 'ajax'])->group(function (): void {
            Route::get('/', function (School $school) {
                return view($school->resolveView('student.dashboard'), ['school' => $school]);
            })->name('dashboard');

            Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
            Route::put('/profile', [StudentController::class, 'updateProfile'])->name('profile.update');

            // Student courses
            Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
            Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

            // Student bookings
            Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
            Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
            Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
            Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

            // Student progress
            Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
            Route::get('/progress/{progress}', [ProgressController::class, 'show'])->name('progress.show');

            // Student payments
            Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

            // Student schedule
            Route::get('/schedule', function (School $school) {
                $student = auth()->guard('student')->user();
                $bookings = \App\Models\Booking::where('student_id', $student->id)
                    ->with(['course', 'schedule.instructor'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                if (request()->expectsJson()) {
                    return view($school->resolveView('student.schedule', 'ajax'), compact('school', 'bookings', 'isAjax'))->with('isAjax', true);
                }
                return view($school->resolveView('student.schedule'), compact('school', 'bookings'));
            })->name('schedule');
            
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });