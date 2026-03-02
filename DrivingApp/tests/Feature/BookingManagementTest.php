<?php

/**
 * Booking Management Test Suite
 * 
 * Tests for:
 * - Admin: View, create, update, delete bookings
 * - Admin: Update booking status
 * - Student: Create booking, confirm booking, cancel booking
 * - Instructor: Update attendance, provide feedback
 * 
 * @version 1.5b
 */

use App\Models\School;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Course;
use App\Models\TimeSlot;
use App\Models\Booking;
use App\Models\EnrollmentRequest;
use App\Models\SchoolSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ===========================================
// ADMIN BOOKING MANAGEMENT TESTS
// ===========================================
describe('Admin Booking Management', function () {
    
    test('admin can view bookings index', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.bookings.index', $school));
        
        $response->assertStatus(200);
    });

    test('admin can view single booking details', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create(['school_id' => $school->id]);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
        ]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'instructor_id' => $instructor->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.bookings.show', [$school, $booking->id]));
        
        $response->assertStatus(200);
    });

    test('admin can create a new booking', function () {
        $school = School::factory()->create();
        SchoolSetting::factory()->create(['school_id' => $school->id]);
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $student = Student::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->addDays(7),
            'status' => 'open',
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->post(route('schools.admin.bookings.store', $school), [
                'student_id' => $student->id,
                'instructor_id' => $instructor->id,
                'course_id' => $course->id,
                'time_slot_id' => $timeSlot->id,
                'scheduled_at' => now()->addDays(7)->toDateTimeString(),
                'status' => 'scheduled',
            ]);
        
        expect(Booking::where('student_id', $student->id)->exists())->toBeTrue();
    });

    test('admin can update booking', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create(['school_id' => $school->id]);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
        ]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'instructor_id' => $instructor->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
            'notes' => 'Original notes',
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->put(route('schools.admin.bookings.update', [$school, $booking->id]), [
                'student_id' => $student->id,
                'instructor_id' => $instructor->id,
                'course_id' => $course->id,
                'scheduled_at' => $booking->scheduled_at->toDateTimeString(),
                'notes' => 'Updated notes',
            ]);
        
        $booking->refresh();
        expect($booking->notes)->toBe('Updated notes');
    });

    test('admin can delete booking', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create(['school_id' => $school->id]);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
        ]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'instructor_id' => $instructor->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->delete(route('schools.admin.bookings.destroy', [$school, $booking->id]));
        
        $response->assertStatus(200);
        expect(Booking::find($booking->id))->toBeNull();
    });

    test('admin can update booking status', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create(['school_id' => $school->id]);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
        ]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'instructor_id' => $instructor->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'scheduled',
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->patch(route('schools.admin.bookings.updateStatus', [$school, $booking->id]), [
                'status' => 'completed',
            ]);
        
        $booking->refresh();
        expect($booking->status)->toBe('completed');
    });
});

// ===========================================
// STUDENT BOOKING TESTS
// ===========================================
describe('Student Booking Management', function () {
    
    test('student can create a booking', function () {
        $school = School::factory()->create();
        SchoolSetting::factory()->create(['school_id' => $school->id]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
            'availability' => 'available',
        ]);
        $student = Student::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
            'role' => 'student',
        ]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->addDays(7),
            'status' => 'open',
        ]);
        
        // Attach instructor to time slot
        $timeSlot->instructors()->attach($instructor->id, [
            'school_id' => $school->id,
            'assignment_type' => 'admin_assigned',
        ]);
        
        $response = $this->actingAs($student, 'student')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.student.bookings.store', $school), [
                'student_id' => $student->id,
                'time_slot_id' => $timeSlot->id,
                'instructor_id' => $instructor->id,
                'course_id' => $course->id,
                'scheduled_at' => now()->addDays(7)->toDateTimeString(),
            ]);
        
        expect(Booking::where('student_id', $student->id)->exists())->toBeTrue();
    });

    test('student can confirm a booking', function () {
        $school = School::factory()->create();
        SchoolSetting::factory()->create(['school_id' => $school->id]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create(['school_id' => $school->id]);
        $student = Student::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
            'role' => 'student',
        ]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
        ]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'instructor_id' => $instructor->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'pending',
        ]);
        
        $response = $this->actingAs($student, 'student')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.student.bookings.confirm', [$school, $booking->id]));
        
        $booking->refresh();
        expect($booking->status)->toBe('scheduled');  // confirmBooking changes from 'pending' to 'scheduled'
    });

    test('student can remove from queue', function () {
        $school = School::factory()->create();
        SchoolSetting::factory()->create(['school_id' => $school->id]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create(['school_id' => $school->id]);
        $student = Student::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
            'role' => 'student',
        ]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
        ]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'instructor_id' => $instructor->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'pending',  // Must be 'pending' for removeFromQueue to work
        ]);
        
        $response = $this->actingAs($student, 'student')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->delete(route('schools.student.bookings.removeQueue', [$school, $booking->id]));
        
        $booking->refresh();
        expect($booking->status)->toBe('cancelled');  // removeFromQueue cancels, doesn't delete
    });

    test('student can view their payments', function () {
        $school = School::factory()->create();
        SchoolSetting::factory()->create(['school_id' => $school->id]);
        $student = Student::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
            'role' => 'student',
        ]);
        
        $response = $this->actingAs($student, 'student')
            ->get(route('schools.student.payments.index', $school));
        
        $response->assertStatus(200);
    });

    test('student can view their progress', function () {
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
    });
});

// ===========================================
// INSTRUCTOR BOOKING TESTS
// ===========================================
describe('Instructor Booking Actions', function () {
    
    test('instructor can update attendance for a booking', function () {
        $school = School::factory()->create();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
        ]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'instructor_id' => $instructor->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'confirmed',
        ]);
        
        $response = $this->actingAs($instructor, 'instructor')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.instructor.bookings.attendance', [$school, $booking->id]), [
                'attendance_status' => 'attended',
            ]);
        
        $booking->refresh();
        expect($booking->attendance_status)->toBe('attended');
    });

    test('instructor can provide feedback for a booking', function () {
        $school = School::factory()->create();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
        ]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'instructor_id' => $instructor->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'completed',
        ]);
        
        $response = $this->actingAs($instructor, 'instructor')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.instructor.bookings.feedback', [$school, $booking->id]), [
                'instructor_feedback' => 'Great progress on parallel parking!',
            ]);
        
        $booking->refresh();
        expect($booking->instructor_feedback)->toBe('Great progress on parallel parking!');
    });

    test('instructor can update grade only for a completed booking', function () {
        $school = School::factory()->create();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
        ]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'instructor_id' => $instructor->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($instructor, 'instructor')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.instructor.lessons.update', [$school, $booking->id]), [
                'session_grade' => 89.5,
            ]);

        $response->assertStatus(200);
        $booking->refresh();
        expect((float) $booking->session_grade)->toBe(89.5);
    });

    test('instructor cannot provide feedback for a non-completed booking', function () {
        $school = School::factory()->create();
        $course = Course::factory()->create(['school_id' => $school->id]);
        $instructor = Instructor::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $timeSlot = TimeSlot::factory()->create([
            'school_id' => $school->id,
            'course_id' => $course->id,
        ]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'instructor_id' => $instructor->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'confirmed',
            'instructor_feedback' => null,
        ]);

        $response = $this->actingAs($instructor, 'instructor')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('schools.instructor.bookings.feedback', [$school, $booking->id]), [
                'instructor_feedback' => 'Trying to comment too early',
            ]);

        $response->assertStatus(422);
        $booking->refresh();
        expect($booking->instructor_feedback)->toBeNull();
    });

    test('instructor can view their students', function () {
        $school = School::factory()->create();
        $instructor = Instructor::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        
        $response = $this->actingAs($instructor, 'instructor')
            ->get(route('schools.instructor.students.index', $school));
        
        $response->assertStatus(200);
    });

    test('instructor can view student details', function () {
        $school = School::factory()->create();
        $instructor = Instructor::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        $student = Student::factory()->create(['school_id' => $school->id]);
        
        $response = $this->actingAs($instructor, 'instructor')
            ->get(route('schools.instructor.students.show', [$school, $student->id]));
        
        $response->assertStatus(200);
    });

    test('instructor can view their dashboard', function () {
        $school = School::factory()->create();
        $instructor = Instructor::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
        ]);
        
        $response = $this->actingAs($instructor, 'instructor')
            ->get(route('schools.instructor.dashboard', $school));
        
        $response->assertStatus(200);
    });
});
