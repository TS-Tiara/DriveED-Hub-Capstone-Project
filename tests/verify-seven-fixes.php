<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\{AdminController, EnrollmentRequestController, ModuleLessonController, PasswordResetController, ReportController};
use App\Mail\PasswordResetRequested;
use App\Models\{Admin, Course, CourseModule, EnrollmentRequest, Instructor, Payment, School, Student};
use App\Services\ReceiptAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Mail, View};
use Symfony\Component\HttpKernel\Exception\HttpException;

if (DB::connection()->getDatabaseName() !== 'drivingapp_regression_20261108') throw new RuntimeException('Use the isolated regression database.');
$checks=0;
$check=function(bool $condition,string $message)use(&$checks){if(!$condition)throw new RuntimeException($message);$checks++;echo "PASS: {$message}\n";};
$request=function(string $url,string $method='GET',array $body=[])use($app){
    $r=Request::create($url,$method,$body,[],[],['HTTP_ACCEPT'=>'application/json','HTTP_X_REQUESTED_WITH'=>'XMLHttpRequest','REMOTE_ADDR'=>'192.0.2.190']);
    $r->setLaravelSession(app('session')->driver());$app->instance('request',$r);return $r;
};
$deny=function(callable $callback,string $message)use($check){try{$callback();throw new RuntimeException('Expected rejection: '.$message);}catch(HttpException $e){$check($e->getStatusCode()===403,$message);}};
$login=function(string $guard,$user){Auth::forgetGuards();Auth::guard($guard)->setUser($user);};

DB::beginTransaction();
try {
    Mail::fake();
    $school=School::where('slug','drived-hub')->firstOrFail();
    app('session')->driver()->put('school_id',$school->id);
    $admin=Admin::where('school_id',$school->id)->where('role','school_admin')->firstOrFail();
    $secretary=Admin::where('school_id',$school->id)->where('role','branch_secretary')->whereNotNull('branch_id')->firstOrFail();
    $student=Student::where('email','student1@driveedhub.test')->firstOrFail();
    $guest=Student::where('email','guest1@driveedhub.test')->firstOrFail();
    foreach ([[$student,'student','8999900000001'],[$guest,'guest','8999900000002']] as [$owner,$label,$reference]) {
        $ownedEnrollment=EnrollmentRequest::where('learner_id',$owner->id)->firstOrFail();
        Payment::create(['school_id'=>$school->id,'branch_id'=>$owner->branch_id,'payer_user_id'=>$owner->id,'enrollment_request_id'=>$ownedEnrollment->id,'amount'=>100,'method'=>'gcash','reference'=>$reference,'paid_on'=>now(),'proof_of_payment_path'=>'receipts/qa-audit-'.$label.'.png','status'=>'pending']);
    }
    $foreignReceipt=EnrollmentRequest::where('school_id',$school->id)->where('branch_id','!=',$secretary->branch_id)->firstOrFail();
    $foreignReceipt->update(['payment_proof_path'=>'receipts/qa-audit-other-branch.png']);
    $access=app(ReceiptAccessService::class);
    $check($access->canView('receipts/qa-audit-student.png',$school,null,$student),'Student can view an owned receipt');
    $check(!$access->canView('receipts/qa-audit-guest.png',$school,null,$student),'Student cannot view a guest receipt');
    $check(!$access->canView('receipts/qa-audit-student.png',$school,null,$guest),'Guest cannot view a student receipt');
    $check($access->canView('receipts/qa-audit-guest.png',$school,null,$guest),'Guest can view an owned receipt');
    $check($access->canView('receipts/qa-audit-guest.png',$school,$admin,null),'School admin can view school receipts');
    $check(!$access->canView('receipts/unattached.png',$school,$admin,null),'Unattached receipt paths are denied');
    $otherSchool=School::where('slug','qa-audit-boundary')->firstOrFail();
    $check(!$access->canView('receipts/qa-audit-student.png',$otherSchool,$admin,null),'Receipt access enforces the requested school');
    $check(!$access->canView('receipts/qa-audit-other-branch.png',$school,$secretary,null),'Secretary cannot view another branch receipt');
    $check($access->canView('receipts/qa-audit-other-branch.png',$school,$admin,null),'Foreign-branch proof is available to the school admin');
    $check($access->canView('receipts/qa-audit-student.png',$school,$secretary,null),'Secretary can view own-branch receipts');
    $legacy=EnrollmentRequest::where('learner_id',$student->id)->firstOrFail();
    $legacy->update(['payment_proof_path'=>'receipts/legacy-only.png']);
    $check($access->canView('receipts/legacy-only.png',$school,null,$student),'Owned legacy enrollment-only proofs remain accessible');
    $check(!$access->canView('receipts/legacy-only.png',$school,null,$guest),'Legacy proofs enforce learner ownership');
    $payment=Payment::where('payer_user_id',$student->id)->firstOrFail();
    $payment->update(['proof_of_payment_path'=>'\\storage\\receipts\\windows.png']);
    $check($access->canView('receipts/windows.png',$school,null,$student),'Legacy Windows and storage-prefixed paths remain authorized for their owner');

    $login('admin',$secretary);
    $controller=app(AdminController::class);
    $deny(fn()=>$controller->settings($request('/settings'),$school),'Secretary cannot open school settings');
    $deny(fn()=>$controller->updateSettings($request('/settings','POST'),$school),'Secretary cannot update school settings');
    $deny(fn()=>$controller->storeCourse($request('/courses','POST'),$school),'Secretary cannot create courses');
    $deny(fn()=>$controller->updateCourse($request('/courses','PUT'),$school,1),'Secretary cannot update courses');
    $deny(fn()=>$controller->storePackage($request('/packages','POST'),$school,1),'Secretary cannot create course packages');
    $deny(fn()=>app(ReportController::class)->index($request('/reports'),$school,app(App\Services\FinancialService::class)),'Secretary cannot view school financial reports');
    $foreign=EnrollmentRequest::where('school_id',$school->id)->where('branch_id','!=',$secretary->branch_id)->firstOrFail();
    $deny(fn()=>app(EnrollmentRequestController::class)->viewPaymentProof($request('/proof'),$school,$foreign),'Enrollment proof endpoint enforces the secretary branch');

    $login('admin',$admin);
    $course=Course::where('school_id',$school->id)->firstOrFail();
    $body=['title'=>$course->title,'type'=>$course->type?:'theoretical','course_type'=>$course->course_type,'license_type'=>$course->license_type,'hours_required'=>max(1,$course->hours_required),'price'=>$course->price,'status'=>'active'];
    $duplicate=$controller->storeCourse($request('/courses','POST',$body),$school);
    $check($duplicate->getStatusCode()===422&&isset($duplicate->getData(true)['errors']['title']),'Duplicate course title returns a field-level 422 response');
    $same=$controller->updateCourse($request('/courses','PUT',$body),$school,$course->id);
    $check($same->getStatusCode()===200,'Updating a course without changing its own title succeeds');
    $otherCourse=Course::create(array_merge($body,['school_id'=>$otherSchool->id,'title'=>'QA Shared Course Title']));
    $shared=$controller->storeCourse($request('/courses','POST',array_merge($body,['title'=>$otherCourse->title])),$school);
    $check($shared->getStatusCode()===201,'The same course title is permitted in different schools');
    $detail=app(EnrollmentRequestController::class)->show($request('/enrollment'),$school,$legacy);
    $check($detail->getStatusCode()===302&&str_contains($detail->getTargetUrl(),'review='.$legacy->id),'Enrollment detail URLs open the supported verification interface');

    $module=CourseModule::where('school_id',$school->id)->firstOrFail();
    $moduleCourse=$module->course;
    app()->instance('current_school',$school);
    View::share('errors',new Illuminate\Support\ViewErrorBag());
    $lessonController=app(ModuleLessonController::class);
    $created=$lessonController->store($request('/lessons','POST',['title'=>'QA Admin Lesson','content'=>'<p>Admin authored.</p>']),$school,$moduleCourse,$module);
    $check($created->getStatusCode()===200&&$created->getData(true)['success'],'School admin can save a lesson');
    $lesson=App\Models\ModuleLesson::findOrFail($created->getData(true)['lesson']['id']);
    $createView=$lessonController->create($request('/lessons/create'),$school,$moduleCourse,$module)->render();
    $check(str_contains($createView,'/admin/courses/')&&!str_contains($createView,'action="http://localhost:8006/drived-hub/instructor/'),'Admin lesson form posts through admin routes');
    $updated=$lessonController->update($request('/lessons','PUT',['title'=>'QA Updated Admin Lesson','content'=>'<p>Updated.</p>']),$school,$moduleCourse,$module,$lesson);
    $check($updated->getData(true)['success']&&$lesson->fresh()->title==='QA Updated Admin Lesson','School admin can update a lesson');
    $login('admin',$secretary);
    $deny(fn()=>$lessonController->create($request('/lessons/create'),$school,$moduleCourse,$module),'Secretary cannot manage school course lessons');
    $login('instructor',Instructor::where('school_id',$school->id)->firstOrFail());
    $instructorView=$lessonController->create($request('/lessons/create'),$school,$moduleCourse,$module)->render();
    $check(str_contains($instructorView,'/instructor/courses/'),'Instructor lesson form retains instructor routes');

    $resetController=app(PasswordResetController::class);
    $system=Admin::create(['name'=>'QA Global Reset','email'=>'qa-seven-reset@example.invalid','school_id'=>null,'password'=>'Old-Audit-123!','role'=>'system_admin','is_active'=>true,'must_reset_password'=>false]);
    $resetController->sendResetLink($request('/system-admin/forgot-password','POST',['email'=>$system->email]));
    $mail=Mail::sent(PasswordResetRequested::class,fn($message)=>$message->hasTo($system->email))->first();
    $check($mail!==null&&$mail->school===null,'System-admin reset request creates a global reset email');
    $check(str_contains($mail->render(),'DriveED Hub'),'Global reset email renders without a school');
    $token=basename(parse_url($mail->resetUrl,PHP_URL_PATH));
    $record=DB::table('password_reset_tokens')->where('email',$system->email)->first();
    $check($record&&$record->school_id===null&&Hash::check($token,$record->token),'Global token is hashed and stored without a school foreign key');
    $resetBody=['email'=>$system->email,'password'=>'New-Audit-123!','password_confirmation'=>'New-Audit-123!','token'=>$token];
    $resetController->reset($request('/system-admin/reset-password','POST',array_merge($resetBody,['token'=>'invalid-token'])));
    $check(Hash::check('Old-Audit-123!',$system->fresh()->password),'Invalid global reset token cannot change the password');
    DB::table('password_reset_tokens')->where('email',$system->email)->update(['created_at'=>now()->subHours(2)]);
    $resetController->reset($request('/system-admin/reset-password','POST',$resetBody));
    $check(Hash::check('Old-Audit-123!',$system->fresh()->password),'Expired global reset token cannot change the password');
    DB::table('password_reset_tokens')->where('email',$system->email)->update(['created_at'=>now()]);
    $success=$resetController->reset($request('/system-admin/reset-password','POST',$resetBody));
    $check(Hash::check('New-Audit-123!',$system->fresh()->password),'A valid global reset token changes the system-admin password');
    $check(str_ends_with($success->getTargetUrl(),'/system-admin/login'),'Global reset redirects to the system-admin login');
    $check(!DB::table('password_reset_tokens')->where('email',$system->email)->exists(),'Successful reset consumes the global token');
    $resetController->reset($request('/system-admin/reset-password','POST',array_merge($resetBody,['password'=>'Replay-Audit-123!','password_confirmation'=>'Replay-Audit-123!'])));
    $check(Hash::check('New-Audit-123!',$system->fresh()->password),'A consumed reset token cannot be replayed');

    $tenant=Student::create(['school_id'=>$school->id,'name'=>'QA Tenant Reset','email'=>'qa-seven-tenant@example.invalid','password'=>'Old-Tenant-123!','role'=>'student','status'=>'active']);
    $resetController->sendResetLink($request('/forgot-password','POST',['email'=>$tenant->email]),$school);
    $tenantMail=Mail::sent(PasswordResetRequested::class,fn($message)=>$message->hasTo($tenant->email))->first();
    $tenantToken=basename(parse_url($tenantMail->resetUrl,PHP_URL_PATH));
    $tenantBody=['email'=>$tenant->email,'user_type'=>'student','token'=>$tenantToken,'password'=>'New-Tenant-123!','password_confirmation'=>'New-Tenant-123!'];
    $resetController->reset($request('/reset-password','POST',$tenantBody),$otherSchool);
    $check(Hash::check('Old-Tenant-123!',$tenant->fresh()->password),'School reset tokens cannot be used in another school');
    $tenantSuccess=$resetController->reset($request('/reset-password','POST',$tenantBody),$school);
    $check(Hash::check('New-Tenant-123!',$tenant->fresh()->password)&&str_ends_with($tenantSuccess->getTargetUrl(),'/drived-hub'),'School-specific password recovery still completes');
}finally{DB::rollBack();}
echo "{$checks} seven-fix regression checks passed; database changes rolled back; mail was faked.\n";
