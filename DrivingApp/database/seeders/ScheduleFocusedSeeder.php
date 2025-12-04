<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
use App\Models\EnrollmentRequest;
use App\Models\RegistrationRequest;
use App\Models\InstructorRemovalRequest;
use App\Models\Progress;
use App\Models\Report;
use Carbon\Carbon;

class ScheduleFocusedSeeder extends Seeder
{
    private $instructorCourseMapping = [];
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create two schools for testing
        $schools = [
            [
                'slug' => 'elite-driving',
                'name' => 'Elite Driving Academy',
                'timezone' => 'Asia/Manila',
                'instructor_removal_notice_days' => 7,
                'colors' => [
                    'primary_color' => '#667eea',
                    'secondary_color' => '#764ba2',
                    'accent_color' => '#5568d3',
                ],
                'use_gradient' => true,
                'enable_booking_queue' => true,
                'booking_queue_days' => 3,
                'advance_booking_days' => 0,
            ],
            [
                'slug' => 'smart-driving',
                'name' => 'Smart Driving School',
                'timezone' => 'Asia/Manila',
                'instructor_removal_notice_days' => 7,
                'colors' => [
                    'primary_color' => '#2563eb',
                    'secondary_color' => '#eab308',
                    'accent_color' => '#1d4ed8',
                ],
                'use_gradient' => false,
                'enable_booking_queue' => true,
                'booking_queue_days' => 3,
                'advance_booking_days' => 0,
            ]
        ];

        foreach ($schools as $schoolData) {
            $this->command->info("Creating {$schoolData['name']}...");
            
            $school = School::firstOrCreate(
                ['slug' => $schoolData['slug']],
                [
                    'name' => $schoolData['name'],
                    'timezone' => $schoolData['timezone'],
                    'instructor_removal_notice_days' => $schoolData['instructor_removal_notice_days'],
                ]
            );

            // Create test accounts for easy login
            $testAdmin = Admin::firstOrCreate(
                ['school_id' => $school->id, 'email' => 'admin@gmail.com'],
                [
                    'name' => 'Admin User',
                    'password' => Hash::make('password123'),
                    'role' => 'school_admin',
                ]
            );

            $testInstructor = Instructor::firstOrCreate(
                ['school_id' => $school->id, 'email' => 'instructor@gmail.com'],
                [
                    'name' => 'Instructor User',
                    'password' => Hash::make('password123'),
                    'license_number' => 'INST-' . $school->id . '-001',
                    'status' => 'active',
                    'availability' => 'available',
                    'contact' => '+63-917-123-4567',
                ]
            );

            $testStudent = Student::firstOrCreate(
                ['school_id' => $school->id, 'email' => 'student@gmail.com'],
                [
                    'name' => 'Student User',
                    'password' => Hash::make('password123'),
                    'status' => 'active',
                    'role' => 'student',
                    'enrollment_date' => now()->subMonths(2),
                    'contact' => '+63-918-987-6543',
                ]
            );

            // Create school settings with custom colors
            SchoolSetting::updateOrCreate(
                ['school_id' => $school->id],
                [
                    'primary_color' => $schoolData['colors']['primary_color'],
                    'secondary_color' => $schoolData['colors']['secondary_color'],
                    'accent_color' => $schoolData['colors']['accent_color'],
                    'background_type' => 'color',
                    'background_color' => '#f5f5f5',
                    'sidebar_bg_color' => '#ffffff',
                    'sidebar_text_color' => '#333333',
                    'sidebar_hover_color' => '#f5f5f5',
                    'use_gradient_header' => $schoolData['use_gradient'] ?? true,
                    'header_text_color' => '#ffffff',
                    'login_header_layout' => 'horizontal',
                    'login_show_school_name' => true,
                    'login_school_name_position' => 'left',
                    'login_school_name_size' => 24,
                    'login_welcome_text' => 'Welcome!',
                    'login_show_welcome_text' => true,
                    'login_welcome_position' => 'right',
                    'login_welcome_size' => 16,
                    'login_header_bg_type' => 'gradient',
                    'login_header_height' => 60,
                    'login_header_text_color' => '#ffffff',
                    'login_header_shadow' => true,
                    'register_welcome_text' => 'Student Registration',
                    'calendar_day_border' => '#dee2e6',
                    'calendar_day_hover' => $schoolData['colors']['primary_color'],
                    'calendar_today_color' => $schoolData['colors']['primary_color'],
                    'button_primary_bg' => $schoolData['colors']['primary_color'],
                    'button_primary_text' => '#ffffff',
                    'button_secondary_bg' => '#6c757d',
                    'button_secondary_text' => '#ffffff',
                    'button_success_bg' => '#28a745',
                    'button_success_text' => '#ffffff',
                    'button_danger_bg' => '#dc3545',
                    'button_danger_text' => '#ffffff',
                    'border_radius' => 8,
                    'button_border_radius' => 8,
                    'button_style' => 'solid',
                    'modal_header_bg' => $schoolData['colors']['primary_color'],
                    'modal_header_text' => '#ffffff',
                    'modal_border_color' => $schoolData['colors']['primary_color'],
                    'card_border_color' => '#e5e7eb',
                    'card_header_bg' => '#f9fafb',
                    'page_header_border' => $schoolData['colors']['primary_color'],
                    'badge_pending_bg' => '#fbbf24',
                    'badge_pending_text' => '#78350f',
                    'badge_approved_bg' => '#10b981',
                    'badge_approved_text' => '#065f46',
                    'badge_cancelled_bg' => '#ef4444',
                    'badge_cancelled_text' => '#7f1d1d',
                    'instructor_selection_mode' => 'auto_assign',
                    'enable_booking_queue' => $schoolData['enable_booking_queue'] ?? true,
                    'booking_queue_days' => $schoolData['booking_queue_days'] ?? 3,
                    'advance_booking_days' => $schoolData['advance_booking_days'] ?? 0,
                    'login_page_bg_type' => 'color',
                    'login_page_bg_color' => '#f5f5f5',
                    'login_page_bg_opacity' => 100,
                ]
            );

            $this->seedSchoolData($school, $schoolData);
            
            // Create connected test data for test accounts (admin, instructor, student)
            $this->seedTestAccountConnections($school, $testAdmin, $testInstructor, $testStudent);
        }

        $this->command->info('');
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('Welcome Page: http://localhost:8000/');
        $this->command->info('System Admin Login: http://localhost:8000/system-admin/login');
        $this->command->info('');
        $this->command->info('System Admin (Access All Schools):');
        $this->command->info('  Email: carlos.rodriguez.admin@gmail.com');
        $this->command->info('  Password: password123');
        $this->command->info('');
        $this->command->info('School 1 - Elite Driving Academy:');
        $this->command->info('  URL: http://localhost:8000/elite-driving');
        $this->command->info('  Admin 1: maria.santos.school@gmail.com / password123');
        $this->command->info('  Admin 2: patricia.gonzales.school@gmail.com / password123');
        $this->command->info('');
        $this->command->info('School 2 - Smart Driving School:');
        $this->command->info('  URL: http://localhost:8000/smart-driving');
        $this->command->info('  Admin 1: john.dela.cruz.school@gmail.com / password123');
        $this->command->info('  Admin 2: mark.villanueva.school@gmail.com / password123');
        $this->command->info('');
        $this->command->info('=== TEST ACCOUNTS (Both Schools) ===');
        $this->command->info('  Admin: admin@gmail.com / password123');
        $this->command->info('  Instructor: instructor@gmail.com / password123');
        $this->command->info('  Student: student@gmail.com / password123');
        $this->command->info('');
        $this->command->info('=== STATISTICS ===');
        $this->command->info('Schools: ' . School::count());
        $this->command->info('Admins: ' . Admin::count());
        $this->command->info('Instructors: ' . Instructor::count());
        $this->command->info('Students: ' . Student::count());
        $this->command->info('Courses: ' . Course::count());
        $this->command->info('Schedules: ' . TimeSlot::count());
        $this->command->info('Bookings: ' . Booking::count());
    }

    private function seedSchoolData($school, $schoolData): void
    {
        $isFirstSchool = $schoolData['slug'] === 'elite-driving';
        
        // Create 3 Admins (System Admin only for first school, School Admin for each, Staff)
        if ($isFirstSchool) {
            $systemAdmin = Admin::create([
                'school_id' => $school->id,
                'name' => 'Carlos Rodriguez',
                'email' => 'carlos.rodriguez.admin@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'system_admin',
            ]);
        }

        $schoolAdmin = Admin::create([
            'school_id' => $school->id,
            'name' => $isFirstSchool ? 'Maria Santos' : 'John Dela Cruz',
            'email' => $isFirstSchool ? 'maria.santos.school@gmail.com' : 'john.dela.cruz.school@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'school_admin',
        ]);

        // Create second school admin for each school
        $schoolAdmin2 = Admin::create([
            'school_id' => $school->id,
            'name' => $isFirstSchool ? 'Patricia Gonzales' : 'Mark Villanueva',
            'email' => $isFirstSchool ? 'patricia.gonzales.school@gmail.com' : 'mark.villanueva.school@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'school_admin',
        ]);

        $staff = Admin::create([
            'school_id' => $school->id,
            'name' => $isFirstSchool ? 'Roberto Reyes' : 'Ana Martinez',
            'email' => $isFirstSchool ? 'roberto.reyes.staff@gmail.com' : 'ana.martinez.staff@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
        ]);

        // Create 6 Instructors per school - Different for each school
        $instructors = $isFirstSchool ? [
            ['name' => 'Juan Dela Cruz', 'email' => 'juan.delacruz.instructor@gmail.com', 'contact' => '+63-917-111-2222'],
            ['name' => 'Maria Garcia', 'email' => 'maria.garcia.instructor@gmail.com', 'contact' => '+63-917-222-3333'],
            ['name' => 'Pedro Martinez', 'email' => 'pedro.martinez.instructor@gmail.com', 'contact' => '+63-917-333-4444'],
            ['name' => 'Ana Lopez', 'email' => 'ana.lopez.instructor@gmail.com', 'contact' => '+63-917-444-5555'],
            ['name' => 'Carlos Fernandez', 'email' => 'carlos.fernandez.instructor@gmail.com', 'contact' => '+63-917-555-6666'],
            ['name' => 'Rosa Villanueva', 'email' => 'rosa.villanueva.instructor@gmail.com', 'contact' => '+63-917-666-7777'],
        ] : [
            ['name' => 'Ricardo Santos', 'email' => 'ricardo.santos.instructor@gmail.com', 'contact' => '+63-918-111-2222'],
            ['name' => 'Elena Ramos', 'email' => 'elena.ramos.instructor@gmail.com', 'contact' => '+63-918-222-3333'],
            ['name' => 'Fernando Cruz', 'email' => 'fernando.cruz.instructor@gmail.com', 'contact' => '+63-918-333-4444'],
            ['name' => 'Linda Flores', 'email' => 'linda.flores.instructor@gmail.com', 'contact' => '+63-918-444-5555'],
            ['name' => 'Antonio Mendez', 'email' => 'antonio.mendez.instructor@gmail.com', 'contact' => '+63-918-555-6666'],
            ['name' => 'Teresa Castillo', 'email' => 'teresa.castillo.instructor@gmail.com', 'contact' => '+63-918-666-7777'],
        ];

        $createdInstructors = [];
        
        // Store course IDs for later assignment (courses will be created after instructors)
        $this->instructorCourseMapping[$school->id] = [];
        
        foreach ($instructors as $index => $instructor) {
            $createdInstructors[] = Instructor::create(array_merge($instructor, [
                'school_id' => $school->id,
                'password' => Hash::make('password123'),
                'license_number' => 'LIC-' . rand(100000, 999999),
                'status' => 'active',
                // Course specializations will be set after courses are created
                'course_specializations' => null,
            ]));
            
            // Store instructor index for later course assignment
            $this->instructorCourseMapping[$school->id][$index] = $createdInstructors[$index]->id;
        }

        // Create 40-80 Students per school (random)
        $studentCount = rand(40, 80);
        $this->command->info("  Creating {$studentCount} students...");
        
        // Filipino name pools
        $firstNames = ['Miguel', 'Sofia', 'Luis', 'Isabella', 'Diego', 'Carmen', 'Rafael', 'Valentina', 'Gabriel', 'Lucia',
                       'Daniel', 'Angela', 'Roberto', 'Cristina', 'Fernando', 'Patricia', 'Eduardo', 'Monica', 'Alberto', 'Sandra',
                       'Jose', 'Ana', 'Manuel', 'Elena', 'Antonio', 'Rosa', 'Juan', 'Teresa', 'Carlos', 'Laura',
                       'Francisco', 'Mariana', 'Javier', 'Beatriz', 'Ramon', 'Catalina', 'Enrique', 'Dolores', 'Vicente', 'Gloria',
                       'Rodrigo', 'Pilar', 'Alejandro', 'Mercedes', 'Sergio', 'Concepcion', 'Pablo', 'Rosario', 'Andres', 'Esperanza'];
        
        $lastNames = ['Santos', 'Reyes', 'Cruz', 'Morales', 'Flores', 'Torres', 'Ramos', 'Mendoza', 'Vargas', 'Diaz',
                      'Garcia', 'Lopez', 'Silva', 'Gomez', 'Perez', 'Rivera', 'Castro', 'Ortiz', 'Martinez', 'Fernandez',
                      'Gonzalez', 'Rodriguez', 'Hernandez', 'Jimenez', 'Ruiz', 'Sanchez', 'Ramirez', 'Alvarez', 'Medina', 'Navarro',
                      'Dominguez', 'Gutierrez', 'Moreno', 'Romero', 'Alonso', 'Delgado', 'Castillo', 'Iglesias', 'Nuñez', 'Marquez'];
        
        $createdStudents = [];
        $usedEmails = [];
        
        for ($i = 0; $i < $studentCount; $i++) {
            do {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $fullName = $firstName . ' ' . $lastName;
                $email = strtolower($firstName . '.' . $lastName . ($i + 1) . '.student@gmail.com');
            } while (in_array($email, $usedEmails));
            
            $usedEmails[] = $email;
            
            // Spread enrollments realistically across time periods showing growth:
            // 45% this month, 25% last month, 15% 2-3 months ago, 10% 4-6 months ago, 5% older
            $rand = rand(1, 100);
            if ($rand <= 45) {
                // This month (45%) - showing growth
                $enrollmentDate = now()->startOfMonth()->addDays(rand(0, now()->day));
            } elseif ($rand <= 70) {
                // Last month (25%)
                $enrollmentDate = now()->subMonth()->startOfMonth()->addDays(rand(0, 30));
            } elseif ($rand <= 85) {
                // 2-3 months ago (15%)
                $enrollmentDate = now()->subMonths(rand(2, 3))->startOfMonth()->addDays(rand(0, 28));
            } elseif ($rand <= 95) {
                // 4-6 months ago (10%)
                $enrollmentDate = now()->subMonths(rand(4, 6))->startOfMonth()->addDays(rand(0, 28));
            } else {
                // 7-12 months ago (5%)
                $enrollmentDate = now()->subMonths(rand(7, 12))->startOfMonth()->addDays(rand(0, 28));
            }
            
            $createdStudents[] = Student::create([
                'school_id' => $school->id,
                'name' => $fullName,
                'email' => $email,
                'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                'password' => Hash::make('password123'),
                'role' => 'student',
                'status' => rand(1, 100) <= 95 ? 'active' : 'inactive', // 95% active
                'address' => 'Metro Manila, Philippines',
                'enrollment_date' => $enrollmentDate,
            ]);
        }

        // Create Courses with Packages - Different for each school
        if ($isFirstSchool) {
            // Elite Driving Academy - Premium courses
            $course1 = Course::create([
                'school_id' => $school->id,
                'title' => "Professional Driving Mastery",
                'description' => 'Elite comprehensive driving course for complete beginners. Master the art of safe driving with our award-winning instructors.',
                'type' => 'Beginner',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'is_featured' => true,
                'features' => [
                    'UP TO 25 HOURS PREMIUM TRAINING',
                    'THEORY & PRACTICAL LESSONS',
                    'LTO REQUIREMENTS ASSISTANCE',
                    'FREE STUDENT PERMIT PROCESSING',
                    'FLEXIBLE SCHEDULING',
                    'ONE-ON-ONE COACHING',
                ],
                'sort_order' => 1,
            ]);
        } else {
            // Metro Drive School - Affordable courses
            $course1 = Course::create([
                'school_id' => $school->id,
                'title' => "Essential Driving Course",
                'description' => 'Practical and affordable driving course designed for beginners. Learn safe driving skills at competitive prices.',
                'type' => 'Beginner',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'is_featured' => true,
                'features' => [
                    'UP TO 20 HOURS TRAINING',
                    'THEORY & PRACTICAL LESSONS',
                    'LTO REQUIREMENTS ASSISTANCE',
                    'BUDGET-FRIENDLY PACKAGES',
                    'WEEKDAY & WEEKEND CLASSES',
                ],
                'sort_order' => 1,
            ]);
        }

        // Packages for Course 1 - Different pricing for each school
        if ($isFirstSchool) {
            // Elite Driving Academy - Higher prices, more hours
            CoursePackage::create([
                'course_id' => $course1->id,
                'name' => 'PLATINUM',
                'vehicle_type' => 'Sedan',
                'transmission_type' => 'manual',
                'price' => 16990.00,
                'training_hours' => 25,
                'description' => 'Premium package for sedan with manual transmission',
                'features' => [
                    '25 hours driving training',
                    'Theory classes included',
                    'LTO requirements assistance',
                    'Student permit processing',
                    'One-on-one coaching',
                ],
                'is_popular' => true,
                'sort_order' => 1,
            ]);

            CoursePackage::create([
                'course_id' => $course1->id,
                'name' => 'PLATINUM',
                'vehicle_type' => 'Sedan',
                'transmission_type' => 'automatic',
                'price' => 22990.00,
                'training_hours' => 25,
                'description' => 'Luxury package for sedan with automatic transmission',
                'features' => [
                    '25 hours driving training',
                    'Theory classes included',
                    'Premium vehicles',
                    'Personalized instruction',
                ],
                'is_popular' => false,
                'sort_order' => 2,
            ]);

            CoursePackage::create([
                'course_id' => $course1->id,
                'name' => 'EXECUTIVE',
                'vehicle_type' => 'SUV',
                'transmission_type' => 'automatic',
                'price' => 29990.00,
                'training_hours' => 30,
                'description' => 'Elite package with luxury SUV training',
                'features' => [
                    '30 hours comprehensive training',
                    'Luxury SUV experience',
                    'VIP treatment',
                    'Flexible scheduling',
                ],
                'is_popular' => false,
                'sort_order' => 3,
            ]);
        } else {
            // Metro Drive School - Budget-friendly prices
            CoursePackage::create([
                'course_id' => $course1->id,
                'name' => 'STANDARD',
                'vehicle_type' => 'Sedan',
                'transmission_type' => 'manual',
                'price' => 9990.00,
                'training_hours' => 20,
                'description' => 'Affordable package for sedan with manual transmission',
                'features' => [
                    '20 hours driving training',
                    'Theory classes included',
                    'LTO requirements assistance',
                    'Budget-friendly option',
                ],
                'is_popular' => true,
                'sort_order' => 1,
            ]);

            CoursePackage::create([
                'course_id' => $course1->id,
                'name' => 'STANDARD',
                'vehicle_type' => 'Sedan',
                'transmission_type' => 'automatic',
                'price' => 14990.00,
                'training_hours' => 20,
                'description' => 'Value package for sedan with automatic transmission',
                'features' => [
                    '20 hours driving training',
                    'Theory classes included',
                    'Great value for money',
                ],
                'is_popular' => false,
                'sort_order' => 2,
            ]);

            CoursePackage::create([
                'course_id' => $course1->id,
                'name' => 'EXPRESS',
                'vehicle_type' => 'Sedan',
                'transmission_type' => 'manual',
                'price' => 7990.00,
                'training_hours' => 12,
                'description' => 'Quick and affordable learning package',
                'features' => [
                    '12 hours intensive training',
                    'Fast-track program',
                    'Most affordable option',
                ],
                'is_popular' => false,
                'sort_order' => 3,
            ]);
        }

        // Course 2 - Different for each school
        if ($isFirstSchool) {
            // Elite: Defensive Driving Mastery
            $course2 = Course::create([
                'school_id' => $school->id,
                'title' => 'Defensive Driving Mastery',
                'description' => 'Master advanced defensive driving techniques with our expert instructors. Perfect for experienced drivers looking to enhance their skills.',
                'type' => 'Advanced',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'is_featured' => true,
                'features' => [
                    'DEFENSIVE DRIVING TECHNIQUES',
                    'HIGHWAY DRIVING MASTERY',
                    'EMERGENCY MANEUVERS',
                    'NIGHT DRIVING TRAINING',
                    'ADVERSE WEATHER CONDITIONS',
                    'ACCIDENT PREVENTION STRATEGIES',
                ],
                'sort_order' => 2,
            ]);

            CoursePackage::create([
                'course_id' => $course2->id,
                'name' => 'DEFENSIVE PRO',
                'vehicle_type' => 'Sedan',
                'transmission_type' => 'automatic',
                'price' => 24990.00,
                'training_hours' => 25,
                'description' => 'Professional defensive driving package',
                'features' => [
                    'All defensive techniques',
                    'Highway & city driving',
                    'Night driving sessions',
                    'Expert coaching',
                ],
                'is_popular' => true,
                'sort_order' => 1,
            ]);
        } else {
            // Metro: Refresher Course
            $course2 = Course::create([
                'school_id' => $school->id,
                'title' => 'Refresher Driving Course',
                'description' => 'Perfect for licensed drivers who need to brush up on their skills. Affordable and practical training sessions.',
                'type' => 'Refresher',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'is_featured' => false,
                'features' => [
                    'REVIEW OF BASIC SKILLS',
                    'CONFIDENCE BUILDING',
                    'ROAD RULES UPDATE',
                    'PRACTICAL ASSESSMENT',
                ],
                'sort_order' => 2,
            ]);

            CoursePackage::create([
                'course_id' => $course2->id,
                'name' => 'REFRESHER',
                'vehicle_type' => 'Sedan',
                'transmission_type' => 'manual',
                'price' => 5990.00,
                'training_hours' => 10,
                'description' => 'Quick refresher for experienced drivers',
                'features' => [
                    '10 hours review training',
                    'Confidence building',
                    'Most affordable refresher',
                ],
                'is_popular' => true,
                'sort_order' => 1,
            ]);
        }

        // Course 3 - Different for each school
        if ($isFirstSchool) {
            // Elite: SUV Driving Specialization
            $course3 = Course::create([
                'school_id' => $school->id,
                'title' => 'SUV Driving Specialization',
                'description' => 'Specialized training for large vehicle handling. Learn to master SUVs and crossovers with confidence.',
                'type' => 'Specialized',
                'vehicle_type' => 'SUV',
                'status' => 'active',
                'is_featured' => false,
                'features' => [
                    'LARGE VEHICLE HANDLING',
                    'PARKING MASTERY',
                    'HIGHWAY CONFIDENCE',
                    'SAFETY FEATURES TRAINING',
                    'LUXURY SUV EXPERIENCE',
                ],
                'sort_order' => 3,
            ]);

            CoursePackage::create([
                'course_id' => $course3->id,
                'name' => 'SUV MASTER',
                'vehicle_type' => 'SUV',
                'transmission_type' => 'automatic',
                'price' => 32990.00,
                'training_hours' => 20,
                'description' => 'Complete SUV driving mastery program',
                'features' => [
                    'Luxury SUV training',
                    'Parking techniques',
                    'Highway confidence',
                    'Premium instruction',
                ],
                'is_popular' => true,
                'sort_order' => 1,
            ]);
        } else {
            // Metro: Motorcycle Training
            $course3 = Course::create([
                'school_id' => $school->id,
                'title' => 'Motorcycle Basics',
                'description' => 'Affordable motorcycle training for beginners. Learn safe riding techniques on a budget.',
                'type' => 'Motorcycle',
                'vehicle_type' => 'Motorcycle',
                'status' => 'active',
                'is_featured' => false,
                'features' => [
                    'BASIC RIDING SKILLS',
                    'SAFETY GEAR TRAINING',
                    'BALANCE & CONTROL',
                    'CITY RIDING PRACTICE',
                    'BUDGET-FRIENDLY',
                ],
                'sort_order' => 3,
            ]);

            CoursePackage::create([
                'course_id' => $course3->id,
                'name' => 'MOTO BASIC',
                'vehicle_type' => 'Motorcycle',
                'transmission_type' => 'manual',
                'price' => 6990.00,
                'training_hours' => 12,
                'description' => 'Affordable motorcycle training for beginners',
                'features' => [
                    '12 hours basic training',
                    'Balance & control',
                    'Most affordable motorcycle course',
                    'City riding practice',
                ],
                'is_popular' => true,
                'sort_order' => 1,
            ]);
        }

        // Create 4 varying courses per school
        $allCourses = [$course1, $course2, $course3];
        
        // Add 4th course - Different for each school
        if ($isFirstSchool) {
            $course4 = Course::create([
                'school_id' => $school->id,
                'title' => 'Night Driving Mastery',
                'description' => 'Specialized training for night and low-visibility driving conditions.',
                'type' => 'Specialized',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'is_featured' => false,
                'features' => [
                    'NIGHT DRIVING TECHNIQUES',
                    'LOW VISIBILITY TRAINING',
                    'HEADLIGHT MANAGEMENT',
                    'DEFENSIVE NIGHT DRIVING',
                ],
                'sort_order' => 4,
            ]);

            CoursePackage::create([
                'course_id' => $course4->id,
                'name' => 'NIGHT OWL',
                'vehicle_type' => 'Sedan',
                'transmission_type' => 'automatic',
                'price' => 18990.00,
                'training_hours' => 15,
                'description' => 'Night driving specialization package',
                'features' => ['15 hours night training', 'Low visibility techniques', 'Safety focused'],
                'is_popular' => false,
                'sort_order' => 1,
            ]);
        } else {
            $course4 = Course::create([
                'school_id' => $school->id,
                'title' => 'Highway Driving Basics',
                'description' => 'Learn essential highway and expressway driving skills safely.',
                'type' => 'Intermediate',
                'vehicle_type' => 'Car',
                'status' => 'active',
                'is_featured' => false,
                'features' => [
                    'HIGHWAY BASICS',
                    'EXPRESSWAY TRAINING',
                    'LANE CHANGING',
                    'SAFE MERGING',
                ],
                'sort_order' => 4,
            ]);

            CoursePackage::create([
                'course_id' => $course4->id,
                'name' => 'HIGHWAY',
                'vehicle_type' => 'Sedan',
                'transmission_type' => 'manual',
                'price' => 8990.00,
                'training_hours' => 15,
                'description' => 'Affordable highway driving training',
                'features' => ['15 hours highway training', 'Expressway confidence', 'Budget-friendly'],
                'is_popular' => false,
                'sort_order' => 1,
            ]);
        }
        
        $allCourses[] = $course4;
        
        // Now assign course specializations to instructors
        // Get all course IDs
        $courseIds = array_map(fn($course) => $course->id, $allCourses);
        
        // Update test instructor@gmail.com to have all course specializations
        $testInstructor = Instructor::where('school_id', $school->id)
            ->where('email', 'instructor@gmail.com')
            ->first();
        if ($testInstructor) {
            $testInstructor->update(['course_specializations' => $courseIds]);
        }
        
        // Assign course specializations to regular instructors
        foreach ($this->instructorCourseMapping[$school->id] as $index => $instructorId) {
            $instructor = Instructor::find($instructorId);
            
            if ($index === 0) {
                // First instructor: qualified for all courses
                $instructor->update(['course_specializations' => $courseIds]);
            } elseif ($index === 1) {
                // Second instructor: qualified for first 2 courses
                $instructor->update(['course_specializations' => array_slice($courseIds, 0, 2)]);
            } elseif ($index === 2) {
                // Third instructor: qualified for last 2 courses
                $instructor->update(['course_specializations' => array_slice($courseIds, 2, 2)]);
            } elseif ($index === 3) {
                // Fourth instructor: qualified for course 1 and 3
                $instructor->update(['course_specializations' => [$courseIds[0], $courseIds[2]]]);
            } elseif ($index === 4) {
                // Fifth instructor: qualified for course 2 and 4
                $instructor->update(['course_specializations' => [$courseIds[1], $courseIds[3]]]);
            } else {
                // Sixth instructor: qualified for only one random course
                $instructor->update(['course_specializations' => [$courseIds[rand(0, 3)]]]);
            }
        }
        
        $this->command->info("  Assigned course specializations to instructors");
        
        // Create Random Schedules (Time Slots) for this week and next month
        $this->command->info("  Creating schedules and bookings...");
        // Start from beginning of this week
        $startDate = Carbon::now()->startOfWeek();
        // Generate for rest of this week + next month (approximately 35 days)
        $daysToGenerate = 35;
        
        $timeSlots = [
            ['start' => '07:00:00', 'end' => '09:00:00'],
            ['start' => '09:00:00', 'end' => '11:00:00'],
            ['start' => '11:00:00', 'end' => '13:00:00'],
            ['start' => '13:00:00', 'end' => '15:00:00'],
            ['start' => '15:00:00', 'end' => '17:00:00'],
            ['start' => '17:00:00', 'end' => '19:00:00'],
        ];
        
        $allSchedules = [];
        
        // Track student session counts to ensure realistic distribution (3-9 sessions per student)
        $studentSessionCounts = [];
        $studentEnrolledCourses = []; // Track which course each student is enrolled in
        
        foreach ($createdStudents as $student) {
            // Each student is enrolled in exactly ONE course (randomly chosen)
            $enrolledCourse = $allCourses[array_rand($allCourses)];
            $studentEnrolledCourses[$student->id] = $enrolledCourse;
            
            // Determine target sessions for this student (min 3, max 9)
            // 50% get 5-7 sessions, 30% get 3-4 sessions, 20% get 8-9 sessions
            $rand = rand(1, 100);
            if ($rand <= 50) {
                $targetSessions = rand(5, 7); // Most students
            } elseif ($rand <= 80) {
                $targetSessions = rand(3, 4); // Some students
            } else {
                $targetSessions = rand(8, 9); // Few students with more sessions
            }
            $studentSessionCounts[$student->id] = [
                'current' => 0,
                'target' => $targetSessions,
                'course_id' => $enrolledCourse->id,
            ];
            
            // Get a random package from the enrolled course
            $package = CoursePackage::where('course_id', $enrolledCourse->id)->first();
            
            // Create approved enrollment request for this student
            EnrollmentRequest::create([
                'school_id' => $school->id,
                'learner_id' => $student->id,
                'course_id' => $enrolledCourse->id,
                'status' => 'approved',
                'payment_status' => 'paid',
                'approved_by' => $schoolAdmin->id,
                'approved_at' => $student->enrollment_date,
                'remarks' => 'Enrollment approved',
            ]);
            
            // Create progress record for this student
            Progress::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'course_id' => $enrolledCourse->id,
                'completion_percent' => rand(10, 85),
                'notes' => 'In progress',
                'last_updated' => now()->subDays(rand(0, 10)),
            ]);
        }
        
        for ($day = 0; $day < $daysToGenerate; $day++) {
            $date = $startDate->copy()->addDays($day);
            
            // Skip Sundays
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }

            // Saturday: 2-4 slots, Weekdays: 4-6 slots
            $numSlots = $date->dayOfWeek === Carbon::SATURDAY ? rand(2, 4) : rand(4, 6);
            $usedTimeSlots = [];
            
            for ($i = 0; $i < $numSlots; $i++) {
                // Pick unique time slot for the day
                do {
                    $timeSlotIndex = array_rand($timeSlots);
                } while (in_array($timeSlotIndex, $usedTimeSlots));
                
                $usedTimeSlots[] = $timeSlotIndex;
                $timeSlot = $timeSlots[$timeSlotIndex];
                
                // Random course
                $course = $allCourses[array_rand($allCourses)];
                
                // Random status: 80% open, 20% closed
                $status = rand(1, 100) <= 80 ? 'open' : 'closed';
                
                // Random max students per schedule (for bookings)
                $maxStudents = rand(3, 8);
                
                $schedule = TimeSlot::create([
                    'school_id' => $school->id,
                    'course_id' => $course->id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => $timeSlot['start'],
                    'end_time' => $timeSlot['end'],
                    'max_instructors' => rand(2, 5),
                    'status' => $status,
                ]);
                
                $allSchedules[] = $schedule;
                
                // Assign 2-4 random instructors to the schedule
                // ALWAYS include test instructor@gmail.com in assignments (for testing)
                $testInstructor = Instructor::where('school_id', $school->id)
                    ->where('email', 'instructor@gmail.com')
                    ->first();
                
                $numInstructors = rand(2, min(4, count($createdInstructors)));
                $assignedInstructorIds = [];
                
                // Add test instructor to 60% of schedules for guaranteed test data
                if ($testInstructor && rand(1, 100) <= 60) {
                    $assignedInstructorIds[] = $testInstructor->id;
                    
                    ScheduleInstructor::create([
                        'school_id' => $school->id,
                        'time_slot_id' => $schedule->id,
                        'instructor_id' => $testInstructor->id,
                        'assignment_type' => 'admin_assigned',
                    ]);
                }
                
                // Fill remaining slots with random instructors
                $remainingSlots = $numInstructors - count($assignedInstructorIds);
                for ($j = 0; $j < $remainingSlots; $j++) {
                    do {
                        $instructorIndex = array_rand($createdInstructors);
                        $instructorId = $createdInstructors[$instructorIndex]->id;
                    } while (in_array($instructorId, $assignedInstructorIds));
                    
                    $assignedInstructorIds[] = $instructorId;
                    
                    ScheduleInstructor::create([
                        'school_id' => $school->id,
                        'time_slot_id' => $schedule->id,
                        'instructor_id' => $instructorId,
                        'assignment_type' => rand(1, 100) <= 70 ? 'admin_assigned' : 'self_selected',
                    ]);
                }
                
                // Create bookings for students who haven't reached their target sessions yet
                // Each booking is assigned to one specific instructor from the slot
                // ONLY book students who are enrolled in THIS course
                if ($status === 'open' && count($createdStudents) > 0 && count($assignedInstructorIds) > 0) {
                    // Find students who still need sessions AND are enrolled in this course
                    $availableStudents = [];
                    foreach ($createdStudents as $student) {
                        $studentData = $studentSessionCounts[$student->id];
                        if ($studentData['current'] < $studentData['target'] && $studentData['course_id'] === $course->id) {
                            $availableStudents[] = $student;
                        }
                    }
                    
                    if (count($availableStudents) > 0) {
                        // Book 2-5 students per time slot
                        $bookingCount = min(rand(2, 5), count($availableStudents), $maxStudents);
                        
                        // Shuffle to randomize which students get this slot
                        shuffle($availableStudents);
                        
                        for ($k = 0; $k < $bookingCount; $k++) {
                            $student = $availableStudents[$k];
                            $studentId = $student->id;
                            
                            // Increment session count
                            $studentSessionCounts[$studentId]['current']++;
                            
                            // Assign to a random instructor from this slot
                            // Prefer test instructor@gmail.com if they're in the assigned list (for better test data)
                            $testInstructorInSlot = Instructor::where('school_id', $school->id)
                                ->where('email', 'instructor@gmail.com')
                                ->first();
                            
                            if ($testInstructorInSlot && in_array($testInstructorInSlot->id, $assignedInstructorIds) && rand(1, 100) <= 50) {
                                $assignedInstructorId = $testInstructorInSlot->id;
                            } else {
                                $assignedInstructorId = $assignedInstructorIds[array_rand($assignedInstructorIds)];
                            }
                            
                            // Get the package from student's enrolled course
                            $studentCourseId = $studentSessionCounts[$studentId]['course_id'];
                            $packages = CoursePackage::where('course_id', $studentCourseId)->get();
                            if ($packages->count() > 0) {
                                $package = $packages->random();
                                
                                // Spread bookings mostly across last month, this month, and next month (70%)
                                // with some older bookings (30%) from 3-6 months ago
                                $isRecentBooking = rand(1, 100) <= 70;
                                $bookingDate = $isRecentBooking 
                                    ? now()->subDays(rand(0, 30))->addDays(rand(-30, 30)) // Last/this/next month
                                    : now()->subDays(rand(90, 180)); // 3-6 months ago
                                
                                // Determine if this is a past booking (for adding lesson details)
                                $isPastBooking = $date->isPast();
                                
                                $bookingData = [
                                'school_id' => $school->id,
                                'student_id' => $studentId,
                                'instructor_id' => $assignedInstructorId,
                                'course_id' => $studentCourseId, // Use student's enrolled course
                                'package_id' => $package->id,
                                'time_slot_id' => $schedule->id,
                                'booking_date' => $bookingDate,
                                'status' => rand(1, 100) <= 85 ? 'confirmed' : (rand(1, 2) === 1 ? 'pending' : 'cancelled'),
                                'payment_status' => rand(1, 100) <= 70 ? 'paid' : (rand(1, 2) === 1 ? 'pending' : 'partial'),
                                'total_amount' => $package->price,
                                'notes' => null,
                            ];
                            
                            // Add lesson details for past bookings (85% chance - increased for better data)
                            if ($isPastBooking && rand(1, 100) <= 85) {
                                $attendanceOptions = ['attended', 'late', 'absent'];
                                $sessionStatusOptions = ['completed', 'cancelled', 'no-show'];
                                $availableSkills = [
                                    'Basic Vehicle Control',
                                    'Parking (90° / Angled)',
                                    'Parallel Parking',
                                    'Lane Changing',
                                    'Turns & Intersections',
                                    'Highway Driving',
                                    'Reverse Driving',
                                    'Emergency Braking',
                                    'Defensive Driving',
                                    'Night Driving',
                                    'Three-Point Turn',
                                    'Uphill/Downhill Parking',
                                ];
                                
                                // 80% attended, 15% late, 5% absent for realistic data
                                $rand = rand(1, 100);
                                if ($rand <= 80) {
                                    $attendance = 'attended';
                                } elseif ($rand <= 95) {
                                    $attendance = 'late';
                                } else {
                                    $attendance = 'absent';
                                }
                                
                                $bookingData['attendance_status'] = $attendance;
                                
                                // Session status based on attendance
                                if ($attendance === 'absent') {
                                    $bookingData['session_status'] = 'no-show';
                                    $bookingData['session_grade'] = null;
                                    $bookingData['instructor_feedback'] = 'Student did not attend the session.';
                                    $bookingData['student_feedback'] = null;
                                } elseif ($attendance === 'late') {
                                    $bookingData['session_status'] = rand(1, 100) <= 90 ? 'completed' : 'cancelled';
                                    $bookingData['session_grade'] = $bookingData['session_status'] === 'completed' ? rand(65, 90) : null;
                                    $bookingData['instructor_feedback'] = $bookingData['session_status'] === 'completed' ? 
                                        'Student arrived late but showed good effort.' : 'Session cancelled due to late arrival.';
                                    $bookingData['student_feedback'] = $bookingData['session_status'] === 'completed' ? 
                                        'Sorry for being late, will improve next time.' : null;
                                } else {
                                    // Attended - 95% completed, 5% cancelled
                                    $bookingData['session_status'] = rand(1, 100) <= 95 ? 'completed' : 'cancelled';
                                    
                                    if ($bookingData['session_status'] === 'completed') {
                                        // Varied grade distribution for realistic data
                                        $gradeRand = rand(1, 100);
                                        if ($gradeRand <= 20) {
                                            $grade = rand(90, 100); // Excellent - 20%
                                            $feedbackPool = [
                                                'Excellent performance! Shows great natural talent.',
                                                'Outstanding session. Student grasped concepts quickly.',
                                                'Exceptional driving skills demonstrated today.',
                                                'Perfect execution of all maneuvers. Very impressive!',
                                            ];
                                        } elseif ($gradeRand <= 60) {
                                            $grade = rand(75, 89); // Good - 40%
                                            $feedbackPool = [
                                                'Good progress. Keep practicing the techniques learned.',
                                                'Solid performance overall. Minor improvements needed.',
                                                'Good understanding of concepts. Practice will make perfect.',
                                                'Well done! Continue building on these skills.',
                                            ];
                                        } elseif ($gradeRand <= 90) {
                                            $grade = rand(60, 74); // Average - 30%
                                            $feedbackPool = [
                                                'Average performance. More practice needed on basic skills.',
                                                'Shows potential but needs more confidence building.',
                                                'Decent effort. Focus on the fundamentals next session.',
                                                'Making progress but needs to work on consistency.',
                                            ];
                                        } else {
                                            $grade = rand(50, 59); // Below Average - 10%
                                            $feedbackPool = [
                                                'Needs significant improvement. Extra practice recommended.',
                                                'Student struggling with basic concepts. Additional sessions suggested.',
                                                'Below expectations. Requires more focused training.',
                                                'Difficulty with key skills. Let\'s schedule review sessions.',
                                            ];
                                        }
                                        
                                        $bookingData['session_grade'] = $grade;
                                        $bookingData['instructor_feedback'] = $feedbackPool[array_rand($feedbackPool)];
                                        
                                        // Student feedback based on grade
                                        $studentFeedbackPool = [
                                            'Very helpful session. Thank you!',
                                            'Learned a lot today. Looking forward to next lesson.',
                                            'Great instructor! Feel more confident now.',
                                            'Thanks for the patience and guidance.',
                                            'This was exactly what I needed to improve.',
                                            'Instructor explains things very well.',
                                        ];
                                        $bookingData['student_feedback'] = $studentFeedbackPool[array_rand($studentFeedbackPool)];
                                    } else {
                                        $bookingData['session_grade'] = null;
                                        $bookingData['instructor_feedback'] = 'Session cancelled due to unforeseen circumstances.';
                                        $bookingData['student_feedback'] = null;
                                    }
                                }
                                
                                // Random skills practiced (2-5 skills) only if session was completed
                                if ($bookingData['session_status'] === 'completed') {
                                    $numSkills = rand(2, 5);
                                    $selectedSkills = array_rand(array_flip($availableSkills), $numSkills);
                                    $bookingData['skills_practiced'] = is_array($selectedSkills) ? $selectedSkills : [$selectedSkills];
                                }
                                
                                $bookingData['attendance_marked_at'] = $date->copy()->addHours(rand(1, 3));
                            }
                            
                            Booking::create($bookingData);
                        }
                    }
                }
            }
        }
        }
        
        // Create Payments for bookings (only for last month, this month, and next month)
        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfNextMonth = now()->addMonth()->endOfMonth();
        
        $bookings = Booking::where('school_id', $school->id)
            ->whereBetween('booking_date', [$startOfLastMonth, $endOfNextMonth])
            ->get();
            
        foreach ($bookings as $booking) {
            // 75% of recent bookings should have payment records
            if (rand(1, 100) <= 75) {
                $paymentMethod = ['credit_card', 'debit_card', 'cash', 'bank_transfer'][rand(0, 3)];
                $paymentStatus = $booking->payment_status === 'paid' ? 'completed' : 
                                ($booking->payment_status === 'partial' ? 'partial' : 'pending');
                
                $amountPaid = $paymentStatus === 'completed' ? $booking->total_amount :
                             ($paymentStatus === 'partial' ? $booking->total_amount * 0.5 : 0);
                
                Payment::create([
                    'school_id' => $school->id,
                    'booking_id' => $booking->id,
                    'amount' => $amountPaid,
                    'method' => $paymentMethod,
                    'reference' => 'TXN' . strtoupper(uniqid()),
                    'status' => $paymentStatus,
                    'paid_on' => $paymentStatus === 'pending' ? null : Carbon::parse($booking->booking_date)->addDays(rand(0, 3)),
                ]);
            }
        }
        
        // Create additional Enrollment Requests (pending/rejected only - approved ones already created per student)
        $enrollmentStatuses = ['pending', 'rejected'];
        $students = Student::where('school_id', $school->id)->get();
        $courses = Course::where('school_id', $school->id)->get();
        $admins = Admin::where('school_id', $school->id)->get();
        
        // Create some pending/rejected requests for students wanting to enroll in OTHER courses
        for ($i = 0; $i < rand(3, 6); $i++) {
            $status = $enrollmentStatuses[rand(0, 1)];
            $student = $students->random();
            
            // Get a course the student is NOT currently enrolled in
            $enrolledCourseId = $studentEnrolledCourses[$student->id]->id ?? null;
            $otherCourses = $courses->filter(fn($c) => $c->id !== $enrolledCourseId);
            if ($otherCourses->isEmpty()) continue;
            
            $course = $otherCourses->random();
            
            EnrollmentRequest::create([
                'school_id' => $school->id,
                'learner_id' => $student->id,
                'course_id' => $course->id,
                'status' => $status,
                'payment_status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'remarks' => $status === 'rejected' ? 'Does not meet requirements' : null,
            ]);
        }
        
        // Create Registration Requests (for new students)
        $registrationStatuses = ['pending', 'approved', 'rejected'];
        for ($i = 0; $i < rand(3, 7); $i++) {
            $status = $registrationStatuses[rand(0, 2)];
            
            RegistrationRequest::create([
                'school_id' => $school->id,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->unique()->safeEmail(),
                'contact' => fake()->phoneNumber(),
                'is_new_driver' => rand(0, 1) === 1,
                'status' => $status,
                'processed_by' => $status !== 'pending' ? $admins->first()->id : null,
                'processed_at' => $status !== 'pending' ? now()->subDays(rand(0, 25)) : null,
                'admin_notes' => $status === 'rejected' ? 'Application incomplete' : null,
            ]);
        }
        
        // Create Instructor Removal Requests
        $instructors = Instructor::where('school_id', $school->id)->get();
        $timeSlots = TimeSlot::where('school_id', $school->id)->get();
        if ($instructors->count() > 2 && $timeSlots->count() > 0) {
            for ($i = 0; $i < rand(2, 4); $i++) {
                $instructor = $instructors->random();
                $timeSlot = $timeSlots->random();
                $status = ['pending', 'approved', 'rejected'][rand(0, 2)];
                $reasons = [
                    'Poor student feedback',
                    'Repeated absence',
                    'Safety violations',
                    'Unprofessional conduct',
                    'Student complaints'
                ];
                
                InstructorRemovalRequest::create([
                    'school_id' => $school->id,
                    'time_slot_id' => $timeSlot->id,
                    'instructor_id' => $instructor->id,
                    'status' => $status,
                    'reason' => $reasons[array_rand($reasons)],
                    'processed_by' => $status !== 'pending' ? $admins->first()->id : null,
                    'processed_at' => $status !== 'pending' ? now()->subDays(rand(0, 35)) : null,
                    'admin_notes' => $status === 'approved' ? 'Approved after review' : null,
                ]);
            }
        }
        
        // Create Progress records for completed bookings
        $completedBookings = Booking::where('school_id', $school->id)
            ->where('status', 'confirmed')
            ->where('payment_status', 'paid')
            ->get();
            
        foreach ($completedBookings as $booking) {
            // 60% of confirmed paid bookings have progress records
            // Check if progress record already exists for this student-course combination
            $existingProgress = Progress::where('school_id', $school->id)
                ->where('student_id', $booking->student_id)
                ->where('course_id', $booking->course_id)
                ->first();
                
            if (rand(1, 100) <= 60 && !$existingProgress) {
                $completionPercent = rand(10, 100);
                
                Progress::create([
                    'school_id' => $school->id,
                    'student_id' => $booking->student_id,
                    'course_id' => $booking->course_id,
                    'completion_percent' => $completionPercent,
                    'notes' => $completionPercent >= 100 ? 'Course completed successfully' : 'In progress',
                    'last_updated' => now()->subDays(rand(0, 10)),
                ]);
            }
        }
        
        // Create Report entries for analytics
        $reportTypes = ['attendance', 'financial', 'instructor_performance', 'student_progress'];
        for ($i = 0; $i < rand(10, 20); $i++) {
            $reportType = $reportTypes[array_rand($reportTypes)];
            $reportData = [];
            $title = '';
            $description = '';
            
            switch ($reportType) {
                case 'attendance':
                    $title = 'Attendance Report - ' . now()->subDays(rand(0, 30))->format('M Y');
                    $description = 'Monthly attendance tracking';
                    $reportData = [
                        'total_sessions' => rand(50, 200),
                        'attended' => rand(40, 180),
                        'missed' => rand(5, 20),
                        'cancellations' => rand(2, 15),
                    ];
                    break;
                case 'financial':
                    $title = 'Revenue Report - ' . now()->subDays(rand(0, 30))->format('M Y');
                    $description = 'Monthly financial summary';
                    $reportData = [
                        'total_revenue' => rand(50000, 500000),
                        'pending_payments' => rand(5000, 50000),
                        'completed_payments' => rand(45000, 450000),
                    ];
                    break;
                case 'instructor_performance':
                    $randomInstructor = $instructors->random();
                    $title = 'Instructor Performance - ' . $randomInstructor->name;
                    $description = 'Performance metrics for instructor';
                    $reportData = [
                        'instructor_id' => $randomInstructor->id,
                        'total_sessions' => rand(20, 100),
                        'completed_sessions' => rand(15, 95),
                        'average_rating' => rand(3, 5),
                        'student_feedback_count' => rand(5, 30),
                    ];
                    break;
                case 'student_progress':
                    $randomStudent = $students->random();
                    $title = 'Student Progress - ' . $randomStudent->name;
                    $description = 'Progress tracking for student';
                    $reportData = [
                        'student_id' => $randomStudent->id,
                        'courses_enrolled' => rand(1, 3),
                        'completion_rate' => rand(60, 100),
                        'average_grade' => rand(70, 95),
                        'sessions_attended' => rand(10, 50),
                    ];
                    break;
            }
            
            Report::create([
                'school_id' => $school->id,
                'report_type' => $reportType,
                'title' => $title,
                'description' => $description,
                'data' => $reportData,
                'generated_by' => $admins->first()->id,
                'date_from' => now()->subDays(rand(30, 60)),
                'date_to' => now()->subDays(rand(0, 10)),
            ]);
        }
    }
    
    /**
     * Create connected test data between admin, instructor, and student test accounts.
     * This ensures they have shared schedules, bookings, and other system interactions.
     */
    private function seedTestAccountConnections($school, $testAdmin, $testInstructor, $testStudent): void
    {
        $this->command->info("  Creating connected test data for test accounts...");
        
        // Get the first course for this school
        $course = Course::where('school_id', $school->id)->first();
        if (!$course) {
            $this->command->warn("  No courses found, skipping test account connections.");
            return;
        }
        
        $package = CoursePackage::where('course_id', $course->id)->first();
        
        // Update test instructor to have this course specialization
        $testInstructor->update(['course_specializations' => [$course->id]]);
        
        // Create time slots for the test instructor (past, today, and future)
        $testTimeSlots = [];
        
        // Past completed sessions (last 2 weeks)
        for ($i = 14; $i >= 1; $i--) {
            $date = now()->subDays($i);
            if ($date->dayOfWeek === Carbon::SUNDAY) continue;
            
            $slot = TimeSlot::create([
                'school_id' => $school->id,
                'course_id' => $course->id,
                'date' => $date->format('Y-m-d'),
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'max_instructors' => 3,
                'status' => 'closed',
            ]);
            
            // Assign test instructor to slot
            ScheduleInstructor::create([
                'school_id' => $school->id,
                'time_slot_id' => $slot->id,
                'instructor_id' => $testInstructor->id,
                'assignment_type' => 'admin_assigned',
            ]);
            
            // Create completed booking for test student with test instructor
            $booking = Booking::create([
                'school_id' => $school->id,
                'student_id' => $testStudent->id,
                'instructor_id' => $testInstructor->id,
                'course_id' => $course->id,
                'package_id' => $package?->id,
                'time_slot_id' => $slot->id,
                'booking_date' => $date->format('Y-m-d'),
                'status' => 'completed',
                'payment_status' => 'paid',
                'total_amount' => $package?->price ?? 5000,
                'attendance_status' => 'attended',
                'session_status' => 'completed',
                'session_grade' => rand(80, 95),
                'instructor_feedback' => 'Good progress! Student is showing steady improvement.',
                'student_feedback' => 'Great session, learned a lot today.',
                'skills_practiced' => ['Basic Vehicle Control', 'Parking', 'Lane Changing'],
                'attendance_marked_at' => $date->copy()->addHours(2),
            ]);
            
            // Create payment for completed booking
            Payment::create([
                'school_id' => $school->id,
                'booking_id' => $booking->id,
                'amount' => $package?->price ?? 5000,
                'method' => 'cash',
                'reference' => 'TXN-TEST-' . strtoupper(uniqid()),
                'status' => 'completed',
                'paid_on' => $date,
            ]);
            
            $testTimeSlots[] = $slot;
        }
        
        // Today's session (scheduled)
        $todaySlot = TimeSlot::create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'max_instructors' => 3,
            'status' => 'open',
        ]);
        
        ScheduleInstructor::create([
            'school_id' => $school->id,
            'time_slot_id' => $todaySlot->id,
            'instructor_id' => $testInstructor->id,
            'assignment_type' => 'admin_assigned',
        ]);
        
        Booking::create([
            'school_id' => $school->id,
            'student_id' => $testStudent->id,
            'instructor_id' => $testInstructor->id,
            'course_id' => $course->id,
            'package_id' => $package?->id,
            'time_slot_id' => $todaySlot->id,
            'booking_date' => now()->format('Y-m-d'),
            'status' => 'scheduled',
            'payment_status' => 'paid',
            'total_amount' => $package?->price ?? 5000,
        ]);
        
        // Future scheduled sessions (next 2 weeks)
        for ($i = 1; $i <= 14; $i++) {
            $date = now()->addDays($i);
            if ($date->dayOfWeek === Carbon::SUNDAY) continue;
            
            $slot = TimeSlot::create([
                'school_id' => $school->id,
                'course_id' => $course->id,
                'date' => $date->format('Y-m-d'),
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'max_instructors' => 3,
                'status' => 'open',
            ]);
            
            ScheduleInstructor::create([
                'school_id' => $school->id,
                'time_slot_id' => $slot->id,
                'instructor_id' => $testInstructor->id,
                'assignment_type' => 'admin_assigned',
            ]);
            
            // Book first 5 future sessions for test student
            if ($i <= 5) {
                Booking::create([
                    'school_id' => $school->id,
                    'student_id' => $testStudent->id,
                    'instructor_id' => $testInstructor->id,
                    'course_id' => $course->id,
                    'package_id' => $package?->id,
                    'time_slot_id' => $slot->id,
                    'booking_date' => $date->format('Y-m-d'),
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                    'total_amount' => $package?->price ?? 5000,
                ]);
            }
            
            $testTimeSlots[] = $slot;
        }
        
        // Create a cancelled booking (cancelled by instructor)
        $cancelledDate = now()->subDays(3);
        $cancelledSlot = TimeSlot::create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => $cancelledDate->format('Y-m-d'),
            'start_time' => '15:00:00',
            'end_time' => '17:00:00',
            'max_instructors' => 3,
            'status' => 'closed',
        ]);
        
        ScheduleInstructor::create([
            'school_id' => $school->id,
            'time_slot_id' => $cancelledSlot->id,
            'instructor_id' => $testInstructor->id,
            'assignment_type' => 'admin_assigned',
        ]);
        
        Booking::create([
            'school_id' => $school->id,
            'student_id' => $testStudent->id,
            'instructor_id' => $testInstructor->id,
            'course_id' => $course->id,
            'package_id' => $package?->id,
            'time_slot_id' => $cancelledSlot->id,
            'booking_date' => $cancelledDate->format('Y-m-d'),
            'status' => 'cancelled',
            'payment_status' => 'refunded',
            'total_amount' => $package?->price ?? 5000,
            'cancelled_by' => 'instructor',
            'cancellation_reason' => 'Vehicle maintenance required',
            'cancelled_at' => $cancelledDate,
        ]);
        
        // Create another cancelled booking (cancelled by student)
        $studentCancelledDate = now()->subDays(5);
        $studentCancelledSlot = TimeSlot::create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => $studentCancelledDate->format('Y-m-d'),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'max_instructors' => 3,
            'status' => 'open',
        ]);
        
        ScheduleInstructor::create([
            'school_id' => $school->id,
            'time_slot_id' => $studentCancelledSlot->id,
            'instructor_id' => $testInstructor->id,
            'assignment_type' => 'admin_assigned',
        ]);
        
        Booking::create([
            'school_id' => $school->id,
            'student_id' => $testStudent->id,
            'instructor_id' => $testInstructor->id,
            'course_id' => $course->id,
            'package_id' => $package?->id,
            'time_slot_id' => $studentCancelledSlot->id,
            'booking_date' => $studentCancelledDate->format('Y-m-d'),
            'status' => 'cancelled',
            'payment_status' => 'refunded',
            'total_amount' => $package?->price ?? 5000,
            'cancelled_by' => 'student',
            'cancellation_reason' => 'Personal emergency',
            'cancelled_at' => $studentCancelledDate,
        ]);
        
        // Create an admin-cancelled booking
        $adminCancelledDate = now()->subDays(7);
        $adminCancelledSlot = TimeSlot::create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'date' => $adminCancelledDate->format('Y-m-d'),
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'max_instructors' => 3,
            'status' => 'closed',
        ]);
        
        ScheduleInstructor::create([
            'school_id' => $school->id,
            'time_slot_id' => $adminCancelledSlot->id,
            'instructor_id' => $testInstructor->id,
            'assignment_type' => 'admin_assigned',
        ]);
        
        Booking::create([
            'school_id' => $school->id,
            'student_id' => $testStudent->id,
            'instructor_id' => $testInstructor->id,
            'course_id' => $course->id,
            'package_id' => $package?->id,
            'time_slot_id' => $adminCancelledSlot->id,
            'booking_date' => $adminCancelledDate->format('Y-m-d'),
            'status' => 'cancelled',
            'payment_status' => 'refunded',
            'total_amount' => $package?->price ?? 5000,
            'cancelled_by' => 'admin',
            'cancellation_reason' => 'Schedule conflict - rescheduled by admin',
            'cancelled_at' => $adminCancelledDate,
        ]);
        
        // ========================================================
        // CREATE SESSIONS WITH OTHER INSTRUCTORS (for continuity testing)
        // This allows testing the feature where instructors can see
        // notes from previous instructors for the same student
        // ========================================================
        $otherInstructors = Instructor::where('school_id', $school->id)
            ->where('id', '!=', $testInstructor->id)
            ->where('email', '!=', 'instructor@gmail.com')
            ->take(3)
            ->get();
        
        $otherInstructorFeedbacks = [
            'Student shows good understanding of basic controls. Needs more practice with parking.',
            'Excellent session! Student is progressing well. Focus on lane changing next.',
            'Good effort today. Student needs to work on mirror checking habits.',
            'Solid improvement from last session. Ready for highway training soon.',
            'Student was nervous initially but gained confidence. Keep encouraging.',
        ];
        
        $sessionCount = 0;
        foreach ($otherInstructors as $otherInstructor) {
            // Create 2-3 past sessions with each other instructor
            $sessionsWithThis = rand(2, 3);
            for ($s = 0; $s < $sessionsWithThis; $s++) {
                $sessionCount++;
                $pastDate = now()->subDays(rand(20, 45)); // 3-6 weeks ago (before test instructor sessions)
                
                // Skip Sundays
                if ($pastDate->dayOfWeek === Carbon::SUNDAY) {
                    $pastDate = $pastDate->subDay();
                }
                
                $otherSlot = TimeSlot::create([
                    'school_id' => $school->id,
                    'course_id' => $course->id,
                    'date' => $pastDate->format('Y-m-d'),
                    'start_time' => ['08:00:00', '10:00:00', '13:00:00', '15:00:00'][rand(0, 3)],
                    'end_time' => ['10:00:00', '12:00:00', '15:00:00', '17:00:00'][rand(0, 3)],
                    'max_instructors' => 3,
                    'status' => 'closed',
                ]);
                
                ScheduleInstructor::create([
                    'school_id' => $school->id,
                    'time_slot_id' => $otherSlot->id,
                    'instructor_id' => $otherInstructor->id,
                    'assignment_type' => 'admin_assigned',
                ]);
                
                // Create completed booking with other instructor
                $otherBooking = Booking::create([
                    'school_id' => $school->id,
                    'student_id' => $testStudent->id,
                    'instructor_id' => $otherInstructor->id,
                    'course_id' => $course->id,
                    'package_id' => $package?->id,
                    'time_slot_id' => $otherSlot->id,
                    'booking_date' => $pastDate->format('Y-m-d'),
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'total_amount' => $package?->price ?? 5000,
                    'attendance_status' => 'attended',
                    'session_status' => 'completed',
                    'session_grade' => rand(70, 90),
                    'instructor_feedback' => $otherInstructorFeedbacks[array_rand($otherInstructorFeedbacks)],
                    'student_feedback' => 'Good session with ' . $otherInstructor->name,
                    'skills_practiced' => ['Basic Vehicle Control', 'Turns & Intersections', 'Mirror Checking'],
                    'attendance_marked_at' => $pastDate->copy()->addHours(2),
                ]);
                
                Payment::create([
                    'school_id' => $school->id,
                    'booking_id' => $otherBooking->id,
                    'amount' => $package?->price ?? 5000,
                    'method' => 'cash',
                    'reference' => 'TXN-OTHER-' . strtoupper(uniqid()),
                    'status' => 'completed',
                    'paid_on' => $pastDate,
                ]);
            }
        }
        
        $this->command->info("    - Created {$sessionCount} sessions with other instructors (for continuity testing)");
        
        // Create enrollment request for test student (approved by test admin)
        EnrollmentRequest::create([
            'school_id' => $school->id,
            'learner_id' => $testStudent->id,
            'course_id' => $course->id,
            'status' => 'approved',
            'payment_status' => 'paid',
            'approved_by' => $testAdmin->id,
            'approved_at' => now()->subMonths(2),
            'remarks' => 'Approved by admin - student meets all requirements',
        ]);
        
        // Create progress record for test student
        Progress::create([
            'school_id' => $school->id,
            'student_id' => $testStudent->id,
            'course_id' => $course->id,
            'completion_percent' => 65,
            'notes' => 'Good progress, on track to complete course.',
            'last_updated' => now(),
        ]);
        
        // Create a performance report for test instructor (generated by test admin)
        Report::create([
            'school_id' => $school->id,
            'report_type' => 'instructor_performance',
            'title' => 'Performance Report - ' . $testInstructor->name,
            'description' => 'Monthly performance review for test instructor',
            'data' => [
                'instructor_id' => $testInstructor->id,
                'total_sessions' => 28,
                'completed_sessions' => 25,
                'cancelled_sessions' => 3,
                'average_rating' => 4.5,
                'student_feedback_count' => 20,
                'on_time_percentage' => 95,
            ],
            'generated_by' => $testAdmin->id,
            'date_from' => now()->subMonth(),
            'date_to' => now(),
        ]);
        
        // Create a student progress report (generated by test admin)
        Report::create([
            'school_id' => $school->id,
            'report_type' => 'student_progress',
            'title' => 'Progress Report - ' . $testStudent->name,
            'description' => 'Progress tracking for test student',
            'data' => [
                'student_id' => $testStudent->id,
                'courses_enrolled' => 1,
                'completion_rate' => 65,
                'average_grade' => 88,
                'sessions_attended' => 12,
                'sessions_remaining' => 8,
            ],
            'generated_by' => $testAdmin->id,
            'date_from' => now()->subMonths(2),
            'date_to' => now(),
        ]);
        
        $this->command->info("  ✓ Created connected test data:");
        $this->command->info("    - Test instructor assigned to " . count($testTimeSlots) . " time slots");
        $this->command->info("    - Test student has bookings with test instructor");
        $this->command->info("    - Test student has bookings with OTHER instructors (for viewing previous notes)");
        $this->command->info("    - Includes completed, scheduled, and cancelled bookings");
        $this->command->info("    - Enrollment approved by test admin");
        $this->command->info("    - Reports generated by test admin");
    }
}