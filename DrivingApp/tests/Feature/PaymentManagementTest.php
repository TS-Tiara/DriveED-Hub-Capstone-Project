<?php

/**
 * Payment Management Test Suite
 * 
 * Tests for:
 * - Admin: View payments, create payments, view statistics
 * - Student: View their payments
 * 
 * @version 1.6
 */

use App\Models\School;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Instructor;
use App\Models\TimeSlot;
use App\Models\SchoolSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ===========================================
// ADMIN PAYMENT MANAGEMENT TESTS
// ===========================================
describe('Admin Payment Management', function () {
    
    test('admin can view payments index', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.payments.index', $school));
        
        $response->assertStatus(200);
    });

    test('admin can view single payment details via json', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $student = Student::factory()->create(['school_id' => $school->id, 'status' => 'active', 'role' => 'student']);
        $course = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $instructor = Instructor::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $timeSlot = TimeSlot::factory()->create(['school_id' => $school->id]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'scheduled',
        ]);
        $payment = Payment::factory()->create([
            'school_id' => $school->id,
            'booking_id' => $booking->id,
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('schools.admin.payments.show', [$school, $payment->id]));
        
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    });

    test('admin can create a new payment', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $student = Student::factory()->create(['school_id' => $school->id, 'status' => 'active', 'role' => 'student']);
        $course = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $instructor = Instructor::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $timeSlot = TimeSlot::factory()->create(['school_id' => $school->id]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'scheduled',
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('schools.admin.payments.store', $school), [
                'booking_id' => $booking->id,
                'amount' => 5000.00,
                'method' => 'cash',
                'status' => 'completed',
            ]);
        
        $response->assertStatus(201);
        expect(Payment::where('booking_id', $booking->id)->exists())->toBeTrue();
    });

    test('admin can update payment', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $student = Student::factory()->create(['school_id' => $school->id, 'status' => 'active', 'role' => 'student']);
        $course = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $instructor = Instructor::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $timeSlot = TimeSlot::factory()->create(['school_id' => $school->id]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'scheduled',
        ]);
        $payment = Payment::factory()->create([
            'school_id' => $school->id,
            'booking_id' => $booking->id,
            'status' => 'pending',
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->putJson(route('schools.admin.payments.update', [$school, $payment->id]), [
                'amount' => 6000.00,
                'method' => 'card',
                'status' => 'completed',
            ]);
        
        $response->assertStatus(200);
        $payment->refresh();
        expect($payment->status)->toBe('completed');
    });

    test('admin can delete payment', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $student = Student::factory()->create(['school_id' => $school->id, 'status' => 'active', 'role' => 'student']);
        $course = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $instructor = Instructor::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $timeSlot = TimeSlot::factory()->create(['school_id' => $school->id]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'scheduled',
        ]);
        $payment = Payment::factory()->create([
            'school_id' => $school->id,
            'booking_id' => $booking->id,
        ]);
        $paymentId = $payment->id;
        
        $response = $this->actingAs($admin, 'admin')
            ->deleteJson(route('schools.admin.payments.destroy', [$school, $payment->id]));
        
        $response->assertStatus(200);
        expect(Payment::find($paymentId))->toBeNull();
    });

    test('admin can view payment statistics', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->get(route('schools.admin.payments.statistics', $school));
        
        $response->assertStatus(200);
    });
});

// ===========================================
// STUDENT PAYMENT VIEW TESTS
// ===========================================
describe('Student Payment View', function () {
    
    test('student can view their payments list', function () {
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

    test('student can view their own payment via json', function () {
        $school = School::factory()->create();
        SchoolSetting::factory()->create(['school_id' => $school->id]);
        $student = Student::factory()->create([
            'school_id' => $school->id,
            'status' => 'active',
            'role' => 'student',
        ]);
        $course = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $instructor = Instructor::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $timeSlot = TimeSlot::factory()->create(['school_id' => $school->id]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'scheduled',
        ]);
        $payment = Payment::factory()->create([
            'school_id' => $school->id,
            'booking_id' => $booking->id,
        ]);
        
        $response = $this->actingAs($student, 'student')
            ->getJson(route('schools.student.payments.show', [$school, $payment->id]));
        
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    });
});
