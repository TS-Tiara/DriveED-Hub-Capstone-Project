<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{Admin, Booking, Course, CourseModule, EnrollmentRequest, GCashSetting, Instructor, ModuleLesson, Payment, Progress, Question, School, SessionCompletion, Student, SystemLog, Vehicle, VehicleImage};
use Illuminate\Support\Facades\{DB, Schema, Storage};

if (DB::connection()->getDatabaseName() !== 'drivingapp_regression_20261108') {
    throw new RuntimeException('This fixture may run only in the isolated regression database.');
}
$school = School::where('slug', 'drived-hub')->firstOrFail();
$users = [
    'system-admin' => Admin::where('role','system_admin')->firstOrFail(),
    'school-admin' => Admin::where('email','admin1@driveedhub.test')->firstOrFail(),
    'branch-secretary' => Admin::where('school_id',$school->id)->where('role','branch_secretary')->whereNotNull('branch_id')->firstOrFail(),
    'instructor' => Instructor::where('email','instructor1@driveedhub.test')->firstOrFail(),
    'student' => Student::where('email','student1@driveedhub.test')->firstOrFail(),
    'guest' => Student::where('email','guest1@driveedhub.test')->firstOrFail(),
];
foreach ($users as $role => $user) {
    $values = ['password'=>'Audit-Local-123!', 'must_reset_password'=>false, 'failed_login_attempts'=>0, 'locked_until'=>null, 'is_active'=>true, 'status'=>'active'];
    if ($user instanceof Student) $values['email_verified_at'] = now();
    $user->forceFill(array_intersect_key($values,array_flip(Schema::getColumnListing($user->getTable()))))->save();
}
$studentEnrollment = EnrollmentRequest::where('learner_id',$users['student']->id)->where('status','approved')->first()
    ?? EnrollmentRequest::where('learner_id',$users['student']->id)->firstOrFail();
$course = Course::findOrFail($studentEnrollment->course_id);
$module = CourseModule::firstOrCreate(['school_id'=>$school->id,'course_id'=>$course->id,'title'=>'QA Regression Lesson Module'],['module_type'=>'reading','sort_order'=>998]);
$lesson = ModuleLesson::firstOrCreate(['school_id'=>$school->id,'module_id'=>$module->id,'title'=>'QA Regression Lesson'],['content'=>'<p>Temporary isolated regression lesson.</p>','sort_order'=>1]);
$assessment = CourseModule::firstOrCreate(['school_id'=>$school->id,'course_id'=>$course->id,'title'=>'QA Regression Assessment'],['module_type'=>'assessment','sort_order'=>999]);
$otherSchool = School::firstOrCreate(['slug'=>'qa-audit-boundary'],['name'=>'QA Access Boundary','status'=>'active','timezone'=>'Asia/Manila']);
$otherStudent = Student::firstOrCreate(['email'=>'qa-audit-boundary@example.invalid'],['school_id'=>$otherSchool->id,'name'=>'QA Boundary Student','password'=>'Audit-Local-123!','role'=>'student','status'=>'active']);
$activeEnrollment=EnrollmentRequest::where('school_id',$school->id)->where('status','approved')->whereHas('learner',fn($q)=>$q->where('role','student'))->firstOrFail();
$activeStudent=$activeEnrollment->learner;
$activeStudent->forceFill(['password'=>'Audit-Local-123!','must_reset_password'=>false,'status'=>'active','email_verified_at'=>now(),'failed_login_attempts'=>0,'locked_until'=>null])->save();
$activeModule=CourseModule::firstOrCreate(['school_id'=>$school->id,'course_id'=>$activeEnrollment->course_id,'title'=>'QA Active Student Lesson Module'],['module_type'=>'reading','sort_order'=>998]);
$activeLesson=ModuleLesson::firstOrCreate(['school_id'=>$school->id,'module_id'=>$activeModule->id,'title'=>'QA Active Student Lesson'],['content'=>'<p>Temporary active-enrollment lesson.</p>','sort_order'=>1]);
$activeAssessment=CourseModule::firstOrCreate(['school_id'=>$school->id,'course_id'=>$activeEnrollment->course_id,'title'=>'QA Active Student Assessment'],['module_type'=>'assessment','sort_order'=>999]);
$activeActor=['id'=>$activeStudent->id,'email'=>$activeStudent->email,'course'=>$activeEnrollment->course_id,'module'=>$activeModule->id,'lesson'=>$activeLesson->id,'assessment'=>$activeAssessment->id];
$receipts=[];
$writeReceipt = function (string $path, string $owner): void {
    $image=imagecreatetruecolor(680,380);
    $paper=imagecolorallocate($image,255,255,255); $ink=imagecolorallocate($image,31,41,55);
    imagefill($image,0,0,$paper); imagerectangle($image,16,16,663,363,$ink);
    imagestring($image,5,35,40,'QA PAYMENT RECEIPT - '.$owner,$ink);
    imagestring($image,4,35,100,'Owner: '.$owner,$ink);
    imagestring($image,4,35,145,'Amount: PHP 100.00',$ink);
    imagestring($image,4,35,200,'Temporary isolated test document',$ink);
    imagestring($image,4,35,290,'No existing uploads were replaced.',$ink);
    ob_start(); imagepng($image); Storage::disk('local')->put($path,ob_get_clean()); imagedestroy($image);
};
foreach (['student','guest'] as $receiptRole) {
    $path='receipts/qa-audit-'.$receiptRole.'.png';
    $writeReceipt($path,strtoupper($receiptRole));
    $payment=Payment::where('payer_user_id',$users[$receiptRole]->id)->first();
    if($payment) $payment->update(['proof_of_payment_path'=>$path]);
    $receipts[$receiptRole]=['path'=>$path,'payment'=>$payment?->id,'owner'=>$users[$receiptRole]->id,'branch_id'=>$payment?->branch_id];
}
$foreignEnrollment=EnrollmentRequest::where('school_id',$school->id)->where('branch_id','!=',$users['branch-secretary']->branch_id)->firstOrFail();
$foreignPath='receipts/qa-audit-other-branch.png';
$writeReceipt($foreignPath,'OTHER BRANCH');
$foreignEnrollment->update(['payment_proof_path'=>$foreignPath]);
$receipts['otherBranch']=['path'=>$foreignPath,'enrollment'=>$foreignEnrollment->id,'branch_id'=>$foreignEnrollment->branch_id];
$roles=[];
foreach ($users as $role=>$user) {
    $enrollmentQuery = EnrollmentRequest::where('school_id',$school->id);
    $bookingQuery = Booking::where('school_id',$school->id);
    $paymentQuery = Payment::where('school_id',$school->id);
    $sessionQuery = SessionCompletion::where('school_id',$school->id);
    if (in_array($role,['student','guest'],true)) {
        $enrollmentQuery->where('learner_id',$user->id);
        $bookingQuery->where('student_id',$user->id);
        $paymentQuery->where('payer_user_id',$user->id);
        $sessionQuery->whereHas('enrollment',fn($q)=>$q->where('learner_id',$user->id));
    } elseif ($role==='instructor') {
        $bookingQuery->where('instructor_id',$user->id);
        $sessionQuery->where('instructor_id',$user->id);
    } elseif($role==='branch-secretary') {
        $enrollmentQuery->where('branch_id',$user->branch_id);
        $bookingQuery->where('branch_id',$user->branch_id);
        $paymentQuery->where('branch_id',$user->branch_id);
        $sessionQuery->whereHas('enrollment',fn($q)=>$q->where('branch_id',$user->branch_id));
    }
    $enrollment=$enrollmentQuery->first(); $booking=$bookingQuery->first(); $payment=$paymentQuery->first(); $session=$sessionQuery->first();
    $roles[$role]=[
        'id'=>$user->id,'email'=>$user->email,'branch_id'=>$user->branch_id,
        'parameters'=>[
            'school'=>$school->slug,'course'=>$course->id,'module'=>$module->id,'lesson'=>$lesson->id,
            'enrollmentRequest'=>$enrollment?->id,'enrollment'=>$enrollment?->id,'enrollment_request_id'=>$enrollment?->id,
            'student'=>in_array($role,['student','guest'],true)?$user->id:($enrollment?->learner_id ?? $users['student']->id),
            'payment'=>$payment?->id,'booking'=>$booking?->id,'sessionCompletion'=>$session?->id,
            'format'=>'pdf','role'=>'student','id'=>$users['student']->id,
            'progress'=>Progress::where('school_id',$school->id)->first()?->id,
            'question'=>Question::where('school_id',$school->id)->first()?->id,
            'gcashSetting'=>GCashSetting::where('school_id',$school->id)->where('is_active',true)->first()?->id,
            'image'=>VehicleImage::whereHas('vehicle',fn($q)=>$q->where('school_id',$school->id))->first()?->id,
            'log'=>SystemLog::first()?->id,
        ],
    ];
}
echo json_encode(['database'=>DB::connection()->getDatabaseName(),'school'=>$school->slug,'roles'=>$roles,'activeStudent'=>$activeActor,'receipts'=>$receipts,'assessment'=>$assessment->id,'otherSchool'=>$otherSchool->slug,'otherStudent'=>$otherStudent->id,'counts'=>['courses'=>Course::count(),'enrollments'=>EnrollmentRequest::count(),'payments'=>Payment::count(),'sessions'=>SessionCompletion::count(),'bookings'=>Booking::count(),'modules'=>CourseModule::count(),'lessons'=>ModuleLesson::count()]],JSON_PRETTY_PRINT);
