<?php

/**
 * Schedule Management Test Suite
 * 
 * Tests for:
 * - Admin: Create, update, delete schedules
 * - Admin: Assign instructors to time slots
 * - Instructor: View schedule, toggle time slots, request removal
 * - Student: View available schedule
 * 
 * @version 1.5b
 */

use App\Models\School;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Course;
use App\Models\TimeSlot;
use App\Models\InstructorRemovalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ===========================================
// SETUP HELPERS
// ===========================================
function createSchoolWithAdmin(): array
{
    $school = School::factory()->create();
    $admin = Admin::factory()->create([
        'school_id' => $school->id,
        'is_active' => true,
    ]);
    return [$school, $admin];
}

function createSchoolWithInstructor(): array
{
    $school = School::factory()->create();
    $instructor = Instructor::factory()->create([
        'school_id' => $school->id,
        'status' => 'active',
        'availability' => 'available',
    ]);
    return [$school, $instructor];
}

function createSchoolWithStudent(): array
{
    $school = School::factory()->create();
    $student = Student::factory()->create([
        'school_id' => $school->id,
        'status' => 'active',
        'role' => 'student',
    ]);
    return [$school, $student];
}

// ===========================================
// ADMIN SCHEDULE MANAGEMENT TESTS
// ===========================================
describe('Admin Schedule Management', function () {
    
    test('admin can view schedules page', function () {
        [$school, $admin] = createSchoolWithAdmin();
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.schedules', $school));
        
        $response->assertStatus(200);
    });

    test('admin can create a new time slot', function () {
        [$school, $admin] = createSchoolWithAdmin();
        $course = Course::factory()->create(['school_id' => $school->id]);
        
        $response = $this->actingAs($admin, 'admin')
            ->post(route('schools.admin.schedules.create', $school), [
                'course_id' => $course->id,
                'date' => now()->addDays(7)->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '10:00',
                'max_instructors' => 2,
                'status' => 'open',
            ]);
        
        expect(TimeSlot::where('school_id', $school->id)->count())->toBeGreaterThan(0);
    });

    test('admin can update an existing time slot', function () {
        [$school, $admin] = createSchoolWithAdmin();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->addDays(7),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'notes' => 'Original notes',
        ]);
        
        // The update schedule endpoint updates notes and instructor assignments
        $response = $this->actingAs($admin, 'admin')
            ->put(route('schools.admin.schedules.update', [$school, $timeSlot->id]), [
                'notes' => 'Updated notes',
                'instructor_ids' => [$instructor->id],
            ]);
        
        $timeSlot->refresh();
        expect($timeSlot->notes)->toBe('Updated notes');
    });

    test('admin can delete a time slot', function () {
        [$school, $admin] = createSchoolWithAdmin();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->delete(route('schools.admin.schedules.delete', [$school, $timeSlot->id]));
        
        expect(TimeSlot::find($timeSlot->id))->toBeNull();
    });

    test('admin can view removal requests', function () {
        [$school, $admin] = createSchoolWithAdmin();
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.removalRequests', $school));
        
        $response->assertStatus(200);
    });

    test('admin can approve instructor removal request', function () {
        [$school, $admin] = createSchoolWithAdmin();
        $instructor = Instructor::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->addDays(14),
        ]);
        
        // Attach instructor to time slot
        $timeSlot->instructors()->attach($instructor->id, [
            'school_id' => $school->id,
            'assignment_type' => 'admin_assigned',
        ]);
        
        // Create removal request
        $removalRequest = InstructorRemovalRequest::create([
            'school_id' => $school->id,
            'instructor_id' => $instructor->id,
            'time_slot_id' => $timeSlot->id,
            'reason' => 'Personal emergency',
            'status' => 'pending',
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->post(route('schools.admin.removalRequests.approve', [$school, $removalRequest->id]));
        
        $removalRequest->refresh();
        expect($removalRequest->status)->toBe('approved');
    });

    test('admin can reject instructor removal request', function () {
        [$school, $admin] = createSchoolWithAdmin();
        $instructor = Instructor::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->addDays(14),
        ]);
        
        $removalRequest = InstructorRemovalRequest::create([
            'school_id' => $school->id,
            'instructor_id' => $instructor->id,
            'time_slot_id' => $timeSlot->id,
            'reason' => 'Want a break',
            'status' => 'pending',
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->post(route('schools.admin.removalRequests.reject', [$school, $removalRequest->id]), [
                'admin_notes' => 'Request denied - insufficient notice period.',
            ]);
        
        $removalRequest->refresh();
        expect($removalRequest->status)->toBe('rejected');
    });
});

// ===========================================
// INSTRUCTOR SCHEDULE TESTS
// ===========================================
describe('Instructor Schedule Management', function () {
    
    test('instructor can view their schedule', function () {
        [$school, $instructor] = createSchoolWithInstructor();
        
        $response = $this->actingAs($instructor, 'instructor')
            ->get(route('schools.instructor.schedule', $school));
        
        $response->assertStatus(200);
    });

    test('instructor can toggle (select) an open time slot', function () {
        [$school, $instructor] = createSchoolWithInstructor();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->addDays(7),
            'status' => 'open',
            'max_instructors' => 2,
        ]);
        
        $response = $this->actingAs($instructor, 'instructor')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.instructor.timeslots.toggle', [$school, $timeSlot->id]));
        
        $response->assertStatus(200);
        $timeSlot->refresh();
        expect($timeSlot->hasInstructor($instructor->id))->toBeTrue();
    });

    test('instructor can toggle (leave) a self-selected time slot', function () {
        [$school, $instructor] = createSchoolWithInstructor();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->addDays(7),
            'status' => 'open',
            'max_instructors' => 2,
        ]);
        
        // First select the slot
        $timeSlot->instructors()->attach($instructor->id, [
            'school_id' => $school->id,
            'assignment_type' => 'self_selected',
        ]);
        
        // Then leave it
        $response = $this->actingAs($instructor, 'instructor')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.instructor.timeslots.toggle', [$school, $timeSlot->id]));
        
        expect($timeSlot->fresh()->hasInstructor($instructor->id))->toBeFalse();
    });

    test('instructor cannot leave admin-assigned time slot without request', function () {
        [$school, $instructor] = createSchoolWithInstructor();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->addDays(7),
            'status' => 'open',
        ]);
        
        // Admin assigned slot
        $timeSlot->instructors()->attach($instructor->id, [
            'school_id' => $school->id,
            'assignment_type' => 'admin_assigned',
        ]);
        
        $response = $this->actingAs($instructor, 'instructor')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.instructor.timeslots.toggle', [$school, $timeSlot->id]));
        
        // Should still be assigned
        expect($timeSlot->fresh()->hasInstructor($instructor->id))->toBeTrue();
    });

    test('instructor can request removal from admin-assigned slot', function () {
        [$school, $instructor] = createSchoolWithInstructor();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->addDays(14),
            'status' => 'open',
        ]);
        
        $timeSlot->instructors()->attach($instructor->id, [
            'school_id' => $school->id,
            'assignment_type' => 'admin_assigned',
        ]);
        
        $response = $this->actingAs($instructor, 'instructor')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.instructor.timeslots.requestRemoval', [$school, $timeSlot->id]), [
                'reason' => 'Personal emergency',
            ]);
        
        expect(InstructorRemovalRequest::where('instructor_id', $instructor->id)
            ->where('time_slot_id', $timeSlot->id)
            ->exists())->toBeTrue();
    });

    test('instructor cannot select closed time slot', function () {
        [$school, $instructor] = createSchoolWithInstructor();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->addDays(7),
            'status' => 'closed',
        ]);
        
        $response = $this->actingAs($instructor, 'instructor')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.instructor.timeslots.toggle', [$school, $timeSlot->id]));
        
        expect($timeSlot->hasInstructor($instructor->id))->toBeFalse();
    });

    test('instructor cannot select past time slot', function () {
        [$school, $instructor] = createSchoolWithInstructor();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->subDays(1),
            'status' => 'open',
        ]);
        
        $response = $this->actingAs($instructor, 'instructor')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.instructor.timeslots.toggle', [$school, $timeSlot->id]));
        
        expect($timeSlot->hasInstructor($instructor->id))->toBeFalse();
    });
});

// ===========================================
// STUDENT SCHEDULE TESTS
// ===========================================
describe('Student Schedule View', function () {
    
    test('student can view their schedule', function () {
        [$school, $student] = createSchoolWithStudent();
        
        $response = $this->actingAs($student, 'student')
            ->get(route('schools.student.schedule', $school));
        
        $response->assertStatus(200);
    });

    test('student dashboard loads correctly', function () {
        [$school, $student] = createSchoolWithStudent();
        
        $response = $this->actingAs($student, 'student')
            ->get(route('schools.student.dashboard', $school));
        
        $response->assertStatus(200);
    });
});
