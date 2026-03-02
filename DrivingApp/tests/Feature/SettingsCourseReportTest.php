<?php

/**
 * Settings, Course, and Report Test Suite
 * 
 * Tests for:
 * - Admin: School settings management
 * - Admin: Course management
 * - Admin: Course package management
 * - Admin: Reports and exports
 * 
 * @version 1.7
 */

use App\Models\School;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Booking;
use App\Models\TimeSlot;
use App\Models\Payment;
use App\Models\SchoolSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ===========================================
// ADMIN SETTINGS MANAGEMENT TESTS
// ===========================================
describe('Admin Settings Management', function () {
    
    test('admin can view settings page', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        SchoolSetting::factory()->create(['school_id' => $school->id]);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.settings', $school));
        
        $response->assertStatus(200);
    });

    test('admin can update school settings', function () {
        $school = School::factory()->create([
            'instructor_removal_notice_days' => 5,
        ]);
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        SchoolSetting::factory()->create(['school_id' => $school->id]);
        
        $response = $this->actingAs($admin, 'admin')
            ->post(route('schools.admin.settings.update', $school), [
                'instructor_removal_notice_days' => 10,
                'instructor_selection_mode' => 'student_chooses',
                'advance_booking_days' => 7,
                'enable_booking_queue' => true,
                'booking_queue_days' => 3,
                'primary_color' => '#667eea',
                'secondary_color' => '#764ba2',
                'accent_color' => '#4f46e5',
                'background_type' => 'color',
                'background_color' => '#f3f4f6',
                'background_opacity' => 100,
            ]);
        
        $school->refresh();
        expect($school->instructor_removal_notice_days)->toBe(10);
    });
});

// ===========================================
// ADMIN COURSE MANAGEMENT TESTS
// ===========================================
describe('Admin Course Management', function () {
    
    test('admin can view courses page', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        // The route name is schools.admin.courses (not courses.index)
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.courses', $school));
        
        $response->assertStatus(200);
    });

    test('admin can create a new course', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        // Use the correct route and required fields
        $response = $this->actingAs($admin, 'admin')
            ->post(route('schools.admin.courses.store', $school), [
                'title' => 'Basic Driving Course',
                'description' => 'Learn the basics of driving',
                'type' => 'TDC',
                'course_type' => 'theoretical',
                'license_type' => 'non_professional',
                'hours_required' => 20,
                'status' => 'active',
            ]);
        
        $response->assertRedirect();
        expect(Course::where('title', 'Basic Driving Course')->exists())->toBeTrue();
    });

    test('admin can update an existing course', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $course = Course::factory()->create([
            'school_id' => $school->id,
            'title' => 'Old Title',
            'status' => 'active',
            'type' => 'TDC',
            'course_type' => 'theoretical',
            'license_type' => 'non_professional',
            'hours_required' => 15,
        ]);
        
        // Use the correct route: schools.admin.courses.update
        $response = $this->actingAs($admin, 'admin')
            ->put(route('schools.admin.courses.update', [$school, $course->id]), [
                'title' => 'New Title',
                'description' => 'Updated description',
                'type' => 'PDC',
                'course_type' => 'practical',
                'license_type' => 'professional',
                'hours_required' => 25,
                'status' => 'active',
            ]);
        
        $response->assertRedirect();
        $course->refresh();
        expect($course->title)->toBe('New Title');
    });

    test('admin can delete a course', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $course = Course::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        $courseId = $course->id;
        
        // The route is schools.admin.courses.delete (not destroy)
        $response = $this->actingAs($admin, 'admin')
            ->delete(route('schools.admin.courses.delete', [$school, $course->id]));
        
        $response->assertRedirect();
        expect(Course::find($courseId))->toBeNull();
    });
});

// ===========================================
// ADMIN COURSE PACKAGE MANAGEMENT TESTS
// ===========================================
describe('Admin Course Package Management', function () {
    
    test('admin can create a course package', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $course = Course::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        
        // Use correct route and required fields
        $response = $this->actingAs($admin, 'admin')
            ->post(route('schools.admin.courses.packages.store', [$school, $course->id]), [
                'name' => 'Premium Package',
                'transmission_type' => 'manual',
                'price' => 7000,
                'training_hours' => 25,
                'description' => 'Premium package description',
            ]);
        
        $response->assertRedirect();
        expect(CoursePackage::where('name', 'Premium Package')->exists())->toBeTrue();
    });

    test('admin can update a course package', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $course = Course::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        $package = CoursePackage::factory()->create([
            'course_id' => $course->id,
            'name' => 'Old Package Name',
            'transmission_type' => 'manual',
            'price' => 5000,
        ]);
        
        // Use correct route
        $response = $this->actingAs($admin, 'admin')
            ->put(route('schools.admin.courses.packages.update', [$school, $course->id, $package->id]), [
                'name' => 'New Package Name',
                'transmission_type' => 'automatic',
                'price' => 8000,
                'training_hours' => 30,
                'description' => 'Updated description',
            ]);
        
        $response->assertRedirect();
        $package->refresh();
        expect($package->name)->toBe('New Package Name');
    });

    test('admin can delete a course package', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $course = Course::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        $package = CoursePackage::factory()->create([
            'course_id' => $course->id,
        ]);
        $packageId = $package->id;
        
        // Use correct route: packages.delete (not destroy)
        $response = $this->actingAs($admin, 'admin')
            ->delete(route('schools.admin.courses.packages.delete', [$school, $course->id, $package->id]));
        
        $response->assertRedirect();
        expect(CoursePackage::find($packageId))->toBeNull();
    });
});

// ===========================================
// ADMIN REPORTS MANAGEMENT TESTS
// ===========================================
describe('Admin Reports Management', function () {
    
    test('admin can view unified reports analytics index', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        // Canonical unified analytics route
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.reports.index', $school));
        
        $response->assertStatus(200)
            ->assertSee('Reports &amp; Analytics', false);
    });

    test('student reports route resolves to unified analytics page', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        Student::factory()->create(['school_id' => $school->id, 'status' => 'active', 'role' => 'student']);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.reports.students', $school));
        
        $response->assertStatus(200)
            ->assertSee('Reports &amp; Analytics', false);
    });

    test('instructor reports route resolves to unified analytics page', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        Instructor::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.reports.instructors', $school));
        
        $response->assertStatus(200)
            ->assertSee('Reports &amp; Analytics', false);
    });

    test('logs reports route resolves to unified analytics page', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        // Legacy route retained for compatibility, now unified to analytics
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.reports.logs', $school));
        
        $response->assertStatus(200)
            ->assertSee('Reports &amp; Analytics', false);
    });
});

// ===========================================
// ADMIN EXCEL EXPORT TESTS
// ===========================================
describe('Admin Excel Exports', function () {
    
    test('admin can export students as Excel', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        Student::factory()->create(['school_id' => $school->id, 'status' => 'active', 'role' => 'student']);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.reports.export.students', $school));
        
        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.ms-excel');
    });

    test('admin can export instructors as Excel', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        Instructor::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.reports.export.instructors', $school));
        
        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.ms-excel');
    });

    test('admin can export bookings as Excel', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.reports.export.bookings', $school));
        
        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.ms-excel');
    });

    test('admin can export payments as Excel', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.reports.export.payments', $school));
        
        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.ms-excel');
    });

    test('admin can export courses as Excel', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.reports.export.courses', $school));
        
        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.ms-excel');
    });
});

// ===========================================
// ADMIN PDF EXPORT TESTS
// ===========================================
describe('Admin PDF Exports', function () {
    
    test('admin can export students as PDF', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        Student::factory()->create(['school_id' => $school->id, 'status' => 'active', 'role' => 'student']);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.exports.students.pdf', $school));
        
        $response->assertStatus(200);
    });

    test('admin can export instructors as PDF', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        Instructor::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.exports.instructors.pdf', $school));
        
        $response->assertStatus(200);
    });

    test('admin can export schedules as PDF', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.exports.schedules.pdf', $school));
        
        $response->assertStatus(200);
    });

    test('admin can export payments as PDF', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.exports.payments.pdf', $school));
        
        $response->assertStatus(200);
    });

    test('admin can export courses as PDF', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.exports.courses.pdf', $school));
        
        $response->assertStatus(200);
    });
});
