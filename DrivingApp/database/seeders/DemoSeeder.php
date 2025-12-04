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
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚗 Creating Demo Data for DriveED Hub...');

        // ========================================
        // SCHOOL 1: Smart Driving School (MAIN CLIENT)
        // ========================================
        $school1 = School::create([
            'name' => 'Smart Driving School',
            'slug' => 'smart-driving',
            'timezone' => 'Asia/Manila',
            'instructor_removal_notice_days' => 7,
        ]);

        SchoolSetting::create([
            'school_id' => $school1->id,
            'primary_color' => '#2563eb',
            'secondary_color' => '#eab308',
            'accent_color' => '#3b82f6',
            'use_gradient_header' => true,
            'header_text_color' => '#ffffff',
            'background_type' => 'color',
            'background_color' => '#f5f5f5',
            'sidebar_bg_color' => '#ffffff',
            'sidebar_text_color' => '#333333',
            'instructor_selection_mode' => 'auto_assign',
            'enable_booking_queue' => true,
            'booking_queue_days' => 3,
        ]);

        // School 1 Admins
        Admin::create([
            'school_id' => $school1->id,
            'name' => 'Maria Santos',
            'email' => 'maria.santos@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'school_admin',
        ]);

        Admin::create([
            'school_id' => $school1->id,
            'name' => 'Carlos Rodriguez',
            'email' => 'carlos.rodriguez@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'system_admin',
        ]);

        // School 1 Instructors (4)
        $instructors1 = [
            ['name' => 'Juan Dela Cruz', 'email' => 'juan.delacruz@gmail.com', 'contact' => '+63-917-111-2222'],
            ['name' => 'Ana Garcia', 'email' => 'ana.garcia@gmail.com', 'contact' => '+63-917-222-3333'],
            ['name' => 'Pedro Martinez', 'email' => 'pedro.martinez@gmail.com', 'contact' => '+63-917-333-4444'],
            ['name' => 'Rosa Villanueva', 'email' => 'rosa.villanueva@gmail.com', 'contact' => '+63-917-444-5555'],
        ];

        $createdInstructors1 = [];
        foreach ($instructors1 as $i => $inst) {
            $createdInstructors1[] = Instructor::create([
                'school_id' => $school1->id,
                'name' => $inst['name'],
                'email' => $inst['email'],
                'contact' => $inst['contact'],
                'password' => Hash::make('password123'),
                'license_number' => 'LIC-SDS-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'status' => 'active',
                'availability' => 'available',
            ]);
        }

        // School 1 Courses
        $course1_1 = Course::create([
            'school_id' => $school1->id,
            'title' => 'Practical Driving Course (Manual)',
            'description' => 'Complete manual transmission driving course for beginners. Learn clutch control, gear shifting, and road safety.',
            'type' => 'Practical',
            'vehicle_type' => 'Car',
            'status' => 'active',
            'is_featured' => true,
            'features' => ['Manual Transmission', 'Clutch Control Training', 'Hill Start Practice', 'LTO Test Preparation'],
        ]);

        CoursePackage::create([
            'course_id' => $course1_1->id,
            'name' => '10-Hour Package',
            'sessions' => 10,
            'hours' => 10,
            'price' => 5500.00,
            'status' => 'active',
        ]);

        CoursePackage::create([
            'course_id' => $course1_1->id,
            'name' => '15-Hour Package',
            'sessions' => 15,
            'hours' => 15,
            'price' => 7500.00,
            'status' => 'active',
        ]);

        $course1_2 = Course::create([
            'school_id' => $school1->id,
            'title' => 'Practical Driving Course (Automatic)',
            'description' => 'Learn to drive automatic transmission vehicles with confidence. Perfect for city driving.',
            'type' => 'Practical',
            'vehicle_type' => 'Car',
            'status' => 'active',
            'is_featured' => true,
            'features' => ['Automatic Transmission', 'City Driving', 'Parking Techniques', 'Defensive Driving'],
        ]);

        CoursePackage::create([
            'course_id' => $course1_2->id,
            'name' => '8-Hour Package',
            'sessions' => 8,
            'hours' => 8,
            'price' => 4800.00,
            'status' => 'active',
        ]);

        $course1_3 = Course::create([
            'school_id' => $school1->id,
            'title' => 'Theoretical Driving Course',
            'description' => 'Comprehensive road rules and traffic signs education. Required for LTO written exam.',
            'type' => 'Theoretical',
            'vehicle_type' => 'Car',
            'status' => 'active',
            'features' => ['Traffic Rules', 'Road Signs', 'LTO Written Exam Prep', 'Certificate Included'],
        ]);

        CoursePackage::create([
            'course_id' => $course1_3->id,
            'name' => 'TDC 15-Hour',
            'sessions' => 5,
            'hours' => 15,
            'price' => 1500.00,
            'status' => 'active',
        ]);

        // School 1 Students (15)
        $students1Data = [
            ['name' => 'Miguel Santos', 'email' => 'miguel.santos@gmail.com'],
            ['name' => 'Sofia Reyes', 'email' => 'sofia.reyes@gmail.com'],
            ['name' => 'Luis Cruz', 'email' => 'luis.cruz@gmail.com'],
            ['name' => 'Isabella Flores', 'email' => 'isabella.flores@gmail.com'],
            ['name' => 'Diego Torres', 'email' => 'diego.torres@gmail.com'],
            ['name' => 'Carmen Ramos', 'email' => 'carmen.ramos@gmail.com'],
            ['name' => 'Rafael Mendoza', 'email' => 'rafael.mendoza@gmail.com'],
            ['name' => 'Valentina Garcia', 'email' => 'valentina.garcia@gmail.com'],
            ['name' => 'Gabriel Lopez', 'email' => 'gabriel.lopez@gmail.com'],
            ['name' => 'Lucia Perez', 'email' => 'lucia.perez@gmail.com'],
            ['name' => 'Daniel Rivera', 'email' => 'daniel.rivera@gmail.com'],
            ['name' => 'Angela Castro', 'email' => 'angela.castro@gmail.com'],
            ['name' => 'Roberto Ortiz', 'email' => 'roberto.ortiz@gmail.com'],
            ['name' => 'Cristina Martinez', 'email' => 'cristina.martinez@gmail.com'],
            ['name' => 'Fernando Fernandez', 'email' => 'fernando.fernandez@gmail.com'],
        ];

        $students1 = [];
        foreach ($students1Data as $i => $s) {
            $enrollmentDate = now()->subDays(rand(7, 90));
            $students1[] = Student::create([
                'school_id' => $school1->id,
                'name' => $s['name'],
                'email' => $s['email'],
                'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                'password' => Hash::make('password123'),
                'status' => 'active',
                'role' => 'student',
                'enrollment_date' => $enrollmentDate,
            ]);
        }

        // Create TimeSlots for School 1 instructors
        $this->createTimeSlotsForInstructors($school1, $createdInstructors1);

        // Create Bookings and Payments for School 1
        $this->createBookingsAndPayments($school1, $students1, $createdInstructors1, [$course1_1, $course1_2, $course1_3]);

        $this->command->info("✅ Smart Driving School created with 4 instructors, 15 students");

        // ========================================
        // SCHOOL 2: LySpeed Driving School
        // ========================================
        $school2 = School::create([
            'name' => 'LySpeed Driving School',
            'slug' => 'lyspeed-driving',
            'timezone' => 'Asia/Manila',
            'instructor_removal_notice_days' => 7,
        ]);

        SchoolSetting::create([
            'school_id' => $school2->id,
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
        ]);

        // School 2 Admin
        Admin::create([
            'school_id' => $school2->id,
            'name' => 'John Dela Cruz',
            'email' => 'john.delacruz@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'school_admin',
        ]);

        Admin::create([
            'school_id' => $school2->id,
            'name' => 'Patricia Lim',
            'email' => 'patricia.lim@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'system_admin',
        ]);

        // School 2 Instructors (3)
        $instructors2 = [
            ['name' => 'Ricardo Santos', 'email' => 'ricardo.santos@gmail.com', 'contact' => '+63-918-111-2222'],
            ['name' => 'Elena Ramos', 'email' => 'elena.ramos@gmail.com', 'contact' => '+63-918-222-3333'],
            ['name' => 'Fernando Cruz', 'email' => 'fernando.cruz@gmail.com', 'contact' => '+63-918-333-4444'],
        ];

        $createdInstructors2 = [];
        foreach ($instructors2 as $i => $inst) {
            $createdInstructors2[] = Instructor::create([
                'school_id' => $school2->id,
                'name' => $inst['name'],
                'email' => $inst['email'],
                'contact' => $inst['contact'],
                'password' => Hash::make('password123'),
                'license_number' => 'LIC-LSD-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'status' => 'active',
                'availability' => 'available',
            ]);
        }

        // School 2 Courses
        $course2_1 = Course::create([
            'school_id' => $school2->id,
            'title' => 'Basic Driving Course',
            'description' => 'Affordable driving lessons for beginners. Learn the fundamentals of safe driving.',
            'type' => 'Practical',
            'vehicle_type' => 'Car',
            'status' => 'active',
            'is_featured' => true,
            'features' => ['Basic Vehicle Control', 'Traffic Navigation', 'Parking Skills', 'Road Safety'],
        ]);

        CoursePackage::create([
            'course_id' => $course2_1->id,
            'name' => '8-Hour Starter',
            'sessions' => 8,
            'hours' => 8,
            'price' => 4000.00,
            'status' => 'active',
        ]);

        CoursePackage::create([
            'course_id' => $course2_1->id,
            'name' => '12-Hour Complete',
            'sessions' => 12,
            'hours' => 12,
            'price' => 5500.00,
            'status' => 'active',
        ]);

        $course2_2 = Course::create([
            'school_id' => $school2->id,
            'title' => 'Motorcycle Riding Course',
            'description' => 'Learn to ride motorcycles safely. Includes scooter and manual motorcycle training.',
            'type' => 'Practical',
            'vehicle_type' => 'Motorcycle',
            'status' => 'active',
            'features' => ['Balance Training', 'Gear Shifting', 'Defensive Riding', 'License Preparation'],
        ]);

        CoursePackage::create([
            'course_id' => $course2_2->id,
            'name' => '6-Hour Package',
            'sessions' => 6,
            'hours' => 6,
            'price' => 3000.00,
            'status' => 'active',
        ]);

        // School 2 Students (10)
        $students2Data = [
            ['name' => 'Paolo Gonzales', 'email' => 'paolo.gonzales@gmail.com'],
            ['name' => 'Maria Rodriguez', 'email' => 'maria.rodriguez@gmail.com'],
            ['name' => 'Antonio Hernandez', 'email' => 'antonio.hernandez@gmail.com'],
            ['name' => 'Teresa Jimenez', 'email' => 'teresa.jimenez@gmail.com'],
            ['name' => 'Marco Ruiz', 'email' => 'marco.ruiz@gmail.com'],
            ['name' => 'Laura Sanchez', 'email' => 'laura.sanchez@gmail.com'],
            ['name' => 'Pedro Ramirez', 'email' => 'pedro.ramirez@gmail.com'],
            ['name' => 'Ana Alvarez', 'email' => 'ana.alvarez@gmail.com'],
            ['name' => 'Jorge Medina', 'email' => 'jorge.medina@gmail.com'],
            ['name' => 'Beatriz Navarro', 'email' => 'beatriz.navarro@gmail.com'],
        ];

        $students2 = [];
        foreach ($students2Data as $s) {
            $enrollmentDate = now()->subDays(rand(7, 60));
            $students2[] = Student::create([
                'school_id' => $school2->id,
                'name' => $s['name'],
                'email' => $s['email'],
                'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                'password' => Hash::make('password123'),
                'status' => 'active',
                'role' => 'student',
                'enrollment_date' => $enrollmentDate,
            ]);
        }

        // Create TimeSlots for School 2 instructors
        $this->createTimeSlotsForInstructors($school2, $createdInstructors2);

        // Create Bookings and Payments for School 2
        $this->createBookingsAndPayments($school2, $students2, $createdInstructors2, [$course2_1, $course2_2]);

        $this->command->info("✅ LySpeed Driving School created with 3 instructors, 10 students");

        // ========================================
        // QUICK LOGIN ACCOUNTS (for demo)
        // ========================================
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('🔐 DEMO LOGIN CREDENTIALS');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('SMART DRIVING SCHOOL (smart-driving) - MAIN CLIENT');
        $this->command->info('  School Admin: maria.santos@gmail.com / password123');
        $this->command->info('  System Admin: carlos.rodriguez@gmail.com / password123');
        $this->command->info('  Instructor: juan.delacruz@gmail.com / password123');
        $this->command->info('  Student: miguel.santos@gmail.com / password123');
        $this->command->info('');
        $this->command->info('LYSPEED DRIVING SCHOOL (lyspeed-driving)');
        $this->command->info('  School Admin: john.delacruz@gmail.com / password123');
        $this->command->info('  System Admin: patricia.lim@gmail.com / password123');
        $this->command->info('  Instructor: ricardo.santos@gmail.com / password123');
        $this->command->info('  Student: paolo.gonzales@gmail.com / password123');
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('🎉 Demo data seeding complete!');
        $this->command->info('========================================');
    }

    private function createTimeSlotsForInstructors($school, $instructors)
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

            foreach ($instructors as $instructor) {
                // Each instructor gets 4-6 random slots per day
                $daySlots = array_rand($times, rand(4, min(6, count($times))));
                if (!is_array($daySlots)) $daySlots = [$daySlots];
                
                foreach ($daySlots as $slotIndex) {
                    TimeSlot::create([
                        'school_id' => $school->id,
                        'instructor_id' => $instructor->id,
                        'date' => $date,
                        'start_time' => $times[$slotIndex][0],
                        'end_time' => $times[$slotIndex][1],
                        'status' => 'available',
                        'max_students' => 1,
                    ]);
                }
            }
        }
    }

    private function createBookingsAndPayments($school, $students, $instructors, $courses)
    {
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
                'course_package_id' => $package->id,
                'booking_date' => $bookingDate->format('Y-m-d'),
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'status' => $status,
                'notes' => $status == 'cancelled' ? 'Student requested cancellation' : null,
            ]);

            // Create payment for non-cancelled bookings
            if ($status != 'cancelled' && $status != 'pending') {
                $paymentStatus = $status == 'completed' ? 'completed' : (rand(0, 1) ? 'completed' : 'partial');
                $amountPaid = $paymentStatus == 'completed' ? $package->price : $package->price * 0.5;
                
                Payment::create([
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'booking_id' => $booking->id,
                    'course_package_id' => $package->id,
                    'amount' => $package->price,
                    'amount_paid' => $amountPaid,
                    'balance' => $package->price - $amountPaid,
                    'payment_method' => ['cash', 'gcash', 'bank_transfer'][rand(0, 2)],
                    'status' => $paymentStatus,
                    'payment_date' => $bookingDate->format('Y-m-d'),
                ]);
            }

            // Create progress for completed bookings
            if ($status == 'completed') {
                Progress::create([
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'instructor_id' => $instructor->id,
                    'course_id' => $course->id,
                    'booking_id' => $booking->id,
                    'session_number' => rand(1, 5),
                    'total_sessions' => $package->sessions,
                    'skills_covered' => ['Basic Control', 'Steering', 'Braking'],
                    'performance_rating' => rand(3, 5),
                    'notes' => 'Good progress, student is learning well.',
                    'session_date' => $bookingDate->format('Y-m-d'),
                ]);
            }
        }
    }
}
