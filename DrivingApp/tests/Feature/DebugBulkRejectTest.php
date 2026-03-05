<?php

use App\Models\School;
use App\Models\Admin;
use App\Models\EnrollmentRequest;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('debugging bulk reject', function () {
    $school = School::factory()->create(['slug' => 'test-school']);
    $admin = Admin::factory()->create([
        'school_id' => $school->id,
        'role' => 'school_admin',
        'is_active' => true,
    ]);
    $course = Course::factory()->create(['school_id' => $school->id]);

    $student = Student::factory()->create(['school_id' => $school->id, 'role' => 'guest']);

    $enrollment = EnrollmentRequest::factory()->create([
        'school_id' => $school->id,
        'learner_id' => $student->id,
        'course_id' => $course->id,
        'status' => 'pending',
    ]);

    $url = route('schools.admin.enrollments.bulkReject', ['school' => $school->slug]);

    dump("Middlewares: ", Route::getRoutes()->getByName('schools.admin.enrollments.bulkReject')->gatherMiddleware());
    dump("Target URL: " . $url);

    $response = $this->actingAs($admin, 'admin')
        ->post($url, [
        'enrollment_ids' => [$enrollment->id],
        'remarks' => 'Test rejection',
    ]);

    dump("Status: " . $response->status());
    dump("Location: " . $response->headers->get('Location'));
    if (session()->has('errors')) {
        dump("Validation Errors: ", session()->get('errors')->all());
    }
    dump("Session: ", session()->all());

    if ($response->status() !== 302) {
        dump("Content: " . substr($response->content(), 0, 500));
    }

    $enrollment->refresh();
    dump("Final Status: " . $enrollment->status);

    expect($enrollment->status)->toBe('rejected');
});
