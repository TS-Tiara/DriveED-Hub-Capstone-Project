<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{Admin, Booking, Course, EnrollmentRequest, Instructor, Payment, School, Student, TimeSlot};
use Illuminate\Support\Facades\DB;
if (DB::connection()->getDatabaseName() !== 'drivingapp_regression_20261108') throw new RuntimeException('Use the isolated regression database.');
$school = School::where('slug', 'drived-hub')->firstOrFail();
Admin::where('role', 'system_admin')->firstOrFail()->forceFill(['password' => 'Audit-Local-123!', 'failed_login_attempts' => 0, 'locked_until' => null])->save();
$student = Student::firstOrCreate(['email' => 'nov8-findings@example.invalid'], ['school_id' => $school->id, 'branch_id' => $school->branches()->first()->id, 'name' => 'QA Package Learner', 'password' => 'Audit-Local-123!', 'role' => 'student', 'status' => 'active', 'email_verified_at' => now()]);
$student->forceFill(['email_verified_at' => now(), 'must_reset_password' => false, 'status' => 'active', 'archived_at' => null])->save();
$browserStudent = Student::firstOrCreate(['email' => 'nov8-browse@example.invalid'], ['school_id' => $school->id, 'branch_id' => $student->branch_id, 'name' => 'QA Course Browser', 'password' => 'Audit-Local-123!', 'role' => 'student', 'status' => 'active']);
$browserStudent->forceFill(['email_verified_at' => now(), 'must_reset_password' => false])->save();
$instructor = Instructor::where('email', 'instructor1@driveedhub.test')->firstOrFail();
$instructor->update(['license_status' => 'verified']);
$course = Course::firstOrCreate(['school_id' => $school->id, 'title' => 'QA Package Driving Course'], ['course_type' => 'theoretical', 'type' => 'TDC', 'price' => 0, 'duration_hours' => 0, 'hours_required' => 0, 'status' => 'active', 'description' => 'QA course with package-based hours and pricing.']);
Booking::where('student_id', $student->id)->where('course_id', $course->id)->delete();
TimeSlot::where('course_id', $course->id)->whereNull('batch_group')->delete();
foreach ([['Manual', 'manual', 2301], ['Automatic', 'automatic', 2600]] as [$name, $transmission, $price]) $course->packages()->firstOrCreate(['name' => $name], ['transmission_type' => $transmission, 'price' => $price, 'training_hours' => 15, 'tdc_hours' => 15]);
$enrollment = EnrollmentRequest::firstOrCreate(['school_id' => $school->id, 'learner_id' => $student->id, 'course_id' => $course->id], ['status' => 'approved', 'tdc_hours' => 15, 'price' => 2301, 'branch_id' => $student->branch_id]);
$payment = Payment::firstOrCreate(['school_id' => $school->id, 'payer_user_id' => $student->id, 'enrollment_request_id' => $enrollment->id], ['amount' => 2301, 'method' => 'on_site', 'paid_on' => now(), 'status' => 'approved', 'branch_id' => $student->branch_id]);
$slots = [];
for ($i = 0; $i < 3; $i++) {
    $slot = TimeSlot::firstOrCreate(['school_id' => $school->id, 'course_id' => $course->id, 'batch_group' => 'qa-document-batch', 'batch_day_number' => $i + 1], ['branch_id' => $student->branch_id, 'date' => now()->addDays(5 + $i), 'start_time' => '09:00', 'end_time' => '10:00', 'session_type' => 'theoretical', 'status' => 'open', 'max_instructors' => 4, 'max_students' => 30, 'notes' => 'QA Sequential Theoretical Schedule']);
    $slots[] = $slot->id;
    $slot->instructors()->detach($instructor->id);
}
$otherInstructors = Instructor::where('school_id', $school->id)->where('id', '!=', $instructor->id)->take(3)->get();
foreach ($otherInstructors as $other) {
    $other->update(['license_status' => 'verified']);
    foreach ($slots as $id) TimeSlot::find($id)->instructors()->syncWithoutDetaching([$other->id => ['school_id' => $school->id, 'assignment_type' => 'admin_assigned']]);
}
$school->schoolSetting->update(['background_type' => 'color', 'background_color' => '#e8f0fb', 'background_opacity' => 100, 'booking_cutoff_hours' => 0, 'advance_booking_days' => 0]);
$manifest = json_decode(file_get_contents(base_path('tmp/nov8-audit-manifest.json')), true);
$manifest['roles']['student'] = ['id' => $student->id, 'email' => $student->email];
$manifest['roles']['browse-student'] = ['id' => $browserStudent->id, 'email' => $browserStudent->email];
$manifest['findings'] = ['course' => $course->id, 'enrollment' => $enrollment->id, 'slots' => $slots, 'student' => $student->id, 'instructor' => $instructor->id];
echo json_encode($manifest, JSON_PRETTY_PRINT);
