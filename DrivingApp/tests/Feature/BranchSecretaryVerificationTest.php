<?php

use App\Models\Admin;
use App\Models\Branch;
use App\Models\School;
use App\Models\Student;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buildBranchSecretaryContext(): array
{
    $school = School::factory()->create();

    $branch = Branch::create([
        'school_id' => $school->id,
        'name' => 'Main Branch',
        'slug' => 'main-branch',
        'address' => 'Main Street',
        'contact_number' => '09123456789',
        'email' => 'branch@example.com',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $secretary = Admin::factory()->create([
        'school_id' => $school->id,
        'branch_id' => $branch->id,
        'role' => Admin::ROLE_BRANCH_SECRETARY,
        'is_active' => true,
    ]);

    return [$school, $branch, $secretary];
}

test('branch secretary can load affected pages', function () {
    [$school, $branch, $secretary] = buildBranchSecretaryContext();

    $student = Student::factory()->create([
        'school_id' => $school->id,
        'branch_id' => $branch->id,
        'role' => 'student',
        'status' => 'active',
    ]);

    $course = Course::factory()->create([
        'school_id' => $school->id,
        'status' => 'active',
    ]);

    EnrollmentRequest::factory()->create([
        'school_id' => $school->id,
        'learner_id' => $student->id,
        'course_id' => $course->id,
        'branch_id' => $branch->id,
        'status' => 'approved',
        'payment_status' => 'paid',
    ]);

    $this->actingAs($secretary, 'admin')
        ->get(route('schools.admin.schedules', $school))
        ->assertStatus(200);

    $this->actingAs($secretary, 'admin')
        ->get(route('schools.admin.sessions.index', $school))
        ->assertStatus(200);

    $this->actingAs($secretary, 'admin')
        ->get(route('schools.admin.phase-progressions.index', $school))
        ->assertStatus(200);

    $this->actingAs($secretary, 'admin')
        ->get(route('schools.admin.enrollments.index', $school))
        ->assertStatus(200);
});

test('user management page renders custom paginator markup', function () {
    [$school, $branch, $secretary] = buildBranchSecretaryContext();

    for ($index = 1; $index <= 15; $index++) {
        Student::factory()->create([
            'school_id' => $school->id,
            'branch_id' => $branch->id,
            'role' => 'student',
            'status' => 'active',
            'email' => "student{$index}@gmail.com",
        ]);
    }

    $response = $this->actingAs($secretary, 'admin')
        ->get(route('schools.admin.userManagement', $school));

    $response->assertStatus(200);

    $response->assertSee('ds-pagination-nav', false);
});
