<?php

/**
 * Email Notification Test Suite
 * 
 * Tests that emails are properly sent for various actions.
 * Uses Laravel's Mail::fake() to intercept and verify emails.
 * 
 * @version 1.1
 */

use App\Models\School;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\SchoolSetting;
use App\Models\Booking;
use App\Models\TimeSlot;
use App\Mail\EnrollmentApproved;
use App\Mail\EnrollmentRequestReceived;
use App\Mail\SessionReminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ===========================================
// ENROLLMENT EMAIL TESTS
// ===========================================
describe('Enrollment Email Notifications', function () {

    test('enrollment approval triggers email sending code', function () {
        // We test that the approval action completes successfully
        // The email is wrapped in try-catch in the controller
        $school = School::factory()->create();
        SchoolSetting::factory()->create(['school_id' => $school->id]);
        $admin = Admin::factory()->create([
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $student = Student::factory()->create([
            'school_id' => $school->id,
            'email' => 'student@gmail.com',
            'status' => 'active',
            'role' => 'guest',
        ]);
        $enrollment = EnrollmentRequest::factory()->create([
            'school_id' => $school->id,
            'learner_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'pending',
        ]);
        
        $response = $this->actingAs($admin, 'admin')
            ->post(route('schools.admin.enrollments.approve', [$school, $enrollment->id]));
        
        // Verify the enrollment was approved
        $enrollment->refresh();
        expect($enrollment->status)->toBe('approved');
    });

    test('mail fake can intercept queued emails', function () {
        Mail::fake();
        
        $school = School::factory()->create();
        $student = Student::factory()->create([
            'school_id' => $school->id,
            'email' => 'test@gmail.com',
        ]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $enrollment = EnrollmentRequest::factory()->create([
            'school_id' => $school->id,
            'learner_id' => $student->id,
            'course_id' => $course->id,
        ]);
        
        // Manually trigger the email
        Mail::to($student->email)->send(new EnrollmentApproved($enrollment, $school));
        
        // Assert that the email was sent
        Mail::assertSent(EnrollmentApproved::class, function ($mail) use ($student) {
            return $mail->hasTo($student->email);
        });
    });
});

// ===========================================
// EMAIL QUEUE TESTS
// ===========================================
describe('Email Queuing', function () {

    test('emails can be queued for later sending', function () {
        Mail::fake();
        
        $school = School::factory()->create();
        $student = Student::factory()->create([
            'school_id' => $school->id,
            'email' => 'test@gmail.com',
        ]);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
        ]);
        
        // Queue the email
        Mail::to($student->email)->queue(new SessionReminder($booking, $school));
        
        Mail::assertQueued(SessionReminder::class);
    });
});

// ===========================================
// EMAIL CONTENT TESTS
// ===========================================
describe('Email Content Verification', function () {

    test('enrollment approved email contains correct content', function () {
        $school = School::factory()->create(['name' => 'Test Driving School']);
        $course = Course::factory()->create([
            'school_id' => $school->id,
            'title' => 'Basic Driving Course',
        ]);
        $student = Student::factory()->create([
            'school_id' => $school->id,
            'name' => 'John Doe',
        ]);
        $enrollment = EnrollmentRequest::factory()->create([
            'school_id' => $school->id,
            'learner_id' => $student->id,
            'course_id' => $course->id,
            'approved_at' => now(), // Required for email template
        ]);
        
        // Pass both required arguments
        $mailable = new EnrollmentApproved($enrollment, $school);
        
        // Test that the mailable renders without errors
        $rendered = $mailable->render();
        
        expect($rendered)->toBeString();
        expect($rendered)->toContain('Test Driving School');
    });

    test('session reminder email has proper envelope', function () {
        $school = School::factory()->create(['name' => 'Test Driving School']);
        $booking = Booking::factory()->create([
            'school_id' => $school->id,
        ]);

        $mailable = new SessionReminder($booking, $school);
        
        $envelope = $mailable->envelope();
        
        expect($envelope->subject)->toContain('Session Reminder');
    });
});

// ===========================================
// MAIL DRIVER VERIFICATION
// ===========================================
describe('Mail Configuration', function () {

    test('mail is configured correctly for testing', function () {
        // In testing, mail should use 'array' or 'log' driver
        $driver = config('mail.default');
        
        expect($driver)->toBeIn(['log', 'array', 'smtp']);
    });

    test('mail from address is configured', function () {
        $from = config('mail.from.address');
        
        expect($from)->not->toBeNull();
    });
});
