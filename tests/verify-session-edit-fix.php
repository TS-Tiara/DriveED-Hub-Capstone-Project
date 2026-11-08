<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\School;
use App\Models\Instructor;
use App\Models\SessionCompletion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

if (DB::connection()->getDatabaseName() !== 'drivingapp_regression_20261108') {
    throw new RuntimeException('Session edit regression requires the isolated audit database.');
}
DB::beginTransaction();
register_shutdown_function(function () {
    while (DB::transactionLevel() > 0) DB::rollBack();
});

echo "=== VERIFYING INSTRUCTOR SESSION EDIT & UPDATE ===\n";

$school = School::where('slug', 'drived-hub')->firstOrFail();
$instructor = Instructor::where('school_id', $school->id)->firstOrFail();

// Find a session owned by this instructor or create one
$session = SessionCompletion::where('instructor_id', $instructor->id)->first();
if (!$session) {
    $enrollment = \App\Models\EnrollmentRequest::where('school_id', $school->id)->firstOrFail();
    $session = SessionCompletion::create([
        'school_id' => $school->id,
        'enrollment_id' => $enrollment->id,
        'instructor_id' => $instructor->id,
        'session_type' => 'practical',
        'session_date' => now()->toDateString(),
        'session_time' => '10:00:00',
        'hours_completed' => 2.0,
        'status' => 'completed',
        'notes' => 'Original test note',
    ]);
}

echo "Found session ID {$session->id} belonging to instructor {$instructor->name} ({$instructor->email})\n";

// Authenticate as instructor
Auth::guard('instructor')->setUser($instructor);

// 1. Verify Edit view renders successfully
$controller = app(\App\Http\Controllers\SessionCompletionController::class);
$editView = $controller->edit(new Request(), $school, $session->id);
assert($editView->name() === 'school.instructor.sessions.edit', 'View name must be school.instructor.sessions.edit');
$renderedHtml = $editView->render();
assert(str_contains($renderedHtml, 'Edit Session'), 'Page must contain Edit Session');
assert(str_contains($renderedHtml, 'name="_method" value="PUT"'), 'Form must have PUT method');
assert(str_contains($renderedHtml, 'data-no-ajax="1"'), 'Form must have data-no-ajax');
assert(str_contains($renderedHtml, 'class="native-form"'), 'Form must have native-form');
assert(str_contains($renderedHtml, 'name="enrollment_id"'), 'Form must have hidden enrollment_id');
assert(str_contains($renderedHtml, 'name="session_type"'), 'Form must have hidden session_type');
echo "[PASS] 1. Edit view renders correctly with proper form attributes and method.\n";

// 2. Verify update via Controller with StoreSessionCompletionRequest
$newDate = now()->toDateString();
$newTime = '09:00';
$newHours = min(2.5, (float) $session->hours_completed);
$availability = app(\App\Services\SchedulingConflictService::class);
$candidate = now()->subDay();
do {
    $newDate = $candidate->toDateString();
    $candidate->subDay();
    $available = $availability->checkInstructorAvailability(
        $instructor->id, $newDate, $newTime,
        \Carbon\Carbon::parse($newTime)->addMinutes((int) round($newHours * 60))->format('H:i'),
        $session->id
    );
} while (!$available['available']);
$newNotes = 'Updated notes by verification script at ' . now()->toDateTimeString();

$updateRequest = \App\Http\Requests\StoreSessionCompletionRequest::create(
    "/{$school->slug}/instructor/sessions/{$session->id}",
    'POST',
    [
        '_method' => 'PUT',
        'enrollment_id' => $session->enrollment_id,
        'session_type' => $session->session_type,
        'session_date' => $newDate,
        'session_time' => $newTime,
        'hours_completed' => $newHours,
        'notes' => $newNotes,
    ]
);
$updateRequest->setUserResolver(fn () => $instructor);
$updateRequest->setRouteResolver(function () use ($school, $session) {
    $route = new \Illuminate\Routing\Route('PUT', '/{school}/instructor/sessions/{sessionCompletion}', []);
    $route->bind(request());
    $route->setParameter('school', $school);
    $route->setParameter('sessionCompletion', $session->id);
    return $route;
});

// Validate request
$validator = validator(
    $updateRequest->all(),
    $updateRequest->rules(),
    $updateRequest->messages(),
    $updateRequest->attributes()
);
assert(!$validator->fails(), 'Validation should pass: ' . json_encode($validator->errors()->all()));
echo "[PASS] 2. Validation passes for update payload.\n";

$response = $controller->update($school, $updateRequest, $session->id);
echo "Response Target URL: " . $response->getTargetUrl() . "\n";
if (session('error')) {
    echo "Session Error: " . session('error') . "\n";
}
assert($response->isRedirection(), 'Response should be a redirect');
assert(str_contains($response->getTargetUrl(), "/sessions/{$session->id}"), 'Redirect target should be session show: ' . $response->getTargetUrl());
echo "[PASS] 3. Controller update method executes and redirects to session details.\n";

// Check database update
$session->refresh();
assert((float) $session->hours_completed === $newHours, "Hours completed should be {$newHours}, got {$session->hours_completed}");
assert($session->notes === $newNotes, "Notes should be updated");
echo "[PASS] 4. Database records verified: hours_completed={$session->hours_completed}, notes='{$session->notes}'\n";

echo "\nALL TESTS PASSED SUCCESSFULLY!\n";
