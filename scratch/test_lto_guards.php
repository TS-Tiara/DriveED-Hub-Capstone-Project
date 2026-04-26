<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\EnrollmentRequest;
use App\Models\SessionCompletion;
use App\Models\School;

use App\Models\Instructor;

$school = School::where('slug', 'drived-hub')->first();
$instructor = Instructor::where('school_id', $school->id)->first();
if (!$instructor) {
    die("Instructor not found\n");
}

$student = Student::where('email', 'perfect@example.com')->first();
$enrollment = EnrollmentRequest::where('learner_id', $student->id)->where('status', 'approved')->first();

if (!$enrollment) {
    die("Enrollment not found\n");
}

// Clear existing sessions
SessionCompletion::where('enrollment_id', $enrollment->id)->delete();

echo "Case 1: Log 15 hours on 1 day\n";
SessionCompletion::create([
    'enrollment_id' => $enrollment->id,
    'school_id' => $enrollment->school_id,
    'session_date' => date('Y-m-d'),
    'hours_completed' => 15,
    'session_type' => 'theoretical',
    'status' => 'completed',
    'instructor_id' => $instructor->id,
]);

$validation = \App\Support\EnrollmentValidator::canMarkTheoreticalPassed($enrollment);
echo "Allowed: " . ($validation['allowed'] ? 'YES' : 'NO') . "\n";
echo "Message: " . $validation['message'] . "\n\n";

echo "Case 2: Log 15 hours over 3 days\n";
SessionCompletion::where('enrollment_id', $enrollment->id)->delete();
for ($i = 0; $i < 3; $i++) {
    SessionCompletion::create([
        'enrollment_id' => $enrollment->id,
        'school_id' => $enrollment->school_id,
        'session_date' => date('Y-m-d', strtotime("-$i day")),
        'hours_completed' => 5,
        'session_type' => 'theoretical',
        'status' => 'completed',
        'instructor_id' => $instructor->id,
    ]);
}

$validation = \App\Support\EnrollmentValidator::canMarkTheoreticalPassed($enrollment);
echo "Allowed: " . ($validation['allowed'] ? 'YES' : 'NO') . "\n";
echo "Message: " . $validation['message'] . "\n";
