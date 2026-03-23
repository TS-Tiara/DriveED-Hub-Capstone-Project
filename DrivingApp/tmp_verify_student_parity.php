<?php

use App\Models\School;
use App\Models\Student;
use App\Models\EnrollmentRequest;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * PHASE 5 STUDENT PARITY VERIFICATION
 * 
 * This script verifies:
 * 1. Students can see enrollment-linked payments in the index.
 * 2. Students can access enrollment-linked payments in the show view.
 */

$school = School::first();
// Find a student with an enrollment request in this school
$enrollment = EnrollmentRequest::where('school_id', $school->id)->first();
if (!$enrollment) {
    die("No enrollment request found for testing.\n");
}

$student = Student::find($enrollment->learner_id);
if (!$student) {
    die("Student for enrollment {$enrollment->id} not found.\n");
}

Auth::guard('student')->login($student);

echo "--- PHASE 5 STUDENT PARITY START ---\n";
echo "Testing as Student: {$student->name} (ID: {$student->id})\n";

// 1. Create a payment linked ONLY to enrollment
$payment = Payment::create([
    'school_id' => $school->id,
    'branch_id' => $enrollment->branch_id,
    'enrollment_request_id' => $enrollment->id,
    'booking_id' => null, // CRITICAL: No booking
    'amount' => 123.45,
    'method' => 'cash',
    'status' => 'completed',
    'paid_on' => now()->format('Y-m-d'),
]);

echo "Created Enrollment-Only Payment ID: {$payment->id}\n";

$controller = new \App\Http\Controllers\PaymentController();

// 2. Verify Index Visibility
$indexResponse = $controller->index($school);
$payments = $indexResponse->getData()['payments'];
$foundInIndex = false;
foreach ($payments as $p) {
    if ($p->id === $payment->id) {
        $foundInIndex = true;
        break;
    }
}
echo "Payment visible in student index: " . ($foundInIndex ? "YES ✅" : "NO ❌") . "\n";

// 3. Verify Show Access
try {
    $showResponse = $controller->show($school, $payment);
    echo "Payment show access: SUCCESS ✅\n";
} catch (\Exception $e) {
    echo "Payment show access: FAILED ❌ (" . $e->getMessage() . ")\n";
}

echo "\nCleaning up test payment...\n";
$payment->delete();

echo "--- PHASE 5 STUDENT PARITY COMPLETE ---\n";
