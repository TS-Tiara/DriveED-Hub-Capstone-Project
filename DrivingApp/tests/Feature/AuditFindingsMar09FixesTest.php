<?php

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\Instructor;
use App\Models\School;
use App\Models\Student;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buildSchoolWithAdmin(): array
{
    $school = School::factory()->create();
    $admin = Admin::factory()->create([
        'school_id' => $school->id,
        'is_active' => true,
    ]);

    return [$school, $admin];
}

function createStudentForSchool(School $school, string $role = 'guest'): Student
{
    return Student::create([
        'school_id' => $school->id,
        'name' => 'Test Student ' . uniqid(),
        'email' => 'student_' . uniqid() . '@example.com',
        'password' => 'password123',
        'status' => 'active',
        'role' => $role,
    ]);
}

test('bulk approve does not re-approve rejected or cancelled enrollments', function () {
    [$school, $admin] = buildSchoolWithAdmin();
    $course = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);

    $pendingStudent = createStudentForSchool($school, 'guest');
    $rejectedStudent = createStudentForSchool($school, 'guest');
    $cancelledStudent = createStudentForSchool($school, 'guest');

    $pending = EnrollmentRequest::factory()->create([
        'school_id' => $school->id,
        'learner_id' => $pendingStudent->id,
        'course_id' => $course->id,
        'status' => 'pending',
    ]);

    $rejected = EnrollmentRequest::factory()->create([
        'school_id' => $school->id,
        'learner_id' => $rejectedStudent->id,
        'course_id' => $course->id,
        'status' => 'rejected',
    ]);

    $cancelled = EnrollmentRequest::factory()->create([
        'school_id' => $school->id,
        'learner_id' => $cancelledStudent->id,
        'course_id' => $course->id,
        'status' => 'cancelled',
    ]);

    $this->actingAs($admin, 'admin')
        ->post(route('schools.admin.enrollments.bulkApprove', $school), [
            'enrollment_ids' => [$pending->id, $rejected->id, $cancelled->id],
        ])
        ->assertRedirect();

    expect($pending->fresh()->status)->toBe('approved');
    expect($rejected->fresh()->status)->toBe('rejected');
    expect($cancelled->fresh()->status)->toBe('cancelled');
});

test('admin schedules rejects date windows over 90 days', function () {
    [$school, $admin] = buildSchoolWithAdmin();

    $this->actingAs($admin, 'admin')
        ->from(route('schools.admin.schedules', $school))
        ->get(route('schools.admin.schedules', [
            'school' => $school,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(91)->toDateString(),
        ]))
        ->assertRedirect(route('schools.admin.schedules', $school))
        ->assertSessionHasErrors('end_date');
});

test('admin schedules month query filters using month start and end dates', function () {
    [$school, $admin] = buildSchoolWithAdmin();
    $course = Course::factory()->create(['school_id' => $school->id]);

    $targetMonth = now()->addMonths(5)->format('Y-m');

    TimeSlot::factory()->create([
        'school_id' => $school->id,
        'course_id' => $course->id,
        'date' => now()->addMonths(5)->startOfMonth()->addDays(3)->format('Y-m-d'),
        'notes' => 'target-month-schedule',
    ]);

    TimeSlot::factory()->create([
        'school_id' => $school->id,
        'course_id' => $course->id,
        'date' => now()->addMonths(6)->startOfMonth()->addDays(3)->format('Y-m-d'),
        'notes' => 'outside-month-schedule',
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('schools.admin.schedules', [
            'school' => $school,
            'month' => $targetMonth,
        ]))
        ->assertStatus(200)
        ->assertSee('target-month-schedule')
        ->assertDontSee('outside-month-schedule');
});

test('instructor schedule page handles string times without crashing', function () {
    $school = School::factory()->create();
    $instructor = Instructor::factory()->create([
        'school_id' => $school->id,
        'status' => 'active',
    ]);
    $course = Course::factory()->create(['school_id' => $school->id]);

    $slot = TimeSlot::factory()->create([
        'school_id' => $school->id,
        'course_id' => $course->id,
        'date' => now()->addDays(2)->format('Y-m-d'),
        'start_time' => '09:30:00',
        'end_time' => '10:30:00',
    ]);

    $slot->instructors()->attach($instructor->id, [
        'school_id' => $school->id,
        'assignment_type' => 'self_selected',
    ]);

    $this->actingAs($instructor, 'instructor')
        ->get(route('schools.instructor.schedule', $school))
        ->assertStatus(200);
});

test('my students marks assignment using scheduled and completed bookings only', function () {
    $school = School::factory()->create();
    $instructor = Instructor::factory()->create([
        'school_id' => $school->id,
        'status' => 'active',
    ]);
    $course = Course::factory()->create(['school_id' => $school->id]);

    $scheduledStudent = createStudentForSchool($school, 'student');
    $completedStudent = createStudentForSchool($school, 'student');
    $cancelledStudent = createStudentForSchool($school, 'student');

    Booking::factory()->create([
        'school_id' => $school->id,
        'student_id' => $scheduledStudent->id,
        'instructor_id' => $instructor->id,
        'course_id' => $course->id,
        'status' => 'scheduled',
    ]);

    Booking::factory()->create([
        'school_id' => $school->id,
        'student_id' => $completedStudent->id,
        'instructor_id' => $instructor->id,
        'course_id' => $course->id,
        'status' => 'completed',
    ]);

    Booking::factory()->create([
        'school_id' => $school->id,
        'student_id' => $cancelledStudent->id,
        'instructor_id' => $instructor->id,
        'course_id' => $course->id,
        'status' => 'cancelled',
    ]);

    $this->actingAs($instructor, 'instructor')
        ->get(route('schools.instructor.students.index', $school))
        ->assertStatus(200)
        ->assertViewHas('students', function ($students) use ($scheduledStudent, $completedStudent, $cancelledStudent) {
            $collection = $students->getCollection()->keyBy('id');

            return $collection->get($scheduledStudent->id)->is_assigned === true
                && $collection->get($completedStudent->id)->is_assigned === true
                && $collection->get($cancelledStudent->id)->is_assigned === false;
        });
});
