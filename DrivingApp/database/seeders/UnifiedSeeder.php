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
use App\Models\TimeSlot;
use App\Models\ScheduleInstructor;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Progress;
use App\Models\EnrollmentRequest;
use App\Models\Notification;

/**
 * Unified Seeder - Comprehensive Test Data
 * 
 * This seeder creates a complete test environment with:
 * - 2 System Administrators (platform level)
 * - 3 Driving Schools with proper Filipino names
 * - School Admins, Instructors, Students with proper names
 * - Courses with packages, time slots, bookings, payments
 * 
 * Run with: php artisan db:seed --class=UnifiedSeeder
 * 
 * All passwords: "password123" (except system admins: "sysadmin123!")
 */
class UnifiedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════╗');
        $this->command->info('║           UNIFIED SEEDER - COMPREHENSIVE TEST DATA           ║');
        $this->command->info('╚══════════════════════════════════════════════════════════════╝');
        $this->command->info('');

        // Create System Administrators
        $this->createSystemAdmins();

        // Create Schools with all related data
        $this->createSmartDrivingSchool();
        $this->createLySpeedDrivingSchool();
        $this->createDriveEdHubSchool();

        $this->printCredentialsSummary();
    }

    /**
     * Create System Administrators (Platform Level)
     */
    private function createSystemAdmins(): void
    {
        $this->command->info('🔐 Creating System Administrators...');

        Admin::updateOrCreate(
            ['email' => 'systemadmin@gmail.com'],
            [
                'school_id' => null,
                'name' => 'Tiara Angelica Santos',
                'password' => Hash::make('sysadmin123!'),
                'role' => 'system_admin',
                'is_active' => true,
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'systemadmin2@gmail.com'],
            [
                'school_id' => null,
                'name' => 'Ricardo Jose Dela Cruz',
                'password' => Hash::make('sysadmin123!'),
                'role' => 'system_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('   ✓ 2 System Administrators created');
    }

    /**
     * Create Smart Driving School
     */
    private function createSmartDrivingSchool(): void
    {
        $this->command->info('');
        $this->command->info('🏫 Creating Smart Driving School...');

        // Create School
        $school = School::updateOrCreate(
            ['slug' => 'smart-driving'],
            [
                'name' => 'Smart Driving School',
                'timezone' => 'Asia/Manila',
                'branding' => json_encode([
                    'logo' => null,
                    'colors' => [
                        'primary' => '#3b82f6',
                        'secondary' => '#1e40af',
                        'accent' => '#f59e0b',
                    ]
                ]),
                'settings' => json_encode([
                    'contact_number' => '+63 917 123 4567',
                    'email' => 'info@smartdriving.com',
                    'address' => '123 MacArthur Highway, Angeles City, Pampanga',
                    'allow_self_registration' => true,
                ]),
                'instructor_removal_notice_days' => 7,
            ]
        );

        // Create School Settings
        SchoolSetting::updateOrCreate(
            ['school_id' => $school->id],
            [
                'primary_color' => '#3b82f6',
                'secondary_color' => '#fbbf24',
                'accent_color' => '#1e40af',
                'use_gradient_header' => false,
                'header_text_color' => '#ffffff',
                'background_type' => 'color',
                'background_color' => '#f8fafc',
                'sidebar_bg_color' => '#ffffff',
                'sidebar_text_color' => '#3b82f6',
                'instructor_selection_mode' => 'student_choice',
                'enable_booking_queue' => true,
                'booking_queue_days' => 3,
            ]
        );

        // Create Admins
        $adminData = [
            ['name' => 'Maria Cristina Santos', 'email' => 'maria.santos@smartdriving.com'],
            ['name' => 'Jose Antonio Reyes', 'email' => 'jose.reyes@smartdriving.com'],
            ['name' => 'Carmen Rosa Villanueva', 'email' => 'carmen.villanueva@smartdriving.com'],
        ];

        foreach ($adminData as $admin) {
            Admin::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'school_id' => $school->id,
                    'name' => $admin['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'school_admin',
                    'is_active' => true,
                ]
            );
        }

        // Test Admin Account
        Admin::updateOrCreate(
            ['email' => 'schooladmin@gmail.com'],
            [
                'school_id' => $school->id,
                'name' => 'Demo School Admin',
                'password' => Hash::make('password123'),
                'role' => 'school_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('   ✓ 4 School Admins created');

        // Create Instructors
        $instructorData = [
            [
                'name' => 'Juan Carlos Dela Cruz',
                'email' => 'juan.delacruz@smartdriving.com',
                'contact' => '+63-917-555-1001',
                'license' => 'LIC-SD-2024-001',
                'bio' => 'Senior Instructor with 10 years of experience in Manual Transmission vehicles.',
            ],
            [
                'name' => 'Ana Maria Garcia',
                'email' => 'ana.garcia@smartdriving.com',
                'contact' => '+63-917-555-1002',
                'license' => 'LIC-SD-2024-002',
                'bio' => 'Specialist in Automatic Transmission and Defensive Driving.',
            ],
            [
                'name' => 'Pedro Miguel Martinez',
                'email' => 'pedro.martinez@smartdriving.com',
                'contact' => '+63-917-555-1003',
                'license' => 'LIC-SD-2024-003',
                'bio' => 'Expert in Road Safety and Highway Driving techniques.',
            ],
            [
                'name' => 'Rosa Elena Villanueva',
                'email' => 'rosa.villanueva@smartdriving.com',
                'contact' => '+63-917-555-1004',
                'license' => 'LIC-SD-2024-004',
                'bio' => 'Certified TDC Instructor with expertise in LTO Exam Preparation.',
            ],
            [
                'name' => 'Roberto Luis Fernandez',
                'email' => 'roberto.fernandez@smartdriving.com',
                'contact' => '+63-917-555-1005',
                'license' => 'LIC-SD-2024-005',
                'bio' => 'Specialist in Motorcycle and Practical Driving Training.',
            ],
        ];

        $instructors = [];
        foreach ($instructorData as $inst) {
            $instructors[] = Instructor::updateOrCreate(
                ['email' => $inst['email']],
                [
                    'school_id' => $school->id,
                    'name' => $inst['name'],
                    'contact' => $inst['contact'],
                    'password' => Hash::make('password123'),
                    'license_number' => $inst['license'],
                    'bio' => $inst['bio'],
                    'status' => 'active',
                    'availability' => 'available',
                ]
            );
        }

        // Test Instructor Account
        $instructors[] = Instructor::updateOrCreate(
            ['email' => 'instructor@gmail.com'],
            [
                'school_id' => $school->id,
                'name' => 'Demo Instructor',
                'contact' => '+63-917-555-0000',
                'password' => Hash::make('password123'),
                'license_number' => 'LIC-SD-TEST-001',
                'bio' => 'Test instructor account for demo purposes.',
                'status' => 'active',
                'availability' => 'available',
            ]
        );

        $this->command->info('   ✓ 6 Instructors created');

        // Create Courses
        $courses = $this->createSmartDrivingCourses($school);
        $this->command->info('   ✓ 3 Courses with packages created');

        // Create Students
        $students = $this->createSmartDrivingStudents($school);
        $this->command->info('   ✓ 15 Students created');

        // Create Time Slots, Bookings, and Payments
        $this->createTimeSlotsAndAssignments($school, $instructors, $courses);
        $this->createBookingsAndPayments($school, $students, $instructors, $courses);

        $this->command->info('   ✓ Time slots, bookings, and payments created');

        // Create guest students with enrollment requests
        $admins = Admin::where('school_id', $school->id)->where('role', 'school_admin')->get()->all();
        $guests = $this->createGuestsAndEnrollmentRequests($school, $courses, $admins);
        $this->command->info('   ✓ Guest students and enrollment requests created');

        // Create sample notifications
        $this->createSampleNotifications($school, $students, $instructors, $admins, $guests);
        $this->command->info('   ✓ Sample notifications created');
    }

    /**
     * Create LySpeed Driving School
     */
    private function createLySpeedDrivingSchool(): void
    {
        $this->command->info('');
        $this->command->info('🏫 Creating LySpeed Driving School...');

        // Create School
        $school = School::updateOrCreate(
            ['slug' => 'lyspeed-driving'],
            [
                'name' => 'LySpeed Driving School',
                'timezone' => 'Asia/Manila',
                'branding' => json_encode([
                    'logo' => null,
                    'colors' => [
                        'primary' => '#8B0000',
                        'secondary' => '#ffffff',
                        'accent' => '#B22222',
                    ]
                ]),
                'settings' => json_encode([
                    'contact_number' => '+63 918 234 5678',
                    'email' => 'info@lyspeed.com',
                    'address' => '456 Jose Abad Santos Avenue, San Fernando, Pampanga',
                    'allow_self_registration' => true,
                ]),
                'instructor_removal_notice_days' => 7,
            ]
        );

        // Create School Settings
        SchoolSetting::updateOrCreate(
            ['school_id' => $school->id],
            [
                'primary_color' => '#8B0000',
                'secondary_color' => '#ffffff',
                'accent_color' => '#B22222',
                'use_gradient_header' => false,
                'header_text_color' => '#ffffff',
                'background_type' => 'color',
                'background_color' => '#f8fafc',
                'sidebar_bg_color' => '#ffffff',
                'sidebar_text_color' => '#8B0000',
                'instructor_selection_mode' => 'student_choice',
                'enable_booking_queue' => true,
                'booking_queue_days' => 2,
            ]
        );

        // Create Admins
        $adminData = [
            ['name' => 'Carlos Miguel Villanueva', 'email' => 'carlos.villanueva@lyspeed.com'],
            ['name' => 'Elena Rose Gonzales', 'email' => 'elena.gonzales@lyspeed.com'],
        ];

        foreach ($adminData as $admin) {
            Admin::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'school_id' => $school->id,
                    'name' => $admin['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'school_admin',
                    'is_active' => true,
                ]
            );
        }

        // Test Admin Account
        Admin::updateOrCreate(
            ['email' => 'lyspeed.admin@gmail.com'],
            [
                'school_id' => $school->id,
                'name' => 'LySpeed Demo Admin',
                'password' => Hash::make('password123'),
                'role' => 'school_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('   ✓ 3 School Admins created');

        // Create Instructors
        $instructorData = [
            [
                'name' => 'Miguel Angel Santos',
                'email' => 'miguel.santos@lyspeed.com',
                'contact' => '+63-918-666-2001',
                'license' => 'LIC-LS-2024-001',
                'bio' => 'Expert in Manual and Automatic Transmission vehicles.',
            ],
            [
                'name' => 'Elena Patricia Ramos',
                'email' => 'elena.ramos@lyspeed.com',
                'contact' => '+63-918-666-2002',
                'license' => 'LIC-LS-2024-002',
                'bio' => 'Specialist in Manual Transmission and City Driving.',
            ],
            [
                'name' => 'Fernando Jose Cruz',
                'email' => 'fernando.cruz@lyspeed.com',
                'contact' => '+63-918-666-2003',
                'license' => 'LIC-LS-2024-003',
                'bio' => 'Expert in Highway Driving and Defensive Techniques.',
            ],
        ];

        $instructors = [];
        foreach ($instructorData as $inst) {
            $instructors[] = Instructor::updateOrCreate(
                ['email' => $inst['email']],
                [
                    'school_id' => $school->id,
                    'name' => $inst['name'],
                    'contact' => $inst['contact'],
                    'password' => Hash::make('password123'),
                    'license_number' => $inst['license'],
                    'bio' => $inst['bio'],
                    'status' => 'active',
                    'availability' => 'available',
                ]
            );
        }

        // Test Instructor Account
        $instructors[] = Instructor::updateOrCreate(
            ['email' => 'lyspeed.instructor@gmail.com'],
            [
                'school_id' => $school->id,
                'name' => 'LySpeed Demo Instructor',
                'contact' => '+63-918-666-0000',
                'password' => Hash::make('password123'),
                'license_number' => 'LIC-LS-TEST-001',
                'bio' => 'Test instructor account for demo purposes.',
                'status' => 'active',
                'availability' => 'available',
            ]
        );

        $this->command->info('   ✓ 4 Instructors created');

        // Create Courses
        $courses = $this->createLySpeedCourses($school);
        $this->command->info('   ✓ 3 Courses with packages created');

        // Create Students
        $students = $this->createLySpeedStudents($school);
        $this->command->info('   ✓ 10 Students created');

        // Create Time Slots, Bookings, and Payments
        $this->createTimeSlotsAndAssignments($school, $instructors, $courses);
        $this->createBookingsAndPayments($school, $students, $instructors, $courses);

        $this->command->info('   ✓ Time slots, bookings, and payments created');

        // Create guest students with enrollment requests
        $admins = Admin::where('school_id', $school->id)->where('role', 'school_admin')->get()->all();
        $guests = $this->createGuestsAndEnrollmentRequests($school, $courses, $admins);
        $this->command->info('   ✓ Guest students and enrollment requests created');

        // Create sample notifications
        $this->createSampleNotifications($school, $students, $instructors, $admins, $guests);
        $this->command->info('   ✓ Sample notifications created');
    }

    /**
     * Create DriveED Hub School
     */
    private function createDriveEdHubSchool(): void
    {
        $this->command->info('');
        $this->command->info('🏫 Creating DriveED Hub School...');

        // Create School
        $school = School::updateOrCreate(
            ['slug' => 'drived-hub'],
            [
                'name' => 'DriveED Hub Driving School',
                'timezone' => 'Asia/Manila',
                'branding' => json_encode([
                    'logo' => null,
                    'colors' => [
                        'primary' => '#667eea',
                        'secondary' => '#764ba2',
                        'accent' => '#1e40af',
                    ]
                ]),
                'settings' => json_encode([
                    'contact_number' => '+63 919 345 6789',
                    'email' => 'info@drivedhub.com',
                    'address' => '789 Del Pilar Street, Clark Freeport Zone, Pampanga',
                    'allow_self_registration' => true,
                ]),
                'instructor_removal_notice_days' => 7,
            ]
        );

        // Create School Settings with Gradient
        SchoolSetting::updateOrCreate(
            ['school_id' => $school->id],
            [
                'primary_color' => '#667eea',
                'secondary_color' => '#764ba2',
                'accent_color' => '#1e40af',
                'use_gradient_header' => true,
                'header_text_color' => '#ffffff',
                'background_type' => 'gradient',
                'background_color' => '#f8fafc',
                'sidebar_bg_color' => '#ffffff',
                'sidebar_text_color' => '#667eea',
                'instructor_selection_mode' => 'admin_assigned',
                'enable_booking_queue' => true,
                'booking_queue_days' => 3,
            ]
        );

        // Create Admins
        $adminData = [
            ['name' => 'Antonio Francisco Reyes', 'email' => 'antonio.reyes@drivedhub.com'],
            ['name' => 'Patricia Lyn Mendoza', 'email' => 'patricia.mendoza@drivedhub.com'],
        ];

        foreach ($adminData as $admin) {
            Admin::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'school_id' => $school->id,
                    'name' => $admin['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'school_admin',
                    'is_active' => true,
                ]
            );
        }

        // Test Admin Account
        Admin::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'school_id' => $school->id,
                'name' => 'DriveED Demo Admin',
                'password' => Hash::make('password123'),
                'role' => 'school_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('   ✓ 3 School Admins created');

        // Create Instructors
        $instructorData = [
            [
                'name' => 'Ricardo Antonio Cruz',
                'email' => 'ricardo.cruz@drivedhub.com',
                'contact' => '+63-919-777-3001',
                'license' => 'LIC-DH-2024-001',
                'bio' => 'Senior Instructor specializing in Manual Transmission and Theoretical Training.',
            ],
            [
                'name' => 'Maria Victoria Santos',
                'email' => 'maria.santos@drivedhub.com',
                'contact' => '+63-919-777-3002',
                'license' => 'LIC-DH-2024-002',
                'bio' => 'Expert in Automatic Transmission and Practical Driving Training.',
            ],
        ];

        $instructors = [];
        foreach ($instructorData as $inst) {
            $instructors[] = Instructor::updateOrCreate(
                ['email' => $inst['email']],
                [
                    'school_id' => $school->id,
                    'name' => $inst['name'],
                    'contact' => $inst['contact'],
                    'password' => Hash::make('password123'),
                    'license_number' => $inst['license'],
                    'bio' => $inst['bio'],
                    'status' => 'active',
                    'availability' => 'available',
                ]
            );
        }

        // Test Instructor Accounts
        $instructors[] = Instructor::updateOrCreate(
            ['email' => 'instructor1@gmail.com'],
            [
                'school_id' => $school->id,
                'name' => 'DriveED Instructor 1',
                'contact' => '+63-919-777-0001',
                'password' => Hash::make('password123'),
                'license_number' => 'LIC-DH-TEST-001',
                'bio' => 'Test instructor account for demo purposes.',
                'status' => 'active',
                'availability' => 'available',
            ]
        );

        $instructors[] = Instructor::updateOrCreate(
            ['email' => 'instructor2@gmail.com'],
            [
                'school_id' => $school->id,
                'name' => 'DriveED Instructor 2',
                'contact' => '+63-919-777-0002',
                'password' => Hash::make('password123'),
                'license_number' => 'LIC-DH-TEST-002',
                'bio' => 'Test instructor account for demo purposes.',
                'status' => 'active',
                'availability' => 'available',
            ]
        );

        $this->command->info('   ✓ 4 Instructors created');

        // Create Courses
        $courses = $this->createDriveEdHubCourses($school);
        $this->command->info('   ✓ 4 Courses with packages created');

        // Create Students
        $students = $this->createDriveEdHubStudents($school);
        $this->command->info('   ✓ 10 Students created');

        // Create Time Slots, Bookings, and Payments
        $this->createTimeSlotsAndAssignments($school, $instructors, $courses);
        $this->createBookingsAndPayments($school, $students, $instructors, $courses);

        $this->command->info('   ✓ Time slots, bookings, and payments created');

        // Create guest students with enrollment requests
        $admins = Admin::where('school_id', $school->id)->where('role', 'school_admin')->get()->all();
        $guests = $this->createGuestsAndEnrollmentRequests($school, $courses, $admins);
        $this->command->info('   ✓ Guest students and enrollment requests created');

        // Create sample notifications
        $this->createSampleNotifications($school, $students, $instructors, $admins, $guests);
        $this->command->info('   ✓ Sample notifications created');
    }

    /**
     * Create courses for Smart Driving School
     */
    private function createSmartDrivingCourses(School $school): array
    {
        $courses = [];

        // Course 1: Practical Driving Course (Manual)
        $course1 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course (Manual)'],
            [
                'description' => 'Master manual transmission driving with comprehensive hands-on training. Perfect for those who want full vehicle control.',
                'type' => 'Practical',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'is_featured' => true,
                'features' => ['Manual Transmission', 'Clutch Control', 'Hill Start', 'Parking Techniques', 'Defensive Driving'],
            ]
        );
        $courses[] = $course1;

        CoursePackage::updateOrCreate(
            ['course_id' => $course1->id, 'name' => '10-Hour Package'],
            [
                'transmission_type' => 'manual',
                'vehicle_type' => 'Car',
                'training_hours' => 10,
                'price' => 5500.00,
                'description' => 'Basic manual driving course for beginners.',
            ]
        );

        CoursePackage::updateOrCreate(
            ['course_id' => $course1->id, 'name' => '15-Hour Package'],
            [
                'transmission_type' => 'manual',
                'vehicle_type' => 'Car',
                'training_hours' => 15,
                'price' => 7500.00,
                'description' => 'Complete manual driving course with advanced techniques.',
                'is_popular' => true,
            ]
        );

        // Course 2: Practical Driving Course (Automatic)
        $course2 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course (Automatic)'],
            [
                'description' => 'Learn to drive automatic transmission vehicles with confidence. Perfect for city driving.',
                'type' => 'Practical',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'is_featured' => true,
                'features' => ['Automatic Transmission', 'City Driving', 'Parking Techniques', 'Defensive Driving'],
            ]
        );
        $courses[] = $course2;

        CoursePackage::updateOrCreate(
            ['course_id' => $course2->id, 'name' => '8-Hour Package'],
            [
                'transmission_type' => 'automatic',
                'vehicle_type' => 'Car',
                'training_hours' => 8,
                'price' => 4800.00,
                'description' => 'Automatic driving course for beginners.',
                'is_popular' => true,
            ]
        );

        // Course 3: Theoretical Driving Course (TDC)
        $course3 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course (TDC)'],
            [
                'description' => 'Comprehensive road rules and traffic signs education. Required for LTO written exam.',
                'type' => 'Theoretical',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'features' => ['Traffic Rules', 'Road Signs', 'LTO Written Exam Prep', 'Certificate Included'],
            ]
        );
        $courses[] = $course3;

        CoursePackage::updateOrCreate(
            ['course_id' => $course3->id, 'name' => 'TDC 15-Hour Course'],
            [
                'transmission_type' => 'automatic',
                'vehicle_type' => 'Car',
                'training_hours' => 15,
                'price' => 1500.00,
                'description' => 'Complete TDC for LTO exam preparation.',
            ]
        );

        return $courses;
    }

    /**
     * Create courses for LySpeed Driving School
     */
    private function createLySpeedCourses(School $school): array
    {
        $courses = [];

        // Course 1: Basic Driving Course
        $course1 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Basic Driving Course'],
            [
                'description' => 'Affordable driving lessons for beginners. Learn the fundamentals of safe driving.',
                'type' => 'Practical',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'is_featured' => true,
                'features' => ['Basic Vehicle Control', 'Traffic Navigation', 'Parking Skills', 'Road Safety'],
            ]
        );
        $courses[] = $course1;

        CoursePackage::updateOrCreate(
            ['course_id' => $course1->id, 'name' => '8-Hour Starter'],
            [
                'transmission_type' => 'automatic',
                'vehicle_type' => 'Car',
                'training_hours' => 8,
                'price' => 4000.00,
                'description' => 'Beginner automatic driving course.',
            ]
        );

        CoursePackage::updateOrCreate(
            ['course_id' => $course1->id, 'name' => '12-Hour Complete'],
            [
                'transmission_type' => 'automatic',
                'vehicle_type' => 'Car',
                'training_hours' => 12,
                'price' => 5500.00,
                'description' => 'Complete automatic driving course.',
                'is_popular' => true,
            ]
        );

        // Course 2: Motorcycle Riding Course
        $course2 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Motorcycle Riding Course'],
            [
                'description' => 'Learn to ride motorcycles safely. Includes scooter and manual motorcycle training.',
                'type' => 'Practical',
                'vehicle_type' => 'Motorcycle',
                'status' => 'active',
                'features' => ['Balance Training', 'Gear Shifting', 'Defensive Riding', 'License Preparation'],
            ]
        );
        $courses[] = $course2;

        CoursePackage::updateOrCreate(
            ['course_id' => $course2->id, 'name' => '6-Hour Package'],
            [
                'transmission_type' => 'manual',
                'vehicle_type' => 'Motorcycle',
                'training_hours' => 6,
                'price' => 3000.00,
                'description' => 'Motorcycle riding fundamentals.',
            ]
        );

        // Course 3: Theoretical Driving Course
        $course3 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course (TDC)'],
            [
                'description' => 'LTO-accredited theoretical driving course covering traffic rules and regulations.',
                'type' => 'Theoretical',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'features' => ['Traffic Rules', 'Road Signs', 'LTO Accredited', 'Certificate'],
            ]
        );
        $courses[] = $course3;

        CoursePackage::updateOrCreate(
            ['course_id' => $course3->id, 'name' => 'TDC 15-Hour'],
            [
                'transmission_type' => 'automatic',
                'vehicle_type' => 'Car',
                'training_hours' => 15,
                'price' => 1200.00,
                'description' => 'Complete TDC for LTO written exam.',
            ]
        );

        return $courses;
    }

    /**
     * Create courses for DriveED Hub School
     */
    private function createDriveEdHubCourses(School $school): array
    {
        $courses = [];

        // Course 1: Theoretical Training - Non-Professional
        $course1 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Training (Non-Professional)'],
            [
                'description' => 'Complete theoretical training covering traffic rules, road safety, vehicle operation basics, and defensive driving principles.',
                'type' => 'Theoretical',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'is_featured' => true,
                'features' => ['Traffic Rules', 'Road Signs', 'Vehicle Basics', 'Defensive Driving'],
            ]
        );
        $courses[] = $course1;

        CoursePackage::updateOrCreate(
            ['course_id' => $course1->id, 'name' => 'TDC Non-Pro 15-Hour'],
            [
                'transmission_type' => 'automatic',
                'vehicle_type' => 'Car',
                'training_hours' => 15,
                'price' => 3500.00,
                'description' => 'Complete TDC for non-professional license.',
            ]
        );

        // Course 2: Theoretical Training - Professional
        $course2 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Training (Professional)'],
            [
                'description' => 'Advanced theoretical training for professional drivers including commercial vehicle regulations and safety standards.',
                'type' => 'Theoretical',
                'vehicle_type' => 'Commercial',
                'status' => 'active',
                'is_featured' => true,
                'features' => ['Advanced Traffic Rules', 'Commercial Regulations', 'Safety Standards', 'Professional License Prep'],
            ]
        );
        $courses[] = $course2;

        CoursePackage::updateOrCreate(
            ['course_id' => $course2->id, 'name' => 'TDC Pro 20-Hour'],
            [
                'transmission_type' => 'manual',
                'vehicle_type' => 'Commercial',
                'training_hours' => 20,
                'price' => 4500.00,
                'description' => 'Complete TDC for professional license.',
            ]
        );

        // Course 3: Practical Driving - Non-Professional
        $course3 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving (Non-Professional)'],
            [
                'description' => 'Hands-on driving training with certified instructors. Learn vehicle control, parking, road navigation, and prepare for the practical driving test.',
                'type' => 'Practical',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'features' => ['Vehicle Control', 'Parking', 'Road Navigation', 'Practical Test Prep'],
            ]
        );
        $courses[] = $course3;

        CoursePackage::updateOrCreate(
            ['course_id' => $course3->id, 'name' => 'PDC Non-Pro 20-Hour'],
            [
                'transmission_type' => 'automatic',
                'vehicle_type' => 'Car',
                'training_hours' => 20,
                'price' => 8500.00,
                'description' => 'Complete practical driving course.',
                'is_popular' => true,
            ]
        );

        // Course 4: Practical Driving - Professional
        $course4 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving (Professional)'],
            [
                'description' => 'Professional driving training for commercial vehicles with experienced instructors.',
                'type' => 'Practical',
                'vehicle_type' => 'Commercial',
                'status' => 'active',
                'features' => ['Commercial Vehicle Control', 'Heavy Vehicle Operation', 'Safety Procedures', 'Professional Test Prep'],
            ]
        );
        $courses[] = $course4;

        CoursePackage::updateOrCreate(
            ['course_id' => $course4->id, 'name' => 'PDC Pro 30-Hour'],
            [
                'transmission_type' => 'manual',
                'vehicle_type' => 'Commercial',
                'training_hours' => 30,
                'price' => 12000.00,
                'description' => 'Complete professional driving course.',
            ]
        );

        return $courses;
    }

    /**
     * Create students for Smart Driving School
     */
    private function createSmartDrivingStudents(School $school): array
    {
        $studentData = [
            ['name' => 'Sofia Angelica Reyes', 'email' => 'sofia.reyes@gmail.com'],
            ['name' => 'Luis Antonio Cruz', 'email' => 'luis.cruz@gmail.com'],
            ['name' => 'Isabella Marie Flores', 'email' => 'isabella.flores@gmail.com'],
            ['name' => 'Diego Rafael Torres', 'email' => 'diego.torres@gmail.com'],
            ['name' => 'Carmen Elena Ramos', 'email' => 'carmen.ramos@gmail.com'],
            ['name' => 'Rafael Jose Mendoza', 'email' => 'rafael.mendoza@gmail.com'],
            ['name' => 'Valentina Rose Garcia', 'email' => 'valentina.garcia@gmail.com'],
            ['name' => 'Gabriel Miguel Lopez', 'email' => 'gabriel.lopez@gmail.com'],
            ['name' => 'Lucia Patricia Perez', 'email' => 'lucia.perez@gmail.com'],
            ['name' => 'Daniel Carlos Rivera', 'email' => 'daniel.rivera@gmail.com'],
            ['name' => 'Angela Maria Castro', 'email' => 'angela.castro@gmail.com'],
            ['name' => 'Roberto Francisco Ortiz', 'email' => 'roberto.ortiz@gmail.com'],
            ['name' => 'Cristina Grace Martinez', 'email' => 'cristina.martinez@gmail.com'],
            ['name' => 'Fernando Jose Fernandez', 'email' => 'fernando.fernandez@gmail.com'],
        ];

        $students = [];
        foreach ($studentData as $s) {
            $enrollmentDate = now()->subDays(rand(7, 90));
            $students[] = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => $s['email']],
                [
                    'name' => $s['name'],
                    'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'password' => Hash::make('password123'),
                    'status' => 'active',
                    'role' => 'student',
                    'enrollment_date' => $enrollmentDate,
                ]
            );
        }

        // Test Student Account
        $students[] = Student::updateOrCreate(
            ['school_id' => $school->id, 'email' => 'student@gmail.com'],
            [
                'name' => 'Demo Student',
                'contact' => '+63-900-000-0001',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'role' => 'student',
                'enrollment_date' => now()->subDays(30),
            ]
        );

        return $students;
    }

    /**
     * Create students for LySpeed Driving School
     */
    private function createLySpeedStudents(School $school): array
    {
        $studentData = [
            ['name' => 'Maria Josephine Rodriguez', 'email' => 'maria.rodriguez@gmail.com'],
            ['name' => 'Antonio Rafael Hernandez', 'email' => 'antonio.hernandez@gmail.com'],
            ['name' => 'Teresa Lyn Jimenez', 'email' => 'teresa.jimenez@gmail.com'],
            ['name' => 'Marco Paulo Ruiz', 'email' => 'marco.ruiz@gmail.com'],
            ['name' => 'Laura Anne Sanchez', 'email' => 'laura.sanchez@gmail.com'],
            ['name' => 'Pedro Miguel Ramirez', 'email' => 'pedro.ramirez@gmail.com'],
            ['name' => 'Ana Christina Alvarez', 'email' => 'ana.alvarez@gmail.com'],
            ['name' => 'Jorge Luis Medina', 'email' => 'jorge.medina@gmail.com'],
            ['name' => 'Beatriz Elena Navarro', 'email' => 'beatriz.navarro@gmail.com'],
        ];

        $students = [];
        foreach ($studentData as $s) {
            $enrollmentDate = now()->subDays(rand(7, 60));
            $students[] = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => $s['email']],
                [
                    'name' => $s['name'],
                    'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'password' => Hash::make('password123'),
                    'status' => 'active',
                    'role' => 'student',
                    'enrollment_date' => $enrollmentDate,
                ]
            );
        }

        // Test Student Account
        $students[] = Student::updateOrCreate(
            ['school_id' => $school->id, 'email' => 'lyspeed.student@gmail.com'],
            [
                'name' => 'LySpeed Demo Student',
                'contact' => '+63-918-999-0001',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'role' => 'student',
                'enrollment_date' => now()->subDays(30),
            ]
        );

        return $students;
    }

    /**
     * Create students for DriveED Hub School
     */
    private function createDriveEdHubStudents(School $school): array
    {
        $studentData = [
            ['name' => 'Juan Miguel Dela Cruz', 'email' => 'student1@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Maria Victoria Garcia', 'email' => 'student2@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Pedro Jose Santos', 'email' => 'student3@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Ana Patricia Reyes', 'email' => 'student4@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Carlos Manuel Mendoza', 'email' => 'student5@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Sofia Angelica Torres', 'email' => 'student6@gmail.com', 'level' => 'experienced'],
            ['name' => 'Miguel Francisco Ramos', 'email' => 'student7@gmail.com', 'level' => 'experienced'],
            ['name' => 'Isabella Rose Cruz', 'email' => 'student8@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Diego Emmanuel Fernandez', 'email' => 'student9@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Luna Marie Martinez', 'email' => 'student10@gmail.com', 'level' => 'experienced'],
        ];

        $students = [];
        foreach ($studentData as $s) {
            $enrollmentDate = now()->subDays(rand(7, 60));
            $students[] = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => $s['email']],
                [
                    'name' => $s['name'],
                    'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'password' => Hash::make('password123'),
                    'status' => 'active',
                    'role' => 'student',
                    'experience_level' => $s['level'],
                    'enrollment_date' => $enrollmentDate,
                ]
            );
        }

        return $students;
    }

    /**
     * Create time slots and assign instructors
     */
    private function createTimeSlotsAndAssignments(School $school, array $instructors, array $courses): void
    {
        $times = [
            ['08:00:00', '09:00:00'],
            ['09:00:00', '10:00:00'],
            ['10:00:00', '11:00:00'],
            ['11:00:00', '12:00:00'],
            ['13:00:00', '14:00:00'],
            ['14:00:00', '15:00:00'],
            ['15:00:00', '16:00:00'],
            ['16:00:00', '17:00:00'],
        ];

        // Create slots for next 14 days
        for ($day = 0; $day < 14; $day++) {
            $date = now()->addDays($day)->format('Y-m-d');
            $dayOfWeek = now()->addDays($day)->dayOfWeek;
            
            // Skip Sundays
            if ($dayOfWeek == 0) continue;

            // Create time slots for each course
            foreach ($courses as $course) {
                // Create 4-6 random slots per day per course
                $slotCount = rand(4, min(6, count($times)));
                $daySlots = array_rand($times, $slotCount);
                if (!is_array($daySlots)) $daySlots = [$daySlots];
                
                foreach ($daySlots as $slotIndex) {
                    $timeSlot = TimeSlot::create([
                        'school_id' => $school->id,
                        'course_id' => $course->id,
                        'date' => $date,
                        'start_time' => $times[$slotIndex][0],
                        'end_time' => $times[$slotIndex][1],
                        'status' => 'open',
                        'max_instructors' => 1,
                    ]);

                    // Assign a random instructor to this time slot
                    if (!empty($instructors)) {
                        $instructor = $instructors[array_rand($instructors)];
                        ScheduleInstructor::create([
                            'time_slot_id' => $timeSlot->id,
                            'instructor_id' => $instructor->id,
                            'school_id' => $school->id,
                            'assignment_type' => 'admin_assigned',
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Create bookings and payments
     */
    private function createBookingsAndPayments(School $school, array $students, array $instructors, array $courses): void
    {
        if (empty($students) || empty($instructors) || empty($courses)) {
            return;
        }

        // Create 8-12 bookings per school with varied statuses
        $bookingCount = rand(8, 12);
        $statuses = ['confirmed', 'confirmed', 'confirmed', 'completed', 'completed', 'pending', 'cancelled'];
        
        for ($i = 0; $i < $bookingCount; $i++) {
            $student = $students[array_rand($students)];
            $instructor = $instructors[array_rand($instructors)];
            $course = $courses[array_rand($courses)];
            $package = CoursePackage::where('course_id', $course->id)->inRandomOrder()->first();
            
            if (!$package) continue;

            $status = $statuses[array_rand($statuses)];
            $bookingDate = $status == 'completed' 
                ? now()->subDays(rand(1, 30)) 
                : now()->addDays(rand(1, 14));

            $booking = Booking::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'instructor_id' => $instructor->id,
                'course_id' => $course->id,
                'package_id' => $package->id,
                'scheduled_at' => $bookingDate,
                'booking_date' => $bookingDate,
                'status' => $status,
                'payment_status' => $status == 'completed' ? 'paid' : 'pending',
                'total_amount' => $package->price,
                'notes' => $status == 'cancelled' ? 'Student requested cancellation' : null,
                'cancelled_by' => $status == 'cancelled' ? 'student' : null,
                'cancellation_reason' => $status == 'cancelled' ? 'Personal reasons' : null,
                'cancelled_at' => $status == 'cancelled' ? now() : null,
                'attendance_status' => $status == 'completed' ? 'attended' : null,
                'session_status' => $status == 'completed' ? 'completed' : null,
            ]);

            // Create payment for completed bookings
            if ($status == 'completed') {
                Payment::create([
                    'school_id' => $school->id,
                    'booking_id' => $booking->id,
                    'amount' => $package->price,
                    'paid_on' => $bookingDate,
                    'method' => ['cash', 'gcash', 'bank_transfer'][rand(0, 2)],
                    'status' => 'completed',
                ]);
            }

            // Create/update progress for completed bookings
            if ($status == 'completed') {
                $existingProgress = Progress::where('student_id', $student->id)
                    ->where('course_id', $course->id)
                    ->first();
                    
                if ($existingProgress) {
                    $existingProgress->update([
                        'completion_percent' => min(100, $existingProgress->completion_percent + rand(10, 25)),
                        'last_updated' => now(),
                        'notes' => 'Good progress, student is learning well.',
                    ]);
                } else {
                    Progress::create([
                        'school_id' => $school->id,
                        'student_id' => $student->id,
                        'course_id' => $course->id,
                        'completion_percent' => rand(10, 40),
                        'last_updated' => now(),
                        'notes' => 'Good progress, student is learning well.',
                    ]);
                }
            }
        }
    }

    /**
     * Create guest students with enrollment requests for a school
     */
    private function createGuestsAndEnrollmentRequests(School $school, array $courses, array $admins): array
    {
        $slug = $school->slug;
        $guestData = [
            [
                'name' => 'Elena Joy Reyes',
                'email' => "guest1@{$slug}.test",
                'license_status' => 'none',
                'enrollment_status' => 'pending',
            ],
            [
                'name' => 'Mark Anthony Dizon',
                'email' => "guest2@{$slug}.test",
                'license_status' => 'pending',
                'enrollment_status' => 'pending',
            ],
            [
                'name' => 'Jamie Lyn Pascual',
                'email' => "guest3@{$slug}.test",
                'license_status' => 'verified',
                'enrollment_status' => 'rejected',
            ],
            [
                'name' => 'Carlo Miguel Bautista',
                'email' => "guest4@{$slug}.test",
                'license_status' => 'none',
                'enrollment_status' => null, // no enrollment request yet
            ],
        ];

        $guests = [];
        foreach ($guestData as $g) {
            $guest = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => $g['email']],
                [
                    'name' => $g['name'],
                    'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'password' => Hash::make('password123'),
                    'status' => 'active',
                    'role' => 'guest',
                    'student_license_status' => $g['license_status'],
                    'student_license_verified_at' => $g['license_status'] === 'verified' ? now()->subDays(5) : null,
                ]
            );
            $guests[] = $guest;

            // Create enrollment request if needed
            if ($g['enrollment_status'] && !empty($courses)) {
                $course = $courses[array_rand($courses)];
                $enrollmentData = [
                    'school_id' => $school->id,
                    'learner_id' => $guest->id,
                    'course_id' => $course->id,
                    'status' => $g['enrollment_status'],
                    'payment_status' => 'pending',
                    'experience_level' => 'new_driver',
                    'requested_license_type' => $course->license_type ?? 'non_professional',
                ];

                if ($g['enrollment_status'] === 'rejected') {
                    $enrollmentData['remarks'] = 'Incomplete documentation. Please re-submit with valid credentials.';
                }

                if ($g['enrollment_status'] === 'approved' && !empty($admins)) {
                    $admin = $admins[array_rand($admins)];
                    $enrollmentData['approved_by'] = $admin->id;
                    $enrollmentData['approved_at'] = now()->subDays(2);
                    $enrollmentData['enrolled_at'] = now()->subDays(2);
                }

                EnrollmentRequest::updateOrCreate(
                    ['school_id' => $school->id, 'learner_id' => $guest->id, 'course_id' => $course->id],
                    $enrollmentData
                );
            }
        }

        return $guests;
    }

    /**
     * Create sample notifications for various users
     */
    private function createSampleNotifications(School $school, array $students, array $instructors, array $admins, array $guests): void
    {
        $slug = $school->slug;

        // Notifications for admins
        if (!empty($admins)) {
            $admin = $admins[0];
            Notification::send(
                $admin,
                'new_enrollment_request',
                'New Enrollment Request',
                'Elena Joy Reyes has requested enrollment in a driving course.',
                'enrollment',
                "/{$slug}/admin/enrollments"
            );
            Notification::send(
                $admin,
                'license_uploaded',
                'License Pending Review',
                'Mark Anthony Dizon has uploaded a student driver\'s license for verification.',
                'license',
                "/{$slug}/admin/enrollments"
            );
            Notification::send(
                $admin,
                'new_enrollment_request',
                'New Enrollment Request',
                'Jamie Lyn Pascual has requested enrollment in a PDC course.',
                'enrollment',
                "/{$slug}/admin/enrollments"
            );
        }

        // Notifications for students
        if (!empty($students)) {
            $student = $students[0];
            Notification::send(
                $student,
                'enrollment_approved',
                'Enrollment Approved!',
                'Your enrollment has been approved. Welcome aboard!',
                'success',
                "/{$slug}/student"
            );
            Notification::send(
                $student,
                'session_reminder',
                'Upcoming Session',
                'You have a driving session tomorrow. Don\'t forget to bring your license!',
                'session',
                "/{$slug}/student/schedule"
            );

            if (count($students) > 1) {
                Notification::send(
                    $students[1],
                    'session_reminder',
                    'Session Tomorrow',
                    'Reminder: You have a practical driving session scheduled for tomorrow morning.',
                    'session',
                    "/{$slug}/student/schedule"
                );
            }
        }

        // Notifications for instructors
        if (!empty($instructors)) {
            $instructor = $instructors[0];
            Notification::send(
                $instructor,
                'session_reminder',
                'Upcoming Session',
                'You have a driving session with a student tomorrow at 9:00 AM.',
                'session',
                "/{$slug}/instructor/my-schedule"
            );
        }

        // Notifications for guests
        if (!empty($guests)) {
            Notification::send(
                $guests[0],
                'enrollment_received',
                'Enrollment Request Submitted',
                'Your enrollment request has been submitted and is under review.',
                'enrollment',
                "/{$slug}/guest/enrollment-requests"
            );

            if (count($guests) > 2) {
                Notification::send(
                    $guests[2],
                    'enrollment_rejected',
                    'Enrollment Request Update',
                    'Your enrollment request was not approved. Check your email for details.',
                    'warning',
                    "/{$slug}/guest/enrollment-requests"
                );
                Notification::send(
                    $guests[2],
                    'license_verified',
                    'License Verified!',
                    'Your student driver\'s license has been verified. You can now enroll in PDC courses.',
                    'success',
                    "/{$slug}/guest/courses"
                );
            }
        }
    }

    /**
     * Print credentials summary
     */
    private function printCredentialsSummary(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════╗');
        $this->command->info('║                    LOGIN CREDENTIALS                          ║');
        $this->command->info('╚══════════════════════════════════════════════════════════════╝');
        $this->command->info('');
        $this->command->info('🔐 SYSTEM ADMINISTRATORS (Platform Level)');
        $this->command->info('   ┌─────────────────────────────────────────────────────────┐');
        $this->command->info('   │ Email: systemadmin@gmail.com   │ Password: sysadmin123! │');
        $this->command->info('   │ Email: systemadmin2@gmail.com  │ Password: sysadmin123! │');
        $this->command->info('   └─────────────────────────────────────────────────────────┘');
        $this->command->info('   URL: /system-admin');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('🏫 SMART DRIVING SCHOOL (slug: smart-driving)');
        $this->command->info('   ┌─────────────────────────────────────────────────────────┐');
        $this->command->info('   │ ADMIN:      schooladmin@gmail.com / password123         │');
        $this->command->info('   │ INSTRUCTOR: instructor@gmail.com / password123          │');
        $this->command->info('   │ STUDENT:    student@gmail.com / password123             │');
        $this->command->info('   └─────────────────────────────────────────────────────────┘');
        $this->command->info('   URL: /smart-driving');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('🏫 LYSPEED DRIVING SCHOOL (slug: lyspeed-driving)');
        $this->command->info('   ┌─────────────────────────────────────────────────────────┐');
        $this->command->info('   │ ADMIN:      lyspeed.admin@gmail.com / password123       │');
        $this->command->info('   │ INSTRUCTOR: lyspeed.instructor@gmail.com / password123  │');
        $this->command->info('   │ STUDENT:    lyspeed.student@gmail.com / password123     │');
        $this->command->info('   └─────────────────────────────────────────────────────────┘');
        $this->command->info('   URL: /lyspeed-driving');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('🏫 DRIVED HUB DRIVING SCHOOL (slug: drived-hub)');
        $this->command->info('   ┌─────────────────────────────────────────────────────────┐');
        $this->command->info('   │ ADMIN:      admin@gmail.com / password123               │');
        $this->command->info('   │ INSTRUCTOR: instructor1@gmail.com / password123         │');
        $this->command->info('   │ INSTRUCTOR: instructor2@gmail.com / password123         │');
        $this->command->info('   │ STUDENTS:   student1-10@gmail.com / password123         │');
        $this->command->info('   └─────────────────────────────────────────────────────────┘');
        $this->command->info('   URL: /drived-hub');
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════╗');
        $this->command->info('║              ✓ UNIFIED SEEDER COMPLETED!                     ║');
        $this->command->info('╚══════════════════════════════════════════════════════════════╝');
        $this->command->info('');
    }
}
