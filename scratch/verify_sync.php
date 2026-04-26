<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$requests = \App\Models\EnrollmentRequest::whereIn('status', ['rejected', 'cancelled'])->get();
foreach ($requests as $r) {
    echo "ID: {$r->id} | Status: {$r->status} | Payment: {$r->payment_status} | Identity: " . ($r->learner ? $r->learner->student_license_status : 'N/A') . PHP_EOL;
}
