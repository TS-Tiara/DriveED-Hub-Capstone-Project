<?php

use App\Models\School;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;

beforeEach(function () {
    $this->school = School::factory()->create([
        'name' => 'Test Driving School',
        'slug' => 'test-school',
    ]);
});

describe('Login Pages Load', function () {
    test('school login page loads', function () {
        $response = $this->get("/{$this->school->slug}");
        $response->assertStatus(200);
    });

    test('system admin login page loads', function () {
        $response = $this->get('/system-admin/login');
        $response->assertStatus(200);
    });
});

describe('Student Authentication', function () {
    test('student can login with valid credentials', function () {
        $student = Student::factory()->create([
            'school_id' => $this->school->id,
            'email' => 'student@test.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $response = $this->post("/{$this->school->slug}/login", [
            'email' => 'student@test.com',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $response->assertRedirect("/{$this->school->slug}/student");
        $this->assertAuthenticatedAs($student, 'student');
    });

    test('student cannot login with invalid password', function () {
        Student::factory()->create([
            'school_id' => $this->school->id,
            'email' => 'student@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post("/{$this->school->slug}/login", [
            'email' => 'student@test.com',
            'password' => 'wrongpassword',
            'role' => 'student',
        ]);

        $this->assertGuest('student');
    });

    test('student can logout', function () {
        $student = Student::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $this->actingAs($student, 'student');

        $response = $this->post("/{$this->school->slug}/student/logout");

        $this->assertGuest('student');
    });
});

describe('Instructor Authentication', function () {
    test('instructor can login with valid credentials', function () {
        $instructor = Instructor::factory()->create([
            'school_id' => $this->school->id,
            'email' => 'instructor@test.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $response = $this->post("/{$this->school->slug}/login", [
            'email' => 'instructor@test.com',
            'password' => 'password123',
            'role' => 'instructor',
        ]);

        $response->assertRedirect("/{$this->school->slug}/instructor");
        $this->assertAuthenticatedAs($instructor, 'instructor');
    });

    test('instructor can logout', function () {
        $instructor = Instructor::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $this->actingAs($instructor, 'instructor');

        $response = $this->post("/{$this->school->slug}/instructor/logout");

        $this->assertGuest('instructor');
    });
});

describe('Admin Authentication', function () {
    test('admin can login with valid credentials', function () {
        $admin = Admin::factory()->create([
            'school_id' => $this->school->id,
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->post("/{$this->school->slug}/login", [
            'email' => 'admin@test.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertRedirect("/{$this->school->slug}/admin");
        $this->assertAuthenticatedAs($admin, 'admin');
    });

    test('admin can logout', function () {
        $admin = Admin::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->post("/{$this->school->slug}/admin/logout");

        $this->assertGuest('admin');
    });
});
