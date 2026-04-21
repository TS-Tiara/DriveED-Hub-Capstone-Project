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
use Database\Seeders\Traits\HandlesSchoolSeeding;

class DriveEdHubSeeder extends Seeder
{
    use HandlesSchoolSeeding;

    public function run(): void
    {
        $demoPassword = (string) env('DEMO_SEED_PASSWORD', 'DriveDemo123');
        $hashedPassword = Hash::make($demoPassword);

        $this->command->info('');
        $this->command->info('🏫 Creating DriveED Hub Driving School (2 branches)...');

        $school = School::updateOrCreate(
            ['slug' => 'drived-hub'],
            [
                'name' => 'DriveED Hub Driving School',
                'timezone' => 'Asia/Manila',
                'branding' => json_encode([
                    'logo' => null,
                    'colors' => ['primary' => '#667eea', 'secondary' => '#764ba2', 'accent' => '#1e40af'],
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

        SchoolSetting::updateOrCreate(
            ['school_id' => $school->id],
            [
                'primary_color' => '#667eea',
                'secondary_color' => '#764ba2',
                'accent_color' => '#1e40af',
                'button_primary_bg' => '#667eea',
                'button_style' => 'gradient',
                'use_gradient_header' => true,
                'background_type' => 'gradient',
                'background_color' => '#f8fafc',
                'role_student_bg' => '#ede9fe',
                'role_student_text' => '#5b21b6',
                'role_instructor_bg' => '#f3e8ff',
                'role_instructor_text' => '#6b21a8',

                'header_text_color' => '#ffffff',
                'sidebar_bg_color' => '#ffffff',
                'sidebar_text_color' => '#667eea',
                'instructor_selection_mode' => 'admin_assigned',
                'enable_booking_queue' => true,
                'booking_queue_days' => 3,
                'enable_branches' => true,
                'booking_cutoff_hours' => 12,
                'alert_threshold_pending' => 50,
            ]
        );

        // ── 1. INFRASTRUCTURE ──
        $branches = $this->createBranches($school, [
            ['name' => 'Main Campus - Clark', 'address' => '789 Del Pilar Street, Clark Freeport Zone, Pampanga', 'contact_number' => '+63-919-345-6789', 'email' => 'clark@drivedhub.com'],
            ['name' => 'Balibago Branch', 'address' => '456 Fields Avenue, Balibago, Angeles City, Pampanga', 'contact_number' => '+63-919-345-6790', 'email' => 'balibago@drivedhub.com'],
        ]);
        $this->command->info('   ✓ Branches created');

        // ── 2. IDENTITY (All users first) ──

        // Admins
        $admin = Admin::updateOrCreate(['email' => 'admin1@gmail.com'], [
            'school_id' => $school->id,
            'name' => 'Antonio Francisco Reyes',
            'password' => $hashedPassword,
            'role' => 'school_admin',
            'is_active' => true,
        ]);
        $admin2 = Admin::updateOrCreate(['email' => 'admin2@gmail.com'], [
            'school_id' => $school->id,
            'name' => 'School Admin Two',
            'password' => $hashedPassword,
            'role' => 'school_admin',
            'is_active' => true,
        ]);
        Admin::updateOrCreate(['email' => 'manager1@gmail.com'], [
            'school_id' => $school->id,
            'branch_id' => $branches[0]->id,
            'name' => 'Patricia Lyn Mendoza',
            'password' => $hashedPassword,
            'role' => 'branch_secretary',
            'is_active' => true,
        ]);
        Admin::updateOrCreate(['email' => 'manager2@gmail.com'], [
            'school_id' => $school->id,
            'branch_id' => $branches[1]->id,
            'name' => 'Gabriel Marco Santos',
            'password' => $hashedPassword,
            'role' => 'branch_secretary',
            'is_active' => true,
        ]);

        // Instructors
        $dhInstructors = [
            ['name' => 'Ricardo Antonio Cruz', 'email' => 'instructor1@gmail.com', 'contact' => '+63-919-777-3001', 'license' => 'LIC-DH-2024-001', 'bio' => 'Senior Instructor specializing in Manual Transmission and Motorcycle training. 8 years experience.', 'branch' => 0, 'course_idx' => 0], // TDC
            ['name' => 'Maria Victoria Santos', 'email' => 'instructor2@gmail.com', 'contact' => '+63-919-777-3002', 'license' => 'LIC-DH-2024-002', 'bio' => 'Expert in Automatic Transmission and Practical Driving. Certified defensive driving instructor.', 'branch' => 0, 'course_idx' => 0], // TDC
            ['name' => 'Angelo Miguel Ramos', 'email' => 'instructor3@gmail.com', 'contact' => '+63-919-777-3003', 'license' => 'LIC-DH-2024-003', 'bio' => 'TDC specialist. LTO-certified TDC instructor with 6 years experience.', 'branch' => 1, 'course_idx' => 1], // PDC
            ['name' => 'Sofia Elena Torres', 'email' => 'instructor4@gmail.com', 'contact' => '+63-919-777-3004', 'license' => 'LIC-DH-2024-004', 'bio' => 'Motorcycle and Manual Transmission specialist. Former professional rider turned instructor.', 'branch' => 1, 'course_idx' => 1], // PDC
            ['name' => 'Juan Dela Cruz Jr.', 'email' => 'instructor5@gmail.com', 'contact' => '+63-919-777-3005', 'license' => 'LIC-DH-2024-005', 'bio' => 'Defensive driving specialist.', 'branch' => 0, 'course_idx' => 2], // COMBO
        ];
        $instructors = [];
        foreach ($dhInstructors as $inst) {
            $instructors[] = Instructor::updateOrCreate(
                ['email' => $inst['email']],
                [
                    'school_id' => $school->id,
                    'branch_id' => $branches[$inst['branch']]->id,
                    'name' => $inst['name'],
                    'contact' => $inst['contact'],
                    'password' => $hashedPassword,
                    'license_number' => $inst['license'],
                    'bio' => $inst['bio'],
                    'status' => 'active',
                    'availability' => 'available',
                ]
            );
        }

        // Students
        $students = $this->createDriveEdHubStudents($school, $branches, $hashedPassword);

        // Guests (Base records only, enrollment logic separated later or handled in createDriveEdHubGuests)
        $admins_arr = [$admin, $admin2];
        $guests = $this->createDriveEdHubGuests($school, [], $admins_arr, $branches, $hashedPassword, true); // Pass true to only create users

        $this->command->info('   ✓ All user identities created (Admins, Instructors, Students, Guests)');

        // ── 3. PRODUCTS (Courses) ──
        $courses = $this->createDriveEdHubCourses($school);
        
        // Link instructors strictly to exactly 1 course generated above
        foreach ($dhInstructors as $idx => $instData) {
            if (isset($instructors[$idx]) && isset($courses[$instData['course_idx']])) {
                $instructors[$idx]->update(['course_specializations' => [$courses[$instData['course_idx']]->id]]);
            }
        }
        
        $this->command->info('   ✓ Courses with packages created');

        // ── 4. INTERACTIONS (Link everything) ──

        // Course assignments for guests (completing the creation)
        $this->createDriveEdHubGuests($school, $courses, $admins_arr, $branches, $hashedPassword, false);

        // Time Slots & Assignments
        $this->createTimeSlotsAndAssignments($school, $instructors, $courses, $branches);

        // Bookings & Payments
        $this->createBookingsAndPayments($school, $students, $instructors, $courses, $branches, 10);

        // Notifications
        $this->createSampleNotifications($school, $students, $instructors, $admins_arr, $guests);

        $this->command->info('   ✓ Interactions created (Slots, Bookings, Enrollment Requests)');
    }

    private function createDriveEdHubCourses(School $school): array
    {
        $courses = [];

        // 1. TDC
        $tdc = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course (TDC)'],
            ['description' => 'LTO-accredited 15-hour TDC for new applicants. Covers traffic rules, road signs, and defensive driving.', 'type' => 'Theoretical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'features' => ['Traffic Rules & Regulations', 'Road Signs & Markings', 'Defensive Driving', 'LTO Written Exam Prep']]
        );
        $courses[] = $tdc;
        CoursePackage::updateOrCreate(['course_id' => $tdc->id, 'name' => 'TDC Standard 15-Hour'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 2000.00, 'description' => 'Complete 15-hour TDC for new license applicants.', 'is_popular' => true]);

        // 2. PDC
        $pdc = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course (PDC)'],
            ['description' => 'Hands-on practical driving. Master vehicle control, parking, and safe driving.', 'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'features' => ['Vehicle Operation Basics', 'Parking Techniques', 'City Driving']]
        );
        $courses[] = $pdc;
        CoursePackage::updateOrCreate(['course_id' => $pdc->id, 'name' => 'PDC 10-Hour Manual'], ['transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 10, 'price' => 5000.00, 'description' => 'Manual driving 10 hours', 'is_popular' => true]);
        CoursePackage::updateOrCreate(['course_id' => $pdc->id, 'name' => 'PDC 10-Hour Automatic'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 10, 'price' => 5500.00, 'description' => 'Automatic driving 10 hours']);

        // 3. COMBO
        $combo = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'TDC + PDC Combo Course'],
            ['description' => 'Complete beginner comprehensive package. TDC and PDC bundled together.', 'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'features' => ['15H Theory Classes', '10H Practical Hand-on', 'License Full Processing Help']]
        );
        $courses[] = $combo;
        CoursePackage::updateOrCreate(['course_id' => $combo->id, 'name' => 'Combo 15H TDC + 10H PDC'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 25, 'price' => 6800.00, 'description' => 'Full combined package.']);

        return $courses;
    }

    private function createDriveEdHubStudents(School $school, array $branches, string $password): array
    {
        $data = [
            ['name' => 'Juan Miguel Dela Cruz', 'email' => 'student1@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Maria Victoria Garcia', 'email' => 'student2@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Pedro Jose Santos', 'email' => 'student3@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Ana Patricia Reyes', 'email' => 'student4@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Carlos Manuel Mendoza', 'email' => 'student5@gmail.com', 'level' => 'experienced'],
            ['name' => 'Sofia Angelica Torres', 'email' => 'student6@gmail.com', 'level' => 'experienced'],
            ['name' => 'Miguel Francisco Ramos', 'email' => 'student7@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Isabella Rose Cruz', 'email' => 'student8@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Diego Emmanuel Fernandez', 'email' => 'student9@gmail.com', 'level' => 'new_driver'],
            ['name' => 'Luna Marie Martinez', 'email' => 'student10@gmail.com', 'level' => 'experienced'],
        ];

        $students = [];
        foreach ($data as $i => $s) {
            $student = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => $s['email']],
                [
                    'name' => $s['name'],
                    'branch_id' => $branches[$i % count($branches)]->id,
                    'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'password' => $password,
                    'status' => 'active',
                    'experience_level' => $s['level'],
                    'enrollment_date' => now()->subDays(rand(7, 60)),
                ]
            );
            $student->role = 'student';
            $student->email_verified_at = $student->email_verified_at ?? now();
            $student->verification_code = null;
            $student->verification_code_expires_at = null;
            $student->verification_attempts = 0;
            $student->last_verification_attempt_at = null;
            $student->save();
            $students[] = $student;
        }
        return $students;
    }

    private function createDriveEdHubGuests(School $school, array $courses, array $admins, array $branches, string $password, bool $onlyUsers = false): array
    {
        $guests = [];

        // Definition of guests to create
        $guestData = [
            'guest1@gmail.com' => ['name' => 'Elena Joy Reyes', 'license' => 'verified', 'exp' => 'new_driver', 'course_idx' => 0, 'status' => 'approved', 'pay' => 'paid'],
            'guest2@gmail.com' => ['name' => 'Mark Anthony Dizon', 'license' => 'verified', 'exp' => 'experienced', 'course_idx' => 2, 'status' => 'approved', 'pay' => 'paid', 'cancellation' => true],
            'guest3@gmail.com' => ['name' => 'Jamie Lyn Pascual', 'license' => 'none', 'exp' => 'new_driver'],
            'guest4@gmail.com' => ['name' => 'Carlo Miguel Bautista', 'license' => 'pending', 'exp' => 'new_driver', 'course_idx' => 1, 'status' => 'pending', 'pay' => 'pending'],
            'guest5@gmail.com' => ['name' => 'Angelica Mae Soriano', 'license' => 'none', 'exp' => 'new_driver', 'course_idx' => 2, 'status' => 'rejected', 'pay' => 'pending'],
            'guest6@gmail.com' => ['name' => 'Miguel Francisco Ramos', 'license' => 'verified', 'exp' => 'new_driver', 'course_idx' => 0, 'status' => 'approved', 'pay' => 'paid'],
            'guest7@gmail.com' => ['name' => 'Isabella Rose Cruz', 'license' => 'none', 'exp' => 'new_driver'],
            'guest8@gmail.com' => ['name' => 'Diego Fernandez', 'license' => 'pending', 'exp' => 'new_driver', 'course_idx' => 1, 'status' => 'pending', 'pay' => 'pending'],
            'guest9@gmail.com' => ['name' => 'Luna Martinez', 'license' => 'none', 'exp' => 'new_driver', 'course_idx' => 0, 'status' => 'rejected', 'pay' => 'pending'],
            'guest10@gmail.com' => ['name' => 'Sofia Torres', 'license' => 'verified', 'exp' => 'experienced', 'course_idx' => 2, 'status' => 'approved', 'pay' => 'paid'],
        ];

        foreach ($guestData as $email => $data) {
            $g = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => $email],
                [
                    'name' => $data['name'],
                    'contact' => '+63-919-800-' . rand(1000, 9999),
                    'password' => $password,
                    'status' => 'active',
                    'student_license_status' => $data['license'],
                    'student_license_verified_at' => $data['license'] === 'verified' ? now()->subDays(10) : null,
                    'experience_level' => $data['exp']
                ]
            );
            $g->role = 'guest';
            $g->email_verified_at = $g->email_verified_at ?? now();
            $g->verification_code = null;
            $g->verification_code_expires_at = null;
            $g->verification_attempts = 0;
            $g->last_verification_attempt_at = null;
            $g->save();
            $guests[] = $g;

            // If we're only creating users, skip the interaction logic
            if ($onlyUsers)
                continue;

            // Interaction logic (Enrollment Requests)
            if (isset($data['course_idx']) && isset($courses[$data['course_idx']])) {
                $course = $courses[$data['course_idx']];
                $package = CoursePackage::where('course_id', '=', $course->id)->first();

                $branch = $branches[rand(0, count($branches) - 1)];

                $ed = [
                    'status' => $data['status'],
                    'payment_status' => $data['pay'],
                    'experience_level' => $data['exp'],
                    'requested_license_type' => 'non_professional',
                    'branch_id' => $branch->id,
                    'price' => $package ? $package->price : 0,
                    'payment_method' => 'gcash',
                    'payment_reference' => 'DH-REF-' . strtoupper(\Illuminate\Support\Str::random(8)),
                ];

                if (isset($data['cancellation']) && $data['cancellation']) {
                    $ed['cancellation_requested'] = true;
                    $ed['cancellation_reason'] = 'Conflict with work schedule.';
                }

                if ($data['status'] === 'approved' && !empty($admins)) {
                    $ed['approved_by'] = $admins[0]->id;
                    $ed['approved_at'] = now()->subDays(5);
                    $ed['enrolled_at'] = now()->subDays(5);
                }

                if ($data['status'] === 'rejected') {
                    $ed['remarks'] = 'Incomplete documentation. Please re-submit with valid student license.';
                }

                \App\Models\EnrollmentRequest::updateOrCreate(
                    ['school_id' => $school->id, 'learner_id' => $g->id, 'course_id' => $course->id],
                    $ed
                );
            }
        }

        return $guests;
    }
}
