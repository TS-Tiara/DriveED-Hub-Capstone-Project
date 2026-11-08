<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{EnrollmentRequest, Payment, School, Student};
use Illuminate\Support\Facades\{DB, Storage};

$emails = ['qa-nov7-student@example.invalid', 'qa-nov7-guest@example.invalid'];
$path = 'receipts/qa-nov7-browser.png';
if (($argv[1] ?? '') === 'cleanup') {
    DB::transaction(function () use ($emails) {
        $ids = Student::whereIn('email', $emails)->pluck('id');
        Payment::whereIn('payer_user_id', $ids)->delete();
        EnrollmentRequest::whereIn('learner_id', $ids)->delete();
        Student::whereIn('id', $ids)->delete();
    });
    Storage::disk('local')->delete($path);
    echo "Removed isolated Nov 7 browser fixtures.\n";
    exit;
}
if (Student::whereIn('email', $emails)->exists() || Storage::disk('local')->exists($path)) {
    throw new RuntimeException('Nov 7 fixture already exists; run cleanup first.');
}
$fixture = DB::transaction(function () use ($emails, $path) {
    $school = School::where('slug', 'drived-hub')->firstOrFail();
    $source = EnrollmentRequest::where('school_id', $school->id)->whereNotNull('branch_id')->firstOrFail();
    $fixture = [];
    foreach (['student', 'guest'] as $index => $role) {
        $user = Student::create([
            'school_id' => $school->id, 'branch_id' => $source->branch_id,
            'name' => 'Nov7 QA '.ucfirst($role), 'email' => $emails[$index], 'password' => 'Nov7-Local-Test-123!',
            'role' => $role, 'status' => 'active',
            'student_license_path' => $path, 'student_license_status' => 'verified',
            'student_license_mime_type' => 'image/png', 'must_reset_password' => false,
        ]);
        $user->email_verified_at = now();
        $user->save();
        $enrollment = EnrollmentRequest::create([
            'school_id' => $school->id, 'branch_id' => $source->branch_id, 'learner_id' => $user->id,
            'course_id' => $source->course_id, 'package_id' => $source->package_id,
            'price' => 100, 'status' => 'pending', 'license_status' => 'verified',
            'payment_status' => 'pending', 'payment_method' => 'gcash',
            'payment_reference' => '8960000000000', 'payment_proof_path' => $path,
            'credentials_file_path' => $path,
        ]);
        for ($i = 0; $i < 12; $i++) {
            Payment::create([
                'school_id' => $school->id, 'branch_id' => $source->branch_id,
                'payer_user_id' => $user->id, 'enrollment_request_id' => $enrollment->id,
                'amount' => 100, 'paid_on' => now(), 'method' => 'gcash',
                'reference' => (string) (8960000000000 + $index * 100 + $i),
                'proof_of_payment_path' => $path, 'status' => $i % 2 === 0 ? 'approved' : 'pending',
                'snap_qr_path' => app(\App\Services\ReceiptStorageService::class)->getUrl($path, $school),
            ]);
        }
        $fixture[$role] = $user->id;
    }
    return $fixture;
});
$image = imagecreatetruecolor(600, 360);
$white = imagecolorallocate($image, 255, 255, 255);
$ink = imagecolorallocate($image, 31, 41, 55);
imagefill($image, 0, 0, $white);
imagerectangle($image, 12, 12, 587, 347, $ink);
imagestring($image, 5, 35, 40, 'NOV 7 - TEST DOCUMENT', $ink);
imagestring($image, 4, 35, 100, 'Sample upload for receipt and license viewer checks', $ink);
imagestring($image, 4, 35, 150, 'Reference: QA-NOV7', $ink);
imagestring($image, 4, 35, 190, 'Amount: PHP 100.00', $ink);
imagestring($image, 4, 35, 275, 'Temporary fixture - removed after verification', $ink);
ob_start();
imagepng($image);
Storage::disk('local')->put($path, ob_get_clean());
imagedestroy($image);
$fixture['receipt_url'] = app(\App\Services\ReceiptStorageService::class)->getUrl($path, School::where('slug', 'drived-hub')->firstOrFail());
echo json_encode($fixture);
