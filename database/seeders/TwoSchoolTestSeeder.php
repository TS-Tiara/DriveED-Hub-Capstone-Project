<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Branch;
use App\Models\EnrollmentRequest;

class TwoSchoolTestSeeder extends Seeder
{
    public function run(): void
    {
        $pw = Hash::make('password123');

        $this->command->info('Creating Driving School A...');
        $schoolA = $this->createSchool('driving-school-a', 'Driving School A', '#3b82f6', '#1e40af');
        $branchesA = $this->createBranches($schoolA, [
            ['name' => 'Main Branch', 'address' => '123 Main St, City A'],
            ['name' => 'Sub Branch', 'address' => '456 Sub St, City A'],
        ]);
        $this->createAdmin($schoolA, 'admin@schoola.test', 'School A Admin', 'school_admin', null);
        $this->createInstructors($schoolA, $branchesA, $pw, 'A');
        $studentsA = $this->createStudents($schoolA, $branchesA, $pw, 'A');
        $this->createGuests($schoolA, $branchesA, $pw, 'A');
        $coursesA = $this->createCourses($schoolA);

        $this->command->info('Creating Driving School B...');
        $schoolB = $this->createSchool('driving-school-b', 'Driving School B', '#10b981', '#065f46');
        $branchesB = $this->createBranches($schoolB, [
            ['name' => 'Main Branch', 'address' => '789 Main St, City B'],
            ['name' => 'Sub Branch', 'address' => '321 Sub St, City B'],
        ]);
        $this->createAdmin($schoolB, 'admin@schoolb.test', 'School B Admin', 'school_admin', null);
        $this->createInstructors($schoolB, $branchesB, $pw, 'B');
        $studentsB = $this->createStudents($schoolB, $branchesB, $pw, 'B');
        $this->createGuests($schoolB, $branchesB, $pw, 'B');
        $coursesB = $this->createCourses($schoolB);

        $this->command->info('Creating enrollments...');
        $this->createEnrollments($schoolA, $studentsA, $coursesA);
        $this->createEnrollments($schoolB, $studentsB, $coursesB);

        $this->command->info('');
        $this->command->info('SEEDING COMPLETE');
        $this->command->info('Schools: Driving School A (driving-school-a), Driving School B (driving-school-b)');
        $this->command->info('Admin: admin@schoola.test / password123, admin@schoolb.test / password123');
    }

    private function createSchool(string $slug, string $name, string $primary, string $secondary): School
    {
        $school = School::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'timezone' => 'Asia/Manila',
                'branding' => json_encode(['logo' => null, 'colors' => ['primary' => $primary, 'secondary' => $secondary]]),
                'settings' => json_encode(['contact_number' => '+63 912 345 6789', 'email' => 'info@test.com', 'address' => 'Test Address', 'allow_self_registration' => true]),
                'instructor_removal_notice_days' => 7,
            ]
        );
        SchoolSetting::updateOrCreate(
            ['school_id' => $school->id],
            ['primary_color' => $primary, 'secondary_color' => $secondary, 'enable_branches' => true, 'instructor_selection_mode' => 'admin_assigned']
        );
        return $school;
    }

    private function createBranches(School $school, array $data): array
    {
        $branches = [];
        foreach ($data as $b) {
            $branches[] = Branch::updateOrCreate(
                ['school_id' => $school->id, 'name' => $b['name']],
                ['address' => $b['address'], 'contact_number' => '+63 912 000 0000', 'is_active' => true]
            );
        }
        return $branches;
    }

    private function createAdmin(School $school, string $email, string $name, string $role, ?int $branchId): Admin
    {
        return Admin::updateOrCreate(
            ['email' => $email],
            ['school_id' => $school->id, 'branch_id' => $branchId, 'name' => $name, 'password' => Hash::make('password123'), 'role' => $role, 'is_active' => true]
        );
    }

    private function createInstructors(School $school, array $branches, string $pw, string $suffix): array
    {
        $list = [
            ['name' => "Instructor {$suffix}1", 'email' => "instructor{$suffix}1@test.com", 'branch' => 0],
            ['name' => "Instructor {$suffix}2", 'email' => "instructor{$suffix}2@test.com", 'branch' => 0],
            ['name' => "Instructor {$suffix}3", 'email' => "instructor{$suffix}3@test.com", 'branch' => 1],
        ];
        $instructors = [];
        foreach ($list as $n) {
            $instructors[] = Instructor::updateOrCreate(
                ['email' => $n['email']],
                ['school_id' => $school->id, 'branch_id' => $branches[$n['branch']]->id, 'name' => $n['name'], 'contact' => '+63 912 000 0000', 'password' => $pw, 'license_number' => "LIC-{$suffix}-001", 'license_status' => 'verified', 'status' => 'active', 'availability' => 'available']
            );
        }
        return $instructors;
    }

    private function createStudents(School $school, array $branches, string $pw, string $suffix): array
    {
        $students = [];
        for ($i = 1; $i <= 6; $i++) {
            $hasPassedTdc = ($i % 2 === 0);
            $student = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => "student{$suffix}{$i}@test.com"],
                ['name' => "Student {$suffix}{$i}", 'branch_id' => $branches[$i % 2]->id, 'contact' => "+63 912 000 000{$i}", 'password' => $pw, 'status' => 'active', 'role' => 'student', 'experience_level' => $i <= 3 ? 'new_driver' : 'experienced', 'student_license_status' => $hasPassedTdc ? 'verified' : 'none', 'has_passed_theoretical' => $hasPassedTdc, 'email_verified_at' => now()]
            );
            $students[] = $student;
        }
        return $students;
    }

    private function createGuests(School $school, array $branches, string $pw, string $suffix): array
    {
        $guests = [];
        for ($i = 1; $i <= 3; $i++) {
            $g = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => "guest{$suffix}{$i}@test.com"],
                ['name' => "Guest {$suffix}{$i}", 'branch_id' => $branches[$i % 2]->id, 'contact' => "+63 912 000 000{$i}", 'password' => $pw, 'status' => 'active', 'role' => 'guest', 'experience_level' => 'new_driver', 'email_verified_at' => now()]
            );
            $guests[] = $g;
        }
        return $guests;
    }

    private function createCourses(School $school): array
    {
        $courses = [];
        $manual = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Manual'],
            ['description' => 'Theoretical Driving Course', 'type' => 'Theoretical', 'course_type' => 'theoretical', 'license_type' => 'non_professional', 'vehicle_type' => 'Car', 'status' => 'active', 'hours_required' => 15]
        );
        $courses[] = $manual;
        CoursePackage::updateOrCreate(['course_id' => $manual->id, 'name' => 'Manual Standard'], ['training_hours' => 15, 'price' => 2000.00, 'transmission_type' => 'manual', 'vehicle_type' => 'Car']);

        $practical = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical'],
            ['description' => 'Practical Driving Course', 'type' => 'Practical', 'course_type' => 'practical', 'license_type' => 'professional', 'vehicle_type' => 'Car', 'status' => 'active', 'hours_required' => 10]
        );
        $courses[] = $practical;
        CoursePackage::updateOrCreate(['course_id' => $practical->id, 'name' => 'Practical Standard'], ['training_hours' => 10, 'price' => 5000.00, 'transmission_type' => 'manual', 'vehicle_type' => 'Car']);

        $combo = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Fun Combo Drivers Pack'],
            ['description' => 'Complete TDC + PDC package at a discounted price. Includes Manual and Practical courses.', 'type' => 'Combo', 'course_type' => 'combo', 'license_type' => 'non_professional', 'vehicle_type' => 'Car', 'status' => 'active', 'hours_required' => 25]
        );
        $courses[] = $combo;
        CoursePackage::updateOrCreate(['course_id' => $combo->id, 'name' => 'Fun Combo Full Package'], ['training_hours' => 25, 'price' => 6000.00, 'transmission_type' => 'manual', 'vehicle_type' => 'Car', 'description' => 'Includes Manual + Practical at discounted price']);

        return $courses;
    }

    private function createEnrollments(School $school, array $students, array $courses): void
    {
        foreach ($students as $i => $student) {
            $course = $courses[$i % count($courses)];
            $package = $course->packages->first();
            $isEligible = $student->has_passed_theoretical;
            EnrollmentRequest::updateOrCreate(
                ['school_id' => $school->id, 'learner_id' => $student->id, 'course_id' => $course->id],
                ['package_id' => $package?->id, 'status' => $isEligible ? 'approved' : 'pending', 'payment_status' => $isEligible ? 'paid' : 'pending', 'experience_level' => $student->experience_level, 'branch_id' => $student->branch_id, 'price' => $package?->price ?? 0, 'approved_by' => $isEligible ? 1 : null, 'approved_at' => $isEligible ? now() : null, 'enrolled_at' => $isEligible ? now() : null]
            );
        }
    }
}