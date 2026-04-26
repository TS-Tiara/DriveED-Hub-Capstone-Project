<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\School;
use App\Models\Course;
use App\Models\Invitation;

$school = School::where('slug', 'drived-hub')->first();
$course = Course::where('school_id', $school->id)->where('course_type', 'theoretical')->first();

if (!$school || !$course) {
    die("School or Course not found\n");
}

$invitation = Invitation::create([
    'school_id' => $school->id,
    'email' => 'perfect@example.com',
    'role' => 'student',
    'token' => 'perfect-token-123',
    'payload' => [
        'name' => 'Perfect Test',
        'course_id' => $course->id,
        'contact' => '+639123456789',
        'address' => 'Perfect City'
    ]
]);

echo "Token: " . $invitation->token . "\n";
