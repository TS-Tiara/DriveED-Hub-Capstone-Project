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
use Carbon\Carbon;

class ScheduleFocusedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create two schools for testing
        $schools = [
            [
                'slug' => 'drivingschool1',
                'name' => 'Elite Driving Academy',
                'timezone' => 'Asia/Manila',
                'instructor_removal_notice_days' => 7,
                'colors' => [
                    'primary_color' => '#667eea',
                    'secondary_color' => '#764ba2',
                    'accent_color' => '#5568d3',
                ]
            ],
            [
                'slug' => 'metro-drive',
                'name' => 'Metro Drive School',
                'timezone' => 'Asia/Manila',
                'instructor_removal_notice_days' => 7,
                'colors' => [
                    'primary_color' => '#f59e0b',
                    'secondary_color' => '#ef4444',
                    'accent_color' => '#dc2626',
                ]
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

            // Create school settings with custom colors
            SchoolSetting::updateOrCreate(
                ['school_id' => $school->id],
                [
                    'primary_color' => $schoolData['colors']['primary_color'],
                    'secondary_color' => $schoolData['colors']['secondary_color'],
                    'accent_color' => $schoolData['colors']['accent_color'],
                    'sidebar_bg_color' => '#ffffff',
                    'sidebar_text_color' => '#333333',
                    'sidebar_hover_color' => '#f5f5f5',
                    'use_gradient_header' => true,
                    'header_text_color' => '#ffffff',
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
                ]
            );

            $this->seedSchoolData($school, $schoolData);
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
        $this->command->info('  URL: http://localhost:8000/drivingschool1');
        $this->command->info('  Admin 1: maria.santos.school@gmail.com / password123');
        $this->command->info('  Admin 2: patricia.gonzales.school@gmail.com / password123');
        $this->command->info('  Courses: Professional Driving Mastery, Defensive Driving Mastery, SUV Specialization');
        $this->command->info('');
        $this->command->info('School 2 - Metro Drive School:');
        $this->command->info('  URL: http://localhost:8000/metro-drive');
        $this->command->info('  Admin 1: john.dela.cruz.school@gmail.com / password123');
        $this->command->info('  Admin 2: mark.villanueva.school@gmail.com / password123');
        $this->command->info('  Courses: Essential Driving Course, Refresher Course, Motorcycle Basics');
        $this->command->info('');
        $this->command->info('=== STATISTICS ===');
        $this->command->info('Schools: ' . School::count());
        $this->command->info('Admins: ' . Admin::count());
        $this->command->info('Instructors: ' . Instructor::count());
        $this->command->info('Students: ' . Student::count());
        $this->command->info('Courses: ' . Course::count());
    }

    private function seedSchoolData($school, $schoolData): void
    {
        $isFirstSchool = $schoolData['slug'] === 'drivingschool1';
        
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

        // Create Instructors - Different for each school
        $instructors = $isFirstSchool ? [
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz.instructor@gmail.com',
                'contact' => '+63-917-111-2222',
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia.instructor@gmail.com',
                'contact' => '+63-917-222-3333',
            ],
            [
                'name' => 'Pedro Martinez',
                'email' => 'pedro.martinez.instructor@gmail.com',
                'contact' => '+63-917-333-4444',
            ],
            [
                'name' => 'Ana Lopez',
                'email' => 'ana.lopez.instructor@gmail.com',
                'contact' => '+63-917-444-5555',
            ],
            [
                'name' => 'Carlos Fernandez',
                'email' => 'carlos.fernandez.instructor@gmail.com',
                'contact' => '+63-917-555-6666',
            ],
        ] : [
            [
                'name' => 'Ricardo Santos',
                'email' => 'ricardo.santos.instructor@gmail.com',
                'contact' => '+63-917-666-7777',
            ],
            [
                'name' => 'Elena Ramos',
                'email' => 'elena.ramos.instructor@gmail.com',
                'contact' => '+63-917-777-8888',
            ],
            [
                'name' => 'Fernando Cruz',
                'email' => 'fernando.cruz.instructor@gmail.com',
                'contact' => '+63-917-888-9999',
            ],
            [
                'name' => 'Linda Flores',
                'email' => 'linda.flores.instructor@gmail.com',
                'contact' => '+63-917-999-0000',
            ],
            [
                'name' => 'Antonio Mendez',
                'email' => 'antonio.mendez.instructor@gmail.com',
                'contact' => '+63-917-000-1111',
            ],
        ];

        $createdInstructors = [];
        foreach ($instructors as $instructor) {
            $createdInstructors[] = Instructor::create(array_merge($instructor, [
                'school_id' => $school->id,
                'password' => Hash::make('password123'),
                'license_number' => 'LIC-' . rand(100000, 999999),
                'status' => 'active',
            ]));
        }

        // Create Students - Different for each school
        $students = $isFirstSchool ? [
            ['name' => 'Miguel Santos', 'email' => 'miguel.santos.student@gmail.com', 'contact' => '+63-918-111-2222'],
            ['name' => 'Sofia Reyes', 'email' => 'sofia.reyes.student@gmail.com', 'contact' => '+63-918-222-3333'],
            ['name' => 'Luis Hernandez', 'email' => 'luis.hernandez.student@gmail.com', 'contact' => '+63-918-333-4444'],
            ['name' => 'Isabella Cruz', 'email' => 'isabella.cruz.student@gmail.com', 'contact' => '+63-918-444-5555'],
            ['name' => 'Diego Morales', 'email' => 'diego.morales.student@gmail.com', 'contact' => '+63-918-555-6666'],
            ['name' => 'Carmen Flores', 'email' => 'carmen.flores.student@gmail.com', 'contact' => '+63-918-666-7777'],
            ['name' => 'Rafael Torres', 'email' => 'rafael.torres.student@gmail.com', 'contact' => '+63-918-777-8888'],
            ['name' => 'Valentina Ramos', 'email' => 'valentina.ramos.student@gmail.com', 'contact' => '+63-918-888-9999'],
            ['name' => 'Gabriel Mendoza', 'email' => 'gabriel.mendoza.student@gmail.com', 'contact' => '+63-919-111-2222'],
            ['name' => 'Lucia Vargas', 'email' => 'lucia.vargas.student@gmail.com', 'contact' => '+63-919-222-3333'],
        ] : [
            ['name' => 'Daniel Cruz', 'email' => 'daniel.cruz.student@gmail.com', 'contact' => '+63-919-333-4444'],
            ['name' => 'Angela Diaz', 'email' => 'angela.diaz.student@gmail.com', 'contact' => '+63-919-444-5555'],
            ['name' => 'Roberto Garcia', 'email' => 'roberto.garcia.student@gmail.com', 'contact' => '+63-919-555-6666'],
            ['name' => 'Cristina Lopez', 'email' => 'cristina.lopez.student@gmail.com', 'contact' => '+63-919-666-7777'],
            ['name' => 'Fernando Silva', 'email' => 'fernando.silva.student@gmail.com', 'contact' => '+63-919-777-8888'],
            ['name' => 'Patricia Gomez', 'email' => 'patricia.gomez.student@gmail.com', 'contact' => '+63-919-888-9999'],
            ['name' => 'Eduardo Perez', 'email' => 'eduardo.perez.student@gmail.com', 'contact' => '+63-920-111-2222'],
            ['name' => 'Monica Rivera', 'email' => 'monica.rivera.student@gmail.com', 'contact' => '+63-920-222-3333'],
            ['name' => 'Alberto Castro', 'email' => 'alberto.castro.student@gmail.com', 'contact' => '+63-920-333-4444'],
            ['name' => 'Sandra Ortiz', 'email' => 'sandra.ortiz.student@gmail.com', 'contact' => '+63-920-444-5555'],
        ];

        $createdStudents = [];
        foreach ($students as $student) {
            $createdStudents[] = Student::create(array_merge($student, [
                'school_id' => $school->id,
                'password' => Hash::make('password123'),
                'role' => 'student',
                'status' => 'active',
                'address' => 'Metro Manila, Philippines',
                'enrollment_date' => now()->subDays(rand(1, 90)),
            ]));
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

        // Create Random Schedules (Time Slots) - Different patterns for each school
        $courses = [$course1, $course2, $course3];
        $startDate = Carbon::now()->addDay();
        
        // Define possible time slots - Different for each school
        if ($isFirstSchool) {
            // Elite: Longer sessions, more flexible hours
            $timeSlots = [
                ['start' => '07:00:00', 'end' => '09:30:00'],
                ['start' => '09:30:00', 'end' => '12:00:00'],
                ['start' => '13:00:00', 'end' => '15:30:00'],
                ['start' => '15:30:00', 'end' => '18:00:00'],
                ['start' => '18:00:00', 'end' => '20:00:00'],
            ];
            $daysToGenerate = 21; // 3 weeks
        } else {
            // Metro: Standard sessions, regular hours
            $timeSlots = [
                ['start' => '08:00:00', 'end' => '10:00:00'],
                ['start' => '10:00:00', 'end' => '12:00:00'],
                ['start' => '13:00:00', 'end' => '15:00:00'],
                ['start' => '15:00:00', 'end' => '17:00:00'],
                ['start' => '17:00:00', 'end' => '19:00:00'],
            ];
            $daysToGenerate = 14; // 2 weeks
        }
        
        for ($day = 0; $day < $daysToGenerate; $day++) {
            $date = $startDate->copy()->addDays($day);
            
            // Skip Sundays
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }

            // Different slot patterns for each school
            if ($isFirstSchool) {
                // Elite: More slots available, Saturday: 3-5 slots, Weekdays: 4-5 slots
                $numSlots = $date->dayOfWeek === Carbon::SATURDAY ? rand(3, 5) : rand(4, 5);
            } else {
                // Metro: Standard slots, Saturday: 2-3 slots, Weekdays: 3-4 slots
                $numSlots = $date->dayOfWeek === Carbon::SATURDAY ? rand(2, 3) : rand(3, 4);
            }
            $usedTimeSlots = [];
            
            for ($i = 0; $i < $numSlots; $i++) {
                // Pick a random time slot that hasn't been used today
                do {
                    $timeSlotIndex = array_rand($timeSlots);
                } while (in_array($timeSlotIndex, $usedTimeSlots));
                
                $usedTimeSlots[] = $timeSlotIndex;
                $timeSlot = $timeSlots[$timeSlotIndex];
                
                // Random course
                $course = $courses[array_rand($courses)];
                
                // Random status: 85% open, 15% closed
                $status = rand(1, 100) <= 85 ? 'open' : 'closed';
                
                $schedule = TimeSlot::create([
                    'school_id' => $school->id,
                    'course_id' => $course->id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => $timeSlot['start'],
                    'end_time' => $timeSlot['end'],
                    'status' => $status,
                ]);
                
                // Assign 1-2 random instructors
                $numInstructors = rand(1, 2);
                $assignedInstructors = [];
                
                for ($j = 0; $j < $numInstructors; $j++) {
                    do {
                        $instructorIndex = array_rand($createdInstructors);
                    } while (in_array($instructorIndex, $assignedInstructors));
                    
                    $assignedInstructors[] = $instructorIndex;
                    
                    ScheduleInstructor::create([
                        'school_id' => $school->id,
                        'time_slot_id' => $schedule->id,
                        'instructor_id' => $createdInstructors[$instructorIndex]->id,
                    ]);
                }
            }
        }
    }
}