<?php

use App\Mail\EnrollmentApproved;
use App\Mail\EnrollmentRejected;
use App\Mail\OtpVerificationCode;
use App\Mail\PasswordResetRequested;
use App\Mail\SessionReminder;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\Notification;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function createStudentRecord(School $school, array $overrides = []): Student
{
    return Student::forceCreate(array_merge([
        'school_id' => $school->id,
        'name' => 'Test Student ' . uniqid(),
        'email' => 'student_' . uniqid() . '@example.com',
        'password' => bcrypt('Password1!'),
        'contact' => '09123456789',
        'address' => 'Sample Address',
        'location' => 'Sample Location',
        'status' => 'active',
        'role' => 'student',
    ], $overrides));
}

function createEnrollmentContext(string $status = 'pending'): array
{
    $school = School::factory()->create();
    SchoolSetting::factory()->create(['school_id' => $school->id]);

    $admin = Admin::factory()->create([
        'school_id' => $school->id,
        'is_active' => true,
        'role' => 'school_admin',
    ]);

    $studentRole = $status === 'pending' ? 'guest' : 'student';

    $student = createStudentRecord($school, [
        'role' => $studentRole,
    ]);

    $course = Course::factory()->create([
        'school_id' => $school->id,
        'status' => 'active',
    ]);

    $enrollment = EnrollmentRequest::factory()->create([
        'school_id' => $school->id,
        'learner_id' => $student->id,
        'course_id' => $course->id,
        'status' => $status,
    ]);

    return [$school, $admin, $student, $course, $enrollment];
}

test('student login verification flow sends OTP mailable', function () {
    Mail::fake();

    $school = School::factory()->create();
    $student = createStudentRecord($school, [
        'role' => 'student',
        'email_verified_at' => null,
        'password' => bcrypt('Password1!'),
    ]);

    $this->post("/{$school->slug}/login", [
        'email' => $student->email,
        'password' => 'Password1!',
    ])->assertRedirect(route('schools.verification.show', $school));

    Mail::assertSent(OtpVerificationCode::class, 1);
});

test('guest registration and resend verification use OTP mailable', function () {
    Mail::fake();

    $school = School::factory()->create();

    $this->post(route('schools.registration.submit', $school), [
        'name' => 'Guest User',
        'email' => 'guest-' . uniqid() . '@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
        'contact' => '09123456789',
        'accept_privacy' => '1',
        'accept_terms' => '1',
    ])->assertRedirect(route('schools.verification.show', $school));

    Mail::assertSent(OtpVerificationCode::class, 1);

    $student = Student::query()->latest('id')->firstOrFail();

    $this->withSession([
        'verification_email' => $student->email,
        'school_slug' => $school->slug,
    ])->post(route('schools.verification.resend', $school))
        ->assertSessionHas('success');

    Mail::assertSent(OtpVerificationCode::class, 2);
});

test('password reset request sends password reset mailable', function () {
    Mail::fake();

    $school = School::factory()->create();
    $student = createStudentRecord($school, [
        'email' => 'reset-' . uniqid() . '@example.com',
    ]);

    $this->post(route('schools.password.email', $school), [
        'email' => $student->email,
        'user_type' => 'student',
    ])->assertSessionHas('success');

    Mail::assertSent(PasswordResetRequested::class, 1);
});

test('approve transition sends one email and one in-app notification', function () {
    Mail::fake();
    [$school, $admin, $student, $course, $enrollment] = createEnrollmentContext('pending');

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->actingAs($admin, 'admin')
        ->post(route('schools.admin.enrollments.approve', [$school, $enrollment]))
        ->assertRedirect();

    Mail::assertSent(EnrollmentApproved::class, 1);

    expect(Notification::query()
        ->where('type', 'enrollment_approved')
        ->where('notifiable_id', $student->id)
        ->count())->toBe(1);
});

test('reject transition sends one email and one in-app notification', function () {
    Mail::fake();
    [$school, $admin, $student, $course, $enrollment] = createEnrollmentContext('pending');

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->actingAs($admin, 'admin')
        ->post(route('schools.admin.enrollments.reject', [$school, $enrollment]), [
            'remarks' => 'Missing requirements',
        ])
        ->assertRedirect();

    Mail::assertSent(EnrollmentRejected::class, 1);

    expect(Notification::query()
        ->where('type', 'enrollment_rejected')
        ->where('notifiable_id', $student->id)
        ->count())->toBe(1);
});

test('payment status transition sends in-app notification without lifecycle email by default policy', function () {
    Mail::fake();
    config(['notification_policy.enable_lifecycle_transition_emails' => false]);
    [$school, $admin, $student, $course, $enrollment] = createEnrollmentContext('approved');

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->actingAs($admin, 'admin')
        ->post(route('schools.admin.enrollments.paymentStatus', [$school, $enrollment]), [
            'payment_status' => 'paid',
        ])
        ->assertRedirect();

    Mail::assertNothingSent();

    expect(Notification::query()
        ->where('type', 'payment_status_updated')
        ->where('notifiable_id', $student->id)
        ->count())->toBe(1);
});

test('completion cancellation theoretical and license transitions send in-app notifications without lifecycle email by default policy', function () {
    Mail::fake();
    config(['notification_policy.enable_lifecycle_transition_emails' => false]);

    // Complete
    [$school1, $admin1, $student1, $course1, $enrollment1] = createEnrollmentContext('approved');

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->actingAs($admin1, 'admin')
        ->post(route('schools.admin.enrollments.complete', [$school1, $enrollment1]))
        ->assertRedirect();

    // Cancel
    [$school2, $admin2, $student2, $course2, $enrollment2] = createEnrollmentContext('approved');

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->actingAs($admin2, 'admin')
        ->post(route('schools.admin.enrollments.cancel', [$school2, $enrollment2]), [
            'remarks' => 'Requested by student',
        ])
        ->assertRedirect();

    // Theoretical passed
    [$school3, $admin3, $student3, $course3, $enrollment3] = createEnrollmentContext('approved');

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->actingAs($admin3, 'admin')
        ->post(route('schools.admin.enrollments.theoreticalPassed', [$school3, $enrollment3]), [
            'notes' => 'Passed exam',
        ])
        ->assertRedirect();

    // License verify + reject
    $school4 = School::factory()->create();
    SchoolSetting::factory()->create(['school_id' => $school4->id]);
    $admin4 = Admin::factory()->create([
        'school_id' => $school4->id,
        'role' => 'school_admin',
    ]);

    $student4 = createStudentRecord($school4, [
        'role' => 'guest',
    ]);
    $student4->forceFill(['student_license_status' => 'pending'])->save();

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->actingAs($admin4, 'admin')
        ->post(route('schools.admin.enrollments.verifyLicense', [$school4, $student4]))
        ->assertRedirect();

    $student5 = createStudentRecord($school4, [
        'role' => 'guest',
    ]);
    $student5->forceFill(['student_license_status' => 'pending'])->save();

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->actingAs($admin4, 'admin')
        ->post(route('schools.admin.enrollments.rejectLicense', [$school4, $student5]), [
            'rejection_reason' => 'Image unreadable',
        ])
        ->assertRedirect();

    Mail::assertNothingSent();

    expect(Notification::query()->where('type', 'enrollment_completed')->count())->toBe(1);
    expect(Notification::query()->where('type', 'enrollment_cancelled')->count())->toBe(1);
    expect(Notification::query()->where('type', 'theoretical_passed')->count())->toBe(1);
    expect(Notification::query()->where('type', 'license_verified')->count())->toBe(1);
    expect(Notification::query()->where('type', 'license_rejected')->count())->toBe(1);
});

test('session reminder command follows policy and sends email plus in-app reminders', function () {
    Mail::fake();

    $school = School::factory()->create();
    SchoolSetting::factory()->create([
        'school_id' => $school->id,
        'enable_booking_queue' => true,
    ]);

    $student = createStudentRecord($school, [
        'role' => 'student',
    ]);

    $booking = Booking::factory()->create([
        'school_id' => $school->id,
        'student_id' => $student->id,
        'status' => 'confirmed',
        'scheduled_at' => Carbon::now()->addHours(2),
    ]);

    Artisan::call('reminders:sessions', ['--hours' => 24]);

    Mail::assertSent(SessionReminder::class, 1);
    expect(Notification::query()->where('type', 'session_reminder')->count())->toBe(2);
});

test('confirm queued bookings command applies status changes without notifications', function () {
    Mail::fake();

    $school = School::factory()->create();
    SchoolSetting::factory()->create([
        'school_id' => $school->id,
        'enable_booking_queue' => true,
        'booking_queue_days' => 3,
    ]);

    $student = createStudentRecord($school, ['role' => 'student']);

    $booking = Booking::factory()->create([
        'school_id' => $school->id,
        'student_id' => $student->id,
        'status' => 'pending',
        'created_at' => Carbon::now()->subDays(4),
    ]);

    Artisan::call('bookings:confirm-queued');

    expect($booking->fresh()->status)->toBe('scheduled');
    expect(Notification::query()->count())->toBe(0);
    Mail::assertNothingSent();
});
