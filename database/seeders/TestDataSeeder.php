<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\School;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Branch;
use App\Models\EnrollmentRequest;
use App\Models\CourseModule;
use App\Models\ModuleLesson;
use App\Models\Question;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentAttempt;
use App\Models\TimeSlot;
use App\Models\Booking;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $schoolA = School::where('slug', 'driving-school-a')->first();
        $schoolB = School::where('slug', 'driving-school-b')->first();

        if (!$schoolA || !$schoolB) {
            $this->command->error('Run TwoSchoolTestSeeder first!');
            return;
        }

        $this->command->info('Adding test data for Phase 1-3...');

        $this->seedSchoolA($schoolA);
        $this->seedSchoolB($schoolB);

        $this->command->info('');
        $this->command->info('TEST DATA SEEDING COMPLETE');
    }

    private function seedSchoolA(School $school): void
    {
        $branchA1 = Branch::where('school_id', $school->id)->where('name', 'Main Branch')->first();
        $branchA2 = Branch::where('school_id', $school->id)->where('name', 'Sub Branch')->first();

        $adminA1 = Admin::where('school_id', $school->id)->where('role', 'school_admin')->first();

        $instructorA1 = Instructor::where('school_id', $school->id)->where('branch_id', $branchA1->id)->first();
        $instructorA2 = Instructor::where('school_id', $school->id)->where('branch_id', $branchA2->id)->first();

        $manual = Course::where('school_id', $school->id)->where('course_type', 'theoretical')->first();
        $practical = Course::where('school_id', $school->id)->where('course_type', 'practical')->first();

        $students = Student::where('school_id', $school->id)->orderBy('email')->get();
        $studentA1 = $students->get(0);
        $studentA2 = $students->get(1);
        $studentA3 = $students->get(2);

        if ($studentA1) $studentA1->update(['has_passed_theoretical' => false, 'student_license_status' => 'none']);
        if ($studentA2) $studentA2->update(['has_passed_theoretical' => true, 'student_license_status' => 'verified', 'student_license_verified_at' => now()]);
        if ($studentA3) $studentA3->update(['has_passed_theoretical' => false, 'student_license_status' => 'none']);

        EnrollmentRequest::where('learner_id', $studentA1->id)->delete();
        EnrollmentRequest::where('learner_id', $studentA2->id)->delete();
        EnrollmentRequest::where('learner_id', $studentA3->id)->delete();

        if ($studentA1 && $manual) {
            EnrollmentRequest::create([
                'school_id' => $school->id, 'learner_id' => $studentA1->id, 'course_id' => $manual->id,
                'package_id' => $manual->packages->first()->id, 'status' => 'approved', 'payment_status' => 'paid',
                'experience_level' => 'new_driver', 'branch_id' => $branchA1->id, 'price' => 2000,
                'approved_by' => $adminA1->id, 'approved_at' => now(), 'enrolled_at' => now()
            ]);
        }
        if ($studentA1 && $practical) {
            EnrollmentRequest::create([
                'school_id' => $school->id, 'learner_id' => $studentA1->id, 'course_id' => $practical->id,
                'package_id' => $practical->packages->first()->id, 'status' => 'approved', 'payment_status' => 'paid',
                'experience_level' => 'new_driver', 'branch_id' => $branchA1->id, 'price' => 5000,
                'approved_by' => $adminA1->id, 'approved_at' => now(), 'enrolled_at' => now()
            ]);
        }
        if ($studentA2 && $manual) {
            EnrollmentRequest::create([
                'school_id' => $school->id, 'learner_id' => $studentA2->id, 'course_id' => $manual->id,
                'package_id' => $manual->packages->first()->id, 'status' => 'approved', 'payment_status' => 'paid',
                'experience_level' => 'new_driver', 'branch_id' => $branchA2->id, 'price' => 2000,
                'approved_by' => $adminA1->id, 'approved_at' => now(), 'enrolled_at' => now()
            ]);
        }
        if ($studentA3 && $practical) {
            EnrollmentRequest::create([
                'school_id' => $school->id, 'learner_id' => $studentA3->id, 'course_id' => $practical->id,
                'package_id' => $practical->packages->first()->id, 'status' => 'approved', 'payment_status' => 'paid',
                'experience_level' => 'new_driver', 'branch_id' => $branchA1->id, 'price' => 5000,
                'approved_by' => $adminA1->id, 'approved_at' => now(), 'enrolled_at' => now()
            ]);
        }

        $this->createCourseContent($school, $manual, 'TDC');
        $this->createCourseContent($school, $practical, 'PDC');

        $this->createTimeSlotsAndBookings($school, $branchA1, $instructorA1, $practical, [$studentA1, $studentA2, $studentA3]);
        $this->createTimeSlotsAndBookings($school, $branchA2, $instructorA2, $practical, [$studentA2]);

        if ($manual) {
            $this->createAssessmentAttempts($school, $manual, $studentA1, $studentA2);
        }
    }

    private function seedSchoolB(School $school): void
    {
        $branchB1 = Branch::where('school_id', $school->id)->where('name', 'Main Branch')->first();
        $branchB2 = Branch::where('school_id', $school->id)->where('name', 'Sub Branch')->first();

        $adminB1 = Admin::where('school_id', $school->id)->where('role', 'school_admin')->first();
        $instructorB1 = Instructor::where('school_id', $school->id)->where('branch_id', $branchB1->id)->first();

        $manual = Course::where('school_id', $school->id)->where('course_type', 'theoretical')->first();
        $practical = Course::where('school_id', $school->id)->where('course_type', 'practical')->first();

        $students = Student::where('school_id', $school->id)->orderBy('email')->get();
        $studentB1 = $students->get(0);

        if ($studentB1) {
            EnrollmentRequest::where('learner_id', $studentB1->id)->delete();
            if ($manual) {
                EnrollmentRequest::create([
                    'school_id' => $school->id, 'learner_id' => $studentB1->id, 'course_id' => $manual->id,
                    'package_id' => $manual->packages->first()->id, 'status' => 'approved', 'payment_status' => 'paid',
                    'experience_level' => 'new_driver', 'branch_id' => $branchB1->id, 'price' => 2000,
                    'approved_by' => $adminB1->id, 'approved_at' => now(), 'enrolled_at' => now()
                ]);
            }
        }

        $this->createCourseContent($school, $manual, 'TDC');
        $this->createCourseContent($school, $practical, 'PDC');

        $this->createTimeSlotsAndBookings($school, $branchB1, $instructorB1, $practical, [$studentB1]);
    }

    private function createCourseContent(School $school, ?Course $course, string $prefix): void
    {
        if (!$course) return;

        $module1 = CourseModule::updateOrCreate(
            ['school_id' => $school->id, 'course_id' => $course->id, 'title' => 'Orientation'],
            ['description' => 'Course orientation', 'module_type' => 'lesson', 'sort_order' => 1]
        );
        ModuleLesson::updateOrCreate(['school_id' => $school->id, 'module_id' => $module1->id, 'title' => 'Lesson 1'],
            ['content' => 'Content for lesson 1', 'sort_order' => 1]);
        ModuleLesson::updateOrCreate(['school_id' => $school->id, 'module_id' => $module1->id, 'title' => 'Lesson 2'],
            ['content' => 'Content for lesson 2', 'sort_order' => 2]);
        ModuleLesson::updateOrCreate(['school_id' => $school->id, 'module_id' => $module1->id, 'title' => 'Lesson 3'],
            ['content' => 'Content for lesson 3', 'sort_order' => 3]);

        $module2 = CourseModule::updateOrCreate(
            ['school_id' => $school->id, 'course_id' => $course->id, 'title' => 'Assessment'],
            ['description' => 'Final assessment', 'module_type' => 'assessment', 'sort_order' => 2]
        );

        $q1 = Question::create([
            'school_id' => $school->id, 'course_id' => $course->id,
            'question_text' => 'What is the stopping distance?', 'question_type' => 'multiple_choice',
            'options' => ['10m', '20m', '30m', '40m'], 'correct_answer' => '20m', 'default_points' => 1
        ]);
        $q2 = Question::create([
            'school_id' => $school->id, 'course_id' => $course->id,
            'question_text' => 'True or False: Stop on red', 'question_type' => 'true_false',
            'options' => ['True', 'False'], 'correct_answer' => 'True', 'default_points' => 1
        ]);
        $q3 = Question::create([
            'school_id' => $school->id, 'course_id' => $course->id,
            'question_text' => 'What does a yellow light mean?', 'question_type' => 'multiple_choice',
            'options' => ['Speed up', 'Slow down', 'Stop', 'Ignore'], 'correct_answer' => 'Slow down', 'default_points' => 1
        ]);

        AssessmentQuestion::updateOrCreate(['module_id' => $module2->id, 'question_id' => $q1->id], ['sort_order' => 1]);
        AssessmentQuestion::updateOrCreate(['module_id' => $module2->id, 'question_id' => $q2->id], ['sort_order' => 2]);
        AssessmentQuestion::updateOrCreate(['module_id' => $module2->id, 'question_id' => $q3->id], ['sort_order' => 3]);
    }

    private function createTimeSlotsAndBookings(School $school, Branch $branch, Instructor $instructor, ?Course $course, array $students): void
    {
        $today = now();
        $timeSlots = [];

        for ($i = 0; $i < 3; $i++) {
            $date = $today->copy()->addDays(1 + $i)->toDateString();
            $start = '09:00:00';
            $end = '10:00:00';

            $timeSlot = TimeSlot::updateOrCreate(
                ['school_id' => $school->id, 'branch_id' => $branch->id, 'date' => $date, 'start_time' => $start, 'end_time' => $end],
                ['course_id' => $course?->id, 'status' => 'open', 'max_instructors' => 1, 'max_students' => 10]
            );

            $existing = DB::table('schedule_instructors')
                ->where('time_slot_id', $timeSlot->id)
                ->where('instructor_id', $instructor->id)
                ->first();
            if ($existing) {
                DB::table('schedule_instructors')
                    ->where('time_slot_id', $timeSlot->id)
                    ->where('instructor_id', $instructor->id)
                    ->update(['school_id' => $school->id, 'assignment_type' => 'admin_assigned']);
            } else {
                DB::table('schedule_instructors')
                    ->insert(['time_slot_id' => $timeSlot->id, 'instructor_id' => $instructor->id, 'school_id' => $school->id, 'assignment_type' => 'admin_assigned']);
            }

            $timeSlots[] = $timeSlot;
        }

        foreach ($students as $idx => $student) {
            if (!$student) continue;
            $scheduledAt = $today->copy()->addDays(1 + $idx)->setHour(9)->setMinute(0)->toDateTimeString();

            Booking::updateOrCreate(
                ['school_id' => $school->id, 'student_id' => $student->id, 'time_slot_id' => $timeSlots[$idx]->id],
                [
                    'branch_id' => $branch->id,
                    'instructor_id' => $instructor->id,
                    'course_id' => $course->id,
                    'scheduled_at' => $scheduledAt,
                    'booking_date' => now(),
                    'status' => $idx === 0 ? 'completed' : 'scheduled',
                    'session_grade' => $idx === 0 ? 85.00 : null
                ]
            );
        }
    }

    private function createAssessmentAttempts(School $school, Course $manual, ?Student $studentA1, ?Student $studentA2): void
    {
        $module = CourseModule::where('school_id', $school->id)->where('course_id', $manual->id)->where('module_type', 'assessment')->first();
        if (!$module) return;

        $questions = $module->questions;
        $totalPoints = $questions->sum('default_points');

        $failedAnswers = [];
        foreach ($questions as $q) {
            $failedAnswers[$q->id] = 'wrong_answer';
        }

        $passedAnswers = [];
        foreach ($questions as $q) {
            $passedAnswers[$q->id] = $q->correct_answer;
        }

        if ($studentA1) {
            $enrollmentId = $studentA1->enrollmentRequests()->where('course_id', $manual->id)->first()?->id;
            AssessmentAttempt::create([
                'school_id' => $school->id, 'student_id' => $studentA1->id,
                'enrollment_request_id' => $enrollmentId,
                'course_id' => $manual->id, 'module_id' => $module->id,
                'score' => 1, 'total_points' => $totalPoints, 'percentage' => 33.33,
                'passed' => false, 'answers' => $failedAnswers, 'completed_at' => now()
            ]);
        }

        if ($studentA2) {
            $enrollmentId = $studentA2->enrollmentRequests()->where('course_id', $manual->id)->first()?->id;
            AssessmentAttempt::create([
                'school_id' => $school->id, 'student_id' => $studentA2->id,
                'enrollment_request_id' => $enrollmentId,
                'course_id' => $manual->id, 'module_id' => $module->id,
                'score' => 3, 'total_points' => $totalPoints, 'percentage' => 100.00,
                'passed' => true, 'answers' => $passedAnswers, 'completed_at' => now()
            ]);
        }
    }
}
