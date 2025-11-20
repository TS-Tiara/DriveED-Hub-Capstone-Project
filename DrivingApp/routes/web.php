<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminTimeSlotController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstructorTimeSlotController;
use App\Models\School;

Route::redirect('/', '/drivingschool1');

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

        Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function (): void {
            Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/dashboard', [AdminController::class, 'dashboard']);

            Route::get('/create-account', [AdminController::class, 'createAccount'])->name('createAccount');
            Route::post('/store-account', [AdminController::class, 'storeAccount'])->name('storeAccount');

            Route::get('/students', [AdminController::class, 'students'])->name('students');
            Route::get('/students/{id}/edit', [AdminController::class, 'editStudent'])->name('students.edit');
            Route::put('/students/{id}', [AdminController::class, 'updateStudent'])->name('students.update');
            Route::patch('/students/{id}/toggle-status', [AdminController::class, 'toggleStudentStatus'])->name('students.toggleStatus');

            Route::get('/instructors', [AdminController::class, 'instructors'])->name('instructors');
            Route::post('/instructors', [AdminController::class, 'storeAccount'])->name('instructors.store');
            Route::get('/instructors/{id}/edit', [AdminController::class, 'editInstructor'])->name('instructors.edit');
            Route::put('/instructors/{id}', [AdminController::class, 'updateInstructor'])->name('instructors.update');
            Route::patch('/instructors/{id}/toggle-status', [AdminController::class, 'toggleInstructorStatus'])->name('instructors.toggleStatus');
            Route::patch('/instructors/{id}/availability', [AdminController::class, 'toggleAvailability'])->name('instructors.availability');

            Route::get('/timeslots', [AdminTimeSlotController::class, 'index'])->name('timeslots.index');
            Route::post('/timeslots', [AdminTimeSlotController::class, 'store'])->name('timeslots.store');
            Route::post('/timeslots/{id}/assign', [AdminTimeSlotController::class, 'assignInstructors'])->name('timeslots.assign');
            Route::delete('/timeslots/{id}', [AdminTimeSlotController::class, 'destroy'])->name('timeslots.destroy');
            Route::patch('/timeslots/{id}/toggle-status', [AdminTimeSlotController::class, 'toggleStatus'])->name('timeslots.toggleStatus');

            Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules');
            Route::get('/schedules/create', [AdminController::class, 'createSchedule'])->name('schedules.create');
            Route::post('/schedules', [AdminController::class, 'storeSchedule'])->name('schedules.store');
            Route::get('/schedules/{id}/edit', [AdminController::class, 'editSchedule'])->name('schedules.edit');
            Route::put('/schedules/{id}', [AdminController::class, 'updateSchedule'])->name('schedules.update');
            Route::delete('/schedules/{id}', [AdminController::class, 'deleteSchedule'])->name('schedules.delete');

            Route::get('/reports/students', [AdminController::class, 'studentReports'])->name('reports.students');
            Route::get('/reports/instructors', [AdminController::class, 'instructorReports'])->name('reports.instructors');
            Route::get('/reports/logs', [AdminController::class, 'logs'])->name('reports.logs');
            Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
        });

        Route::prefix('instructor')->name('instructor.')->middleware('auth:instructor')->group(function (): void {
            Route::get('/', function (School $school) {
                return view($school->resolveView('instructor.dashboard'), ['school' => $school]);
            })->name('dashboard');

            Route::get('/dashboard', function (School $school) {
                return view($school->resolveView('instructor.dashboard'), ['school' => $school]);
            });

            Route::get('/timeslots', [InstructorTimeSlotController::class, 'index'])->name('timeslots.index');
            Route::post('/timeslots/{id}/toggle', [InstructorTimeSlotController::class, 'toggle'])->name('timeslots.toggle');
            Route::get('/my-schedule', [InstructorTimeSlotController::class, 'mySchedule'])->name('schedule');
        });

        Route::prefix('student')->name('student.')->middleware('auth:student')->group(function (): void {
            Route::get('/', function (School $school) {
                return view($school->resolveView('student.dashboard'), ['school' => $school]);
            })->name('dashboard');

            Route::get('/dashboard', function (School $school) {
                return view($school->resolveView('student.dashboard'), ['school' => $school]);
            });
        });
    });