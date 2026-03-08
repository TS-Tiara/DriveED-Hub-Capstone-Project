<?php

use App\Models\School;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\SchoolSetting;

beforeEach(function () {
    $this->school = School::factory()->create([
        'name' => 'Test Driving School',
        'slug' => 'test-school',
    ]);
    
    SchoolSetting::factory()->create([
        'school_id' => $this->school->id,
    ]);
    
    $this->admin = Admin::factory()->create([
        'school_id' => $this->school->id,
        'is_active' => true,
    ]);
    
    $this->instructor = Instructor::factory()->create([
        'school_id' => $this->school->id,
        'status' => 'active',
    ]);
    
    $this->student = Student::factory()->create([
        'school_id' => $this->school->id,
        'status' => 'active',
    ]);
});

describe('Dashboard Pages Load', function () {
    test('admin dashboard loads', function () {
        $this->actingAs($this->admin, 'admin');

        $response = $this->get("/{$this->school->slug}/admin");
        $response->assertStatus(200);
    });

    test('instructor dashboard loads', function () {
        $this->actingAs($this->instructor, 'instructor');

        $response = $this->get("/{$this->school->slug}/instructor");
        $response->assertStatus(200);
    });

    test('student dashboard loads', function () {
        $this->actingAs($this->student, 'student');

        $response = $this->get("/{$this->school->slug}/student");
        $response->assertStatus(200);
    });

    test('guest dashboard redirects to courses', function () {
        // Guest dashboard typically redirects to courses page or login
        $response = $this->get("/{$this->school->slug}/guest/dashboard");
        // Accept either 200 (if dashboard exists) or 302 (redirect to courses/login)
        $response->assertStatus(302);
    });
});

describe('Admin Pages Load', function () {
    test('admin courses page loads', function () {
        $this->actingAs($this->admin, 'admin');
        $response = $this->get("/{$this->school->slug}/admin/courses");
        $response->assertStatus(200);
    });

    test('admin bookings page loads', function () {
        $this->actingAs($this->admin, 'admin');
        $response = $this->get("/{$this->school->slug}/admin/bookings");
        $response->assertStatus(200);
    });

    test('admin payments page loads', function () {
        $this->actingAs($this->admin, 'admin');
        $response = $this->get("/{$this->school->slug}/admin/payments");
        $response->assertStatus(200);
    });

    test('admin enrollments page loads', function () {
        $this->actingAs($this->admin, 'admin');
        $response = $this->get("/{$this->school->slug}/admin/enrollments");
        $response->assertStatus(200);
    });

    test('admin schedules page loads', function () {
        $this->actingAs($this->admin, 'admin');
        $response = $this->get("/{$this->school->slug}/admin/schedules");
        $response->assertStatus(200);
    });

    test('admin settings page loads', function () {
        $this->actingAs($this->admin, 'admin');
        $response = $this->get("/{$this->school->slug}/admin/settings");
        $response->assertStatus(200);
    });

    test('admin reports page loads', function () {
        $this->actingAs($this->admin, 'admin');
        $response = $this->get("/{$this->school->slug}/admin/reports");
        $response->assertStatus(200);
    });

    test('admin user management page loads', function () {
        $this->actingAs($this->admin, 'admin');
        $response = $this->get("/{$this->school->slug}/admin/user-management");
        $response->assertStatus(200);
    });

    test('admin profile page loads', function () {
        $this->actingAs($this->admin, 'admin');
        $response = $this->get("/{$this->school->slug}/admin/profile");
        $response->assertStatus(200);
    });
});

describe('Instructor Pages Load', function () {
    test('instructor schedule page loads', function () {
        $this->actingAs($this->instructor, 'instructor');
        $response = $this->get("/{$this->school->slug}/instructor/my-schedule");
        $response->assertStatus(200);
    });

    test('instructor students page loads', function () {
        $this->actingAs($this->instructor, 'instructor');
        $response = $this->get("/{$this->school->slug}/instructor/students");
        $response->assertStatus(200);
    });

    test('instructor profile page loads', function () {
        $this->actingAs($this->instructor, 'instructor');
        $response = $this->get("/{$this->school->slug}/instructor/profile");
        $response->assertStatus(200);
    });

    test('instructor sessions page loads', function () {
        $this->actingAs($this->instructor, 'instructor');
        $response = $this->get("/{$this->school->slug}/instructor/sessions");
        $response->assertStatus(200);
    });
});

describe('Student Pages Load', function () {
    test('student schedule page loads', function () {
        $this->actingAs($this->student, 'student');
        $response = $this->get("/{$this->school->slug}/student/schedule");
        $response->assertStatus(200);
    });

    test('student courses page loads', function () {
        $this->actingAs($this->student, 'student');
        $response = $this->get("/{$this->school->slug}/student/courses");
        $response->assertStatus(200);
    });

    test('student payments page loads', function () {
        $this->actingAs($this->student, 'student');
        $response = $this->get("/{$this->school->slug}/student/payments");
        $response->assertStatus(200);
    });

    test('student profile page loads', function () {
        $this->actingAs($this->student, 'student');
        $response = $this->get("/{$this->school->slug}/student/profile");
        $response->assertStatus(200);
    });

    test('student progress page loads', function () {
        $this->actingAs($this->student, 'student');
        $response = $this->get("/{$this->school->slug}/student/progress");
        $response->assertStatus(200);
    });

    test('student my-course page loads', function () {
        $this->actingAs($this->student, 'student');
        $response = $this->get("/{$this->school->slug}/student/my-course");
        $response->assertStatus(200);
    });
});
