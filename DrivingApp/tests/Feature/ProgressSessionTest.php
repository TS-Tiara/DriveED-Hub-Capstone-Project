<?php

/**
 * Progress Test Suite
 * 
 * Tests for:
 * - Instructor: Create, view, update progress records via JSON
 * - Student: View their progress
 * 
 * @version 1.6
 */

use App\Models\School;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Course;
use App\Models\Progress;
use App\Models\Booking;
use App\Models\TimeSlot;
use App\Models\SchoolSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ===========================================
// INSTRUCTOR PROGRESS MANAGEMENT TESTS
// ===========================================
describe('Instructor Progress Management', function () {

    test('instructor can view progress index', function () {
            $school = School::factory()->create();
            $instructor = Instructor::factory()->create([
                'school_id' => $school->id,
                'status' => 'active',
            ]);

            $response = $this->actingAs($instructor, 'instructor')
                ->get(route('schools.instructor.progress.index', $school));

            $response->assertStatus(200);
        }
        );

        test('instructor can create progress record via json', function () {
            $school = School::factory()->create();
            $instructor = Instructor::factory()->create([
                'school_id' => $school->id,
                'status' => 'active',
            ]);
            $student = Student::factory()->create(['school_id' => $school->id, 'status' => 'active', 'role' => 'student']);
            $course = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
            $timeSlot = TimeSlot::factory()->create(['school_id' => $school->id]);

            Booking::factory()->create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
                'instructor_id' => $instructor->id,
                'time_slot_id' => $timeSlot->id,
                'status' => 'scheduled',
            ]);

            $response = $this->actingAs($instructor, 'instructor')
                ->postJson(route('schools.instructor.progress.store', $school), [
                'student_id' => $student->id,
                'course_id' => $course->id,
                'completion_percent' => 50.00,
                'notes' => 'Good progress on parallel parking',
            ]);

            $response->assertStatus(201);
            expect(Progress::where('student_id', $student->id)->exists())->toBeTrue();
        }
        );

        test('instructor can view progress record via json', function () {
            $school = School::factory()->create();
            $instructor = Instructor::factory()->create([
                'school_id' => $school->id,
                'status' => 'active',
            ]);
            $student = Student::factory()->create(['school_id' => $school->id, 'status' => 'active', 'role' => 'student']);
            $course = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
            $timeSlot = TimeSlot::factory()->create(['school_id' => $school->id]);

            // Create a booking so the instructor has access to the student
            Booking::factory()->create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
                'instructor_id' => $instructor->id,
                'time_slot_id' => $timeSlot->id,
                'status' => 'scheduled',
            ]);

            $progress = Progress::factory()->create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
            ]);

            $response = $this->actingAs($instructor, 'instructor')
                ->getJson(route('schools.instructor.progress.show', [$school, $progress->id]));

            $response->assertStatus(200)
                ->assertJson(['success' => true]);
        }
        );

        test('instructor can update progress record via json', function () {
            $school = School::factory()->create();
            $instructor = Instructor::factory()->create([
                'school_id' => $school->id,
                'status' => 'active',
            ]);
            $student = Student::factory()->create(['school_id' => $school->id, 'status' => 'active', 'role' => 'student']);
            $course = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
            $timeSlot = TimeSlot::factory()->create(['school_id' => $school->id]);

            Booking::factory()->create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
                'instructor_id' => $instructor->id,
                'time_slot_id' => $timeSlot->id,
                'status' => 'scheduled',
            ]);

            $progress = Progress::factory()->create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
                'notes' => 'Original notes',
                'completion_percent' => 25.00,
            ]);

            $response = $this->actingAs($instructor, 'instructor')
                ->putJson(route('schools.instructor.progress.update', [$school, $progress->id]), [
                'completion_percent' => 75.00,
                'notes' => 'Updated notes',
            ]);

            $response->assertStatus(200);
            $progress->refresh();
            expect($progress->notes)->toBe('Updated notes');
            expect($progress->completion_percent)->toBe('75.00');
        }
        );

        test('instructor can delete progress record via json', function () {
            $school = School::factory()->create();
            $instructor = Instructor::factory()->create([
                'school_id' => $school->id,
                'status' => 'active',
            ]);
            $student = Student::factory()->create(['school_id' => $school->id, 'status' => 'active', 'role' => 'student']);
            $course = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
            $timeSlot = TimeSlot::factory()->create(['school_id' => $school->id]);

            Booking::factory()->create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
                'instructor_id' => $instructor->id,
                'time_slot_id' => $timeSlot->id,
                'status' => 'scheduled',
            ]);

            $progress = Progress::factory()->create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
            ]);
            $progressId = $progress->id;

            $response = $this->actingAs($instructor, 'instructor')
                ->deleteJson(route('schools.instructor.progress.destroy', [$school, $progress->id]));

            $response->assertStatus(200);
            expect(Progress::find($progressId))->toBeNull();
        }
        );    });

// ===========================================
// STUDENT PROGRESS VIEW TESTS
// ===========================================
describe('Student Progress View', function () {

    test('student can view their progress list', function () {
            $school = School::factory()->create();
            SchoolSetting::factory()->create(['school_id' => $school->id]);
            $student = Student::factory()->create([
                'school_id' => $school->id,
                'status' => 'active',
                'role' => 'student',
            ]);

            $response = $this->actingAs($student, 'student')
                ->get(route('schools.student.progress.index', $school));

            $response->assertStatus(200);
        }
        );

        test('student can only see their own progress', function () {
            $school = School::factory()->create();
            SchoolSetting::factory()->create(['school_id' => $school->id]);
            $student = Student::factory()->create([
                'school_id' => $school->id,
                'status' => 'active',
                'role' => 'student',
            ]);
            $otherStudent = Student::factory()->create([
                'school_id' => $school->id,
                'status' => 'active',
                'role' => 'student',
            ]);
            $course = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);

            // Create progress for both students
            Progress::factory()->create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
            ]);
            Progress::factory()->create([
                'school_id' => $school->id,
                'student_id' => $otherStudent->id,
                'course_id' => $course->id,
            ]);

            $response = $this->actingAs($student, 'student')
                ->getJson(route('schools.student.progress.index', $school));

            $response->assertStatus(200);
            // Student should only see their own progress (1 record)
            expect(count($response->json('progresses')['data']))->toBe(1);
        }
        );    });
