<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\Course;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Admin;
use App\Models\User;
use App\Models\CourseModule;
use App\Models\ModuleLesson;
use App\Models\SessionCompletion;
use App\Models\EnrollmentRequest;
use Illuminate\Support\Facades\Hash;

class Alpha2TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates test data for Alpha 2 LMS features:
     * - Theoretical and practical courses with prerequisites
     * - Students at various stages of completion
     * - Course modules and lessons
     * - Session completions
     * - Enrollment requests
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Alpha 2 Test Data Seeder...');

        // Create test school
        $school = $this->createTestSchool();
        $this->command->info('✓ School created');

        // Create users
        $admin = $this->createAdmin($school);
        $instructors = $this->createInstructors($school);
        $this->command->info('✓ Admin and instructors created');

        // Create courses
        $courses = $this->createCourses($school);
        $this->command->info('✓ Courses created');

        // Create modules and lessons
        $this->createCourseMaterials($courses);
        $this->command->info('✓ Course materials created');

        // Create students with various states
        $students = $this->createStudents($school);
        $this->command->info('✓ Students created');

        // Create enrollments and sessions
        $this->createEnrollmentsAndSessions($students, $courses, $instructors);
        $this->command->info('✓ Enrollments and sessions created');

        // Create enrollment requests (pending) - DISABLED: table structure different
        // $this->createEnrollmentRequests($students, $courses);
        // $this->command->info('✓ Enrollment requests created');

        $this->command->info('');
        $this->command->info('🎉 Alpha 2 Test Data Seeded Successfully!');
        $this->command->info('');
        $this->command->info('🏫 School: DriveED Hub Alpha 3 School');
        $this->command->info('📋 Test Accounts:');
        $this->command->info('   Admin: admin@gmail.com / password');
        $this->command->info('   Instructor 1: instructor1@gmail.com / password');
        $this->command->info('   Instructor 2: instructor2@gmail.com / password');
        $this->command->info('   Students: student1@gmail.com to student10@gmail.com / password');
        $this->command->info('');
        $this->command->info('📚 Test Data Summary:');
        $this->command->info('   - 4 Courses (2 theoretical, 2 practical)');
        $this->command->info('   - 10 Students (various completion stages)');
        $this->command->info('   - Course modules with lessons');
        $this->command->info('   - Session completions');
        $this->command->info('   - Pending enrollment requests');
    }

    private function createTestSchool(): School
    {
        // Check if test school already exists
        $school = School::where('slug', 'drived-hub-alpha3')->first();
        
        if (!$school) {
            $school = School::create([
                'name' => 'DriveED Hub Alpha 3 School',
                'slug' => 'drived-hub-alpha3',
                'timezone' => 'Asia/Manila',
                'branding' => json_encode([
                    'logo' => null,
                    'colors' => [
                        'primary' => '#667eea',
                        'secondary' => '#764ba2',
                        'accent' => '#f59e0b',
                    ]
                ]),
                'settings' => json_encode([
                    'contact_number' => '+63 917 123 4567',
                    'email' => 'drivedhub.test@gmail.com',
                    'address' => '123 Test Street, Angeles City, Pampanga',
                    'allow_self_registration' => true,
                ]),
                'instructor_removal_notice_days' => 7,
            ]);
        }

        // Ensure school settings exist
        if (!$school->schoolSetting) {
            SchoolSetting::create([
                'school_id' => $school->id,
                'primary_color' => '#667eea',
                'secondary_color' => '#764ba2',
                'accent_color' => '#f59e0b',
                'use_gradient_header' => true,
            ]);
        }

        return $school;
    }

    private function createAdmin(School $school): Admin
    {
        return Admin::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'school_id' => $school->id,
                'name' => 'Test Admin',
                'password' => Hash::make('password'),
                'contact' => '+63 917 111 1111',
                'role' => 'school_admin',
                'is_active' => true,
            ]
        );
    }

    private function createInstructors(School $school): array
    {
        $instructors = [];

        // Instructor 1 - Manual & Theoretical Specialist
        $instructors[] = Instructor::firstOrCreate(
            ['school_id' => $school->id, 'email' => 'instructor1@gmail.com'],
            [
                'name' => 'John Instructor',
                'password' => Hash::make('password'),
                'contact' => '+63 917 222 2222',
                'license_number' => 'LIC-2024-001',
                'bio' => 'Specialist in Manual Transmission & Theoretical Training with 8 years of experience.',
                'status' => 'active',
                'availability' => 'available',
            ]
        );

        // Instructor 2 - Automatic & Practical Specialist
        $instructors[] = Instructor::firstOrCreate(
            ['school_id' => $school->id, 'email' => 'instructor2@gmail.com'],
            [
                'name' => 'Maria Santos',
                'password' => Hash::make('password'),
                'contact' => '+63 917 333 3333',
                'license_number' => 'LIC-2024-002',
                'bio' => 'Specialist in Automatic Transmission & Practical Training with 5 years of experience.',
                'status' => 'active',
                'availability' => 'available',
            ]
        );

        return $instructors;
    }

    private function createCourses(School $school): array
    {
        $courses = [];

        // Theoretical Course - Non-Professional
        $courses['theoretical_nonpro'] = Course::create([
            'school_id' => $school->id,
            'title' => 'Theoretical Training - Non-Professional',
            'description' => 'Complete theoretical training covering traffic rules, road safety, vehicle operation basics, and defensive driving principles.',
            'course_type' => 'theoretical',
            'license_type' => 'non_professional',
            'hours_required' => 15.0,
            'price' => 3500.00,
            'duration_hours' => 15.0,
            'max_students' => 30,
            'status' => 'active',
            'type' => 'Theoretical',
            'vehicle_type' => 'Car',
            'is_featured' => true,
        ]);

        // Theoretical Course - Professional
        $courses['theoretical_pro'] = Course::create([
            'school_id' => $school->id,
            'title' => 'Theoretical Training - Professional',
            'description' => 'Advanced theoretical training for professional drivers including commercial vehicle regulations and safety standards.',
            'course_type' => 'theoretical',
            'license_type' => 'professional',
            'hours_required' => 20.0,
            'price' => 4500.00,
            'duration_hours' => 20.0,
            'max_students' => 30,
            'status' => 'active',
            'type' => 'Theoretical',
            'vehicle_type' => 'Commercial',
            'is_featured' => true,
        ]);

        // Practical Course - Non-Professional (requires theoretical)
        $courses['practical_nonpro'] = Course::create([
            'school_id' => $school->id,
            'title' => 'Practical Driving - Non-Professional',
            'description' => 'Hands-on driving training with certified instructors. Learn vehicle control, parking, road navigation, and prepare for the practical driving test.',
            'course_type' => 'practical',
            'license_type' => 'non_professional',
            'hours_required' => 20.0,
            'price' => 8500.00,
            'duration_hours' => 20.0,
            'max_students' => 20,
            'status' => 'active',
            'type' => 'Practical',
            'vehicle_type' => 'Car',
        ]);

        // Practical Course - Professional (requires theoretical)
        $courses['practical_pro'] = Course::create([
            'school_id' => $school->id,
            'title' => 'Practical Driving - Professional',
            'description' => 'Professional driving training for commercial vehicles with experienced instructors.',
            'course_type' => 'practical',
            'license_type' => 'professional',
            'hours_required' => 30.0,
            'price' => 12000.00,
            'duration_hours' => 30.0,
            'max_students' => 15,
            'status' => 'active',
            'type' => 'Practical',
            'vehicle_type' => 'Commercial',
        ]);

        // Set prerequisites (practical courses require theoretical)
        $courses['practical_nonpro']->update([
            'prerequisite_course_id' => $courses['theoretical_nonpro']->id,
        ]);
        $courses['practical_pro']->update([
            'prerequisite_course_id' => $courses['theoretical_pro']->id,
        ]);

        return $courses;
    }

    private function createCourseMaterials(array $courses): void
    {
        // Create modules for theoretical courses
        foreach (['theoretical_nonpro', 'theoretical_pro'] as $courseKey) {
            $course = $courses[$courseKey];

            // Module 1: Traffic Rules and Regulations
            $module1 = CourseModule::create([
                'course_id' => $course->id,
                'title' => 'Traffic Rules and Regulations',
                'description' => 'Learn fundamental traffic rules, road signs, and regulations in the Philippines.',
                'sort_order' => 1,
            ]);

            ModuleLesson::create([
                'module_id' => $module1->id,
                'title' => 'Introduction to Traffic Signs',
                'content' => 'Understanding regulatory, warning, and informational signs on Philippine roads.',
                'sort_order' => 1,
                'video_url' => 'https://www.youtube.com/watch?v=example1',
            ]);

            ModuleLesson::create([
                'module_id' => $module1->id,
                'title' => 'Right of Way and Priority Rules',
                'content' => 'Learn when to yield, how intersections work, and priority rules for different road users.',
                'sort_order' => 2,
            ]);

            // Module 2: Vehicle Operation Basics
            $module2 = CourseModule::create([
                'course_id' => $course->id,
                'title' => 'Vehicle Operation Basics',
                'description' => 'Understanding vehicle controls, dashboard indicators, and basic operations.',
                'sort_order' => 2,
            ]);

            ModuleLesson::create([
                'module_id' => $module2->id,
                'title' => 'Dashboard and Controls Overview',
                'content' => 'Learn about steering wheel, pedals, gear shift, and dashboard indicators.',
                'sort_order' => 1,
            ]);

            ModuleLesson::create([
                'module_id' => $module2->id,
                'title' => 'Starting and Stopping the Vehicle',
                'content' => 'Proper procedures for starting the engine, moving off, and stopping safely.',
                'sort_order' => 2,
                'video_url' => 'https://www.youtube.com/watch?v=example2',
            ]);

            // Module 3: Defensive Driving
            $module3 = CourseModule::create([
                'course_id' => $course->id,
                'title' => 'Defensive Driving Principles',
                'description' => 'Learn how to drive safely and anticipate potential hazards.',
                'sort_order' => 3,
            ]);

            ModuleLesson::create([
                'module_id' => $module3->id,
                'title' => 'Hazard Perception and Awareness',
                'content' => 'Identifying potential dangers and maintaining situational awareness on the road.',
                'sort_order' => 1,
            ]);

            ModuleLesson::create([
                'module_id' => $module3->id,
                'title' => 'Safe Following Distance and Speed Management',
                'content' => 'Maintaining safe distances and adjusting speed for road conditions.',
                'sort_order' => 2,
            ]);
        }

        // Create modules for practical courses
        foreach (['practical_nonpro', 'practical_pro'] as $courseKey) {
            $course = $courses[$courseKey];

            // Module 1: Basic Vehicle Control
            $module1 = CourseModule::create([
                'course_id' => $course->id,
                'title' => 'Basic Vehicle Control',
                'description' => 'Master basic vehicle control techniques in a safe environment.',
                'sort_order' => 1,
            ]);

            ModuleLesson::create([
                'module_id' => $module1->id,
                'title' => 'Steering and Lane Positioning',
                'content' => 'Practice proper steering techniques and maintaining correct lane position.',
                'sort_order' => 1,
            ]);

            // Module 2: Road Navigation
            $module2 = CourseModule::create([
                'course_id' => $course->id,
                'title' => 'Road Navigation and Maneuvers',
                'description' => 'Learn to navigate real traffic conditions and perform common maneuvers.',
                'sort_order' => 2,
            ]);

            ModuleLesson::create([
                'module_id' => $module2->id,
                'title' => 'Intersection Navigation',
                'content' => 'Practice turning, crossing intersections, and roundabout navigation.',
                'sort_order' => 1,
            ]);

            ModuleLesson::create([
                'module_id' => $module2->id,
                'title' => 'Parking Techniques',
                'content' => 'Master parallel parking, perpendicular parking, and angle parking.',
                'sort_order' => 2,
            ]);
        }
    }

    private function createStudents(School $school): array
    {
        $students = [];

        // Student 1: Just enrolled in theoretical (new driver)
        $students[] = $this->createStudent($school, [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'student1@gmail.com',
            'status' => 'active',
            'experience_level' => 'new_driver',
        ]);

        // Student 2: Halfway through theoretical (new driver)
        $students[] = $this->createStudent($school, [
            'first_name' => 'Maria',
            'last_name' => 'Garcia',
            'email' => 'student2@gmail.com',
            'status' => 'active',
            'experience_level' => 'new_driver',
        ]);

        // Student 3: Completed hours but not marked passed yet
        $students[] = $this->createStudent($school, [
            'first_name' => 'Pedro',
            'last_name' => 'Santos',
            'email' => 'student3@gmail.com',
            'status' => 'active',
            'experience_level' => 'new_driver',
        ]);

        // Student 4: Passed theoretical, enrolled in practical
        $students[] = $this->createStudent($school, [
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'email' => 'student4@gmail.com',
            'status' => 'active',
            'theoretical_passed' => true,
            'theoretical_passed_at' => now()->subDays(7),
            'experience_level' => 'new_driver',
        ]);

        // Student 5: Passed theoretical, not yet enrolled in practical
        $students[] = $this->createStudent($school, [
            'first_name' => 'Carlos',
            'last_name' => 'Mendoza',
            'email' => 'student5@gmail.com',
            'status' => 'active',
            'theoretical_passed' => true,
            'theoretical_passed_at' => now()->subDays(3),
            'experience_level' => 'new_driver',
        ]);

        // Student 6: Experienced driver, in theoretical
        $students[] = $this->createStudent($school, [
            'first_name' => 'Sofia',
            'last_name' => 'Torres',
            'email' => 'student6@gmail.com',
            'status' => 'active',
            'experience_level' => 'experienced',
        ]);

        // Student 7: Experienced driver, passed theoretical
        $students[] = $this->createStudent($school, [
            'first_name' => 'Miguel',
            'last_name' => 'Ramos',
            'email' => 'student7@gmail.com',
            'status' => 'active',
            'theoretical_passed' => true,
            'theoretical_passed_at' => now()->subDays(14),
            'experience_level' => 'experienced',
        ]);

        // Student 8: Completing practical training
        $students[] = $this->createStudent($school, [
            'first_name' => 'Isabella',
            'last_name' => 'Cruz',
            'email' => 'student8@gmail.com',
            'status' => 'active',
            'theoretical_passed' => true,
            'theoretical_passed_at' => now()->subDays(30),
            'experience_level' => 'new_driver',
        ]);

        // Student 9: Guest (no enrollments yet)
        $students[] = $this->createStudent($school, [
            'first_name' => 'Diego',
            'last_name' => 'Fernandez',
            'email' => 'student9@gmail.com',
            'status' => 'active',
            'experience_level' => 'new_driver',
        ]);

        // Student 10: Guest with pending requests
        $students[] = $this->createStudent($school, [
            'first_name' => 'Luna',
            'last_name' => 'Martinez',
            'email' => 'student10@gmail.com',
            'status' => 'active',
            'experience_level' => 'experienced',
        ]);

        return $students;
    }

    private function createStudent(School $school, array $data): Student
    {
        return Student::firstOrCreate(
            ['school_id' => $school->id, 'email' => $data['email']],
            [
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'password' => Hash::make('password'),
                'contact' => '+63 917 ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'address' => rand(100, 999) . ' Test Street, Angeles City',
                'status' => $data['status'],
                'role' => 'student',
                'experience_level' => $data['experience_level'],
                'has_passed_theoretical' => $data['theoretical_passed'] ?? false,
                'theoretical_passed_at' => $data['theoretical_passed_at'] ?? null,
                'enrollment_date' => $data['theoretical_passed'] ?? false ? now()->subDays(30) : now(),
            ]
        );
    }

    private function createEnrollmentsAndSessions(array $students, array $courses, array $instructors): void
    {
        // Student 1: Just started theoretical (1 session, 2 hours)
        $enrollment1 = EnrollmentRequest::create([
            'learner_id' => $students[0]->id,
            'course_id' => $courses['theoretical_nonpro']->id,
            'requested_at' => now()->subDays(3),
            'approved_at' => now()->subDays(2),
            'status' => 'approved',
        ]);
        $this->createSession($enrollment1, $instructors[0], now()->subDays(1), 2.0, 'theoretical');

        // Student 2: Halfway through theoretical (4 sessions, 8 hours)
        $enrollment2 = EnrollmentRequest::create([
            'learner_id' => $students[1]->id,
            'course_id' => $courses['theoretical_nonpro']->id,
            'requested_at' => now()->subDays(11),
            'approved_at' => now()->subDays(10),
            'status' => 'approved',
        ]);
        $this->createSession($enrollment2, $instructors[0], now()->subDays(9), 2.0, 'theoretical');
        $this->createSession($enrollment2, $instructors[0], now()->subDays(7), 2.0, 'theoretical');
        $this->createSession($enrollment2, $instructors[1], now()->subDays(5), 2.0, 'theoretical');
        $this->createSession($enrollment2, $instructors[1], now()->subDays(3), 2.0, 'theoretical');

        // Student 3: Completed required hours (8 sessions, 16 hours) - Ready to be marked passed
        $enrollment3 = EnrollmentRequest::create([
            'learner_id' => $students[2]->id,
            'course_id' => $courses['theoretical_nonpro']->id,
            'requested_at' => now()->subDays(21),
            'approved_at' => now()->subDays(20),
            'status' => 'approved',
        ]);
        for ($i = 19; $i >= 5; $i -= 2) {
            $this->createSession($enrollment3, $instructors[0], now()->subDays($i), 2.0, 'theoretical');
        }

        // Student 4: Passed theoretical, now in practical (theoretical complete, practical started)
        $theoreticalEnrollment4 = EnrollmentRequest::create([
            'learner_id' => $students[3]->id,
            'course_id' => $courses['theoretical_nonpro']->id,
            'requested_at' => now()->subDays(31),
            'approved_at' => now()->subDays(30),
            'completed_at' => now()->subDays(7),
            'status' => 'completed',
        ]);
        for ($i = 0; $i < 8; $i++) {
            $this->createSession($theoreticalEnrollment4, $instructors[0], now()->subDays(28 - ($i * 3)), 2.0, 'theoretical');
        }

        $practicalEnrollment4 = EnrollmentRequest::create([
            'learner_id' => $students[3]->id,
            'course_id' => $courses['practical_nonpro']->id,
            'requested_at' => now()->subDays(7),
            'approved_at' => now()->subDays(6),
            'status' => 'approved',
        ]);
        $this->createSession($practicalEnrollment4, $instructors[1], now()->subDays(5), 2.0, 'practical');
        $this->createSession($practicalEnrollment4, $instructors[1], now()->subDays(3), 2.0, 'practical');

        // Student 5: Passed theoretical, no practical enrollment yet
        $theoreticalEnrollment5 = EnrollmentRequest::create([
            'learner_id' => $students[4]->id,
            'course_id' => $courses['theoretical_nonpro']->id,
            'requested_at' => now()->subDays(19),
            'approved_at' => now()->subDays(18),
            'completed_at' => now()->subDays(3),
            'status' => 'completed',
        ]);
        for ($i = 0; $i < 8; $i++) {
            $this->createSession($theoreticalEnrollment5, $instructors[0], now()->subDays(16 - ($i * 2)), 2.0, 'theoretical');
        }

        // Student 6: Experienced driver in theoretical (fewer sessions, 6 hours)
        $enrollment6 = EnrollmentRequest::create([
            'learner_id' => $students[5]->id,
            'course_id' => $courses['theoretical_nonpro']->id,
            'requested_at' => now()->subDays(6),
            'approved_at' => now()->subDays(5),
            'status' => 'approved',
        ]);
        $this->createSession($enrollment6, $instructors[1], now()->subDays(4), 3.0, 'theoretical');
        $this->createSession($enrollment6, $instructors[1], now()->subDays(2), 3.0, 'theoretical');

        // Student 7: Experienced driver, passed theoretical
        $theoreticalEnrollment7 = EnrollmentRequest::create([
            'learner_id' => $students[6]->id,
            'course_id' => $courses['theoretical_nonpro']->id,
            'requested_at' => now()->subDays(22),
            'approved_at' => now()->subDays(21),
            'completed_at' => now()->subDays(14),
            'status' => 'completed',
        ]);
        for ($i = 0; $i < 6; $i++) {
            $this->createSession($theoreticalEnrollment7, $instructors[0], now()->subDays(20 - ($i * 3)), 2.5, 'theoretical');
        }

        // Student 8: In practical training, making good progress (10 hours)
        $theoreticalEnrollment8 = EnrollmentRequest::create([
            'learner_id' => $students[7]->id,
            'course_id' => $courses['theoretical_nonpro']->id,
            'requested_at' => now()->subDays(46),
            'approved_at' => now()->subDays(45),
            'completed_at' => now()->subDays(30),
            'status' => 'completed',
        ]);

        $practicalEnrollment8 = EnrollmentRequest::create([
            'learner_id' => $students[7]->id,
            'course_id' => $courses['practical_nonpro']->id,
            'requested_at' => now()->subDays(29),
            'approved_at' => now()->subDays(28),
            'status' => 'approved',
        ]);
        $this->createSession($practicalEnrollment8, $instructors[1], now()->subDays(26), 2.0, 'practical', 'Good control, needs practice with parking');
        $this->createSession($practicalEnrollment8, $instructors[1], now()->subDays(23), 2.0, 'practical', 'Improving steering, worked on lane changes');
        $this->createSession($practicalEnrollment8, $instructors[1], now()->subDays(19), 2.0, 'practical', 'Practiced parallel parking');
        $this->createSession($practicalEnrollment8, $instructors[1], now()->subDays(15), 2.0, 'practical', 'Highway driving practice');
        $this->createSession($practicalEnrollment8, $instructors[1], now()->subDays(11), 2.0, 'practical', 'Final test preparation');
    }

    private function createSession(Enrollment $enrollment, Instructor $instructor, $date, float $hours, string $type, ?string $notes = null): SessionCompletion
    {
        return SessionCompletion::create([
            'enrollment_id' => $enrollment->id,
            'instructor_id' => $instructor->id,
            'session_type' => $type,
            'session_date' => $date,
            'hours_completed' => $hours,
            'notes' => $notes ?? 'Student attended and participated actively in the session.',
        ]);
    }

    private function createEnrollmentRequests(array $students, array $courses): void
    {
        // Student 9: Pending request for theoretical
        EnrollmentRequest::create([
            'student_id' => $students[8]->id,
            'course_id' => $courses['theoretical_nonpro']->id,
            'status' => 'pending',
            'request_date' => now()->subHours(12),
            'notes' => 'Interested in learning to drive. Complete beginner.',
            'experience_level' => 'new_driver',
        ]);

        // Student 10: Pending request with experienced driver status
        EnrollmentRequest::create([
            'student_id' => $students[9]->id,
            'course_id' => $courses['theoretical_nonpro']->id,
            'status' => 'pending',
            'request_date' => now()->subHours(6),
            'notes' => 'Have driving experience from abroad, need Philippine license.',
            'experience_level' => 'experienced',
        ]);
    }
}
