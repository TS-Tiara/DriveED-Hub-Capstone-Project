<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{Admin, EnrollmentRequest, Payment, School, Student};
use App\Http\Controllers\{AdminController, GuestController, PaymentController};
use App\Services\ReceiptStorageService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\{Auth, DB};

$checks = 0;
$check = function ($condition, $message) use (&$checks) {
    if (!$condition) throw new RuntimeException($message);
    echo "PASS: {$message}\n";
    $checks++;
};
$requestFor = function ($url) use ($app) {
    $request = Request::create($url);
    $app->instance('request', $request);
    Paginator::currentPageResolver(fn ($name = 'page') => max(1, (int) $request->query($name, 1)));
    return $request;
};

DB::beginTransaction();
try {
    $school = School::where('slug', 'drived-hub')->firstOrFail();
    $admin = Admin::where('school_id', $school->id)->where('role', 'school_admin')->firstOrFail();
    Auth::guard('admin')->setUser($admin);
    $source = Payment::where('school_id', $school->id)->whereNotNull('payer_user_id')->firstOrFail();
    for ($i = 0; $i < 22; $i++) {
        $payment = $source->replicate();
        $payment->reference = (string) (8900000000000 + $i);
        $payment->or_number = null;
        $payment->status = $i % 2 === 0 ? 'approved' : 'pending';
        $payment->save();
    }
    $controller = app(PaymentController::class);
    $first = $controller->index($requestFor('/'.$school->slug.'/admin/payments?page=1'), $school, app(ReceiptStorageService::class))->getData();
    $second = $controller->index($requestFor('/'.$school->slug.'/admin/payments?page=2'), $school, app(ReceiptStorageService::class))->getData();
    $check($first['stats'] === $second['stats'], 'Payment totals and counts remain identical on page 2');
    $check($second['stats']['approved_count'] > 0 && $second['stats']['pending_count'] > 0, 'Summary counts include records outside the visible page');
    $filtered = $controller->index($requestFor('/'.$school->slug.'/admin/payments?page=2&method='.$source->method), $school, app(ReceiptStorageService::class))->getData();
    $check(str_contains($filtered['payments']->url(1), 'method='), 'Payment pagination preserves filters');

    $student = Student::where('school_id', $school->id)->where('email', 'student1@driveedhub.test')->firstOrFail();
    $activity = app(AdminController::class)->userActivity($requestFor('/'.$school->slug.'/admin/user-management/student/'.$student->id.'/activity'), $school, 'student', $student->id);
    $html = $activity->render();
    $check(str_contains($html, 'Submitted License') && str_contains($html, 'Enrollment History') && str_contains($html, 'Payment Receipts and Transactions'), 'Student activity contains documents, enrollment history and transactions');
    $licenseUrl = route('schools.admin.enrollments.viewLicense', ['school' => $school, 'student' => $student]);
    $check(str_contains($html, e($licenseUrl)), 'Student license uses an authorized streaming route');
    $check(str_contains($html, 'data-image-url'), 'Student submitted license can open in the shared viewer');

    $otherSchool = School::where('id', '!=', $school->id)->first()
        ?? School::create(['name' => 'Nov7 Transactional QA', 'slug' => 'nov7-transactional-qa', 'status' => 'active']);
    if ($otherSchool) {
        try {
            app(AdminController::class)->userActivity(request(), $otherSchool, 'student', $student->id);
            throw new RuntimeException('Cross-school access unexpectedly allowed');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $check($error->getStatusCode() === 403, 'User activity rejects admins from another school');
        }
    }
    $secretary = Admin::where('school_id', $school->id)->where('role', 'branch_secretary')->whereNotNull('branch_id')->first();
    if ($secretary) {
        Auth::guard('admin')->setUser($secretary);
        $outside = Student::where('school_id', $school->id)->where(fn ($q) => $q->where('branch_id', '!=', $secretary->branch_id)->orWhereNull('branch_id'))->firstOrFail();
        try {
            app(AdminController::class)->userActivity(request(), $school, 'student', $outside->id);
            throw new RuntimeException('Cross-branch access unexpectedly allowed');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $error) {
            $check(true, 'User activity rejects students outside the secretary branch');
        }
        try {
            app(\App\Http\Controllers\EnrollmentRequestController::class)->viewLicense(request(), $school, $outside);
            throw new RuntimeException('Cross-branch license access unexpectedly allowed');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $check($error->getStatusCode() === 403, 'License file access enforces the secretary branch');
        }
    }
} finally {
    DB::rollBack();
}
echo "{$checks} checks passed; database changes rolled back.\n";
