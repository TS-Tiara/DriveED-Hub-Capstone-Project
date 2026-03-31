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
        $hashedPassword = Hash::make('P@ssw0rd123');

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
        $admin = Admin::updateOrCreate(['email' => 'admin@gmail.com'], [
            'school_id' => $school->id,
            'name' => 'Antonio Francisco Reyes',
            'password' => $hashedPassword,
            'role' => 'school_admin',
            'is_active' => true,
        ]);
        Admin::updateOrCreate(['email' => 'manager.clark@drivedhub.com'], [
            'school_id' => $school->id,
            'branch_id' => $branches[0]->id,
            'name' => 'Patricia Lyn Mendoza',
            'password' => $hashedPassword,
            'role' => 'branch_secretary',
            'is_active' => true,
        ]);
        Admin::updateOrCreate(['email' => 'manager.balibago@drivedhub.com'], [
            'school_id' => $school->id,
            'branch_id' => $branches[1]->id,
            'name' => 'Gabriel Marco Santos',
            'password' => $hashedPassword,
            'role' => 'branch_secretary',
            'is_active' => true,
        ]);

        // Instructors
        $dhInstructors = [
            ['name' => 'Ricardo Antonio Cruz', 'email' => 'ricardo.cruz@drivedhub.com', 'contact' => '+63-919-777-3001', 'license' => 'LIC-DH-2024-001', 'bio' => 'Senior Instructor specializing in Manual Transmission and Motorcycle training. 8 years experience.', 'branch' => 0],
            ['name' => 'Maria Victoria Santos', 'email' => 'maria.santos@drivedhub.com', 'contact' => '+63-919-777-3002', 'license' => 'LIC-DH-2024-002', 'bio' => 'Expert in Automatic Transmission and Practical Driving. Certified defensive driving instructor.', 'branch' => 0],
            ['name' => 'Angelo Miguel Ramos', 'email' => 'angelo.ramos@drivedhub.com', 'contact' => '+63-919-777-3003', 'license' => 'LIC-DH-2024-003', 'bio' => 'TDC specialist. LTO-certified TDC instructor with 6 years experience.', 'branch' => 1],
            ['name' => 'Sofia Elena Torres', 'email' => 'sofia.torres@drivedhub.com', 'contact' => '+63-919-777-3004', 'license' => 'LIC-DH-2024-004', 'bio' => 'Motorcycle and Manual Transmission specialist. Former professional rider turned instructor.', 'branch' => 1],
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
        $admins_arr = [$admin];
        $guests = $this->createDriveEdHubGuests($school, [], $admins_arr, $branches, $hashedPassword, true); // Pass true to only create users

        $this->command->info('   ✓ All user identities created (Admins, Instructors, Students, Guests)');

        // ── 3. PRODUCTS (Courses) ──
        $courses = $this->createDriveEdHubCourses($school);
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

        // PDC 1 – Manual
        $pdc1 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course - Manual Transmission'],
            ['description' => 'Hands-on manual transmission driving training. Master clutch control, gear shifting, hill starts, and defensive driving.', 'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'features' => ['Manual Transmission', 'Clutch Control', 'Hill Start', 'Gear Shifting', 'Defensive Driving', 'Parking Techniques']]
        );
        $courses[] = $pdc1;
        CoursePackage::updateOrCreate(['course_id' => $pdc1->id, 'name' => 'Manual 10-Hour'], ['transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 10, 'price' => 6000.00, 'description' => 'Beginner manual driving – 10 hours.']);
        CoursePackage::updateOrCreate(['course_id' => $pdc1->id, 'name' => 'Manual 15-Hour'], ['transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 8500.00, 'description' => 'Complete manual driving with highway & city practice.', 'is_popular' => true]);
        CoursePackage::updateOrCreate(['course_id' => $pdc1->id, 'name' => 'Manual 20-Hour'], ['transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 20, 'price' => 10500.00, 'description' => 'Advanced manual driving – includes LTO exam preparation.']);

        // PDC 2 – Automatic
        $pdc2 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course - Automatic Transmission'],
            ['description' => 'Learn to drive automatic vehicles with confidence. Perfect for city driving and commuting.', 'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'features' => ['Automatic Transmission', 'City Driving', 'Highway Driving', 'Parking', 'Defensive Driving']]
        );
        $courses[] = $pdc2;
        CoursePackage::updateOrCreate(['course_id' => $pdc2->id, 'name' => 'Automatic 8-Hour'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 8, 'price' => 5000.00, 'description' => 'Quick starter course for automatic vehicles.']);
        CoursePackage::updateOrCreate(['course_id' => $pdc2->id, 'name' => 'Automatic 12-Hour'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 12, 'price' => 7000.00, 'description' => 'Complete automatic driving with city & highway practice.', 'is_popular' => true]);

        // PDC 3 – Motorcycle
        $pdc3 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course - Motorcycle'],
            ['description' => 'Comprehensive motorcycle riding course. From basic balance to highway riding.', 'type' => 'Practical', 'vehicle_type' => 'Motorcycle', 'status' => 'active', 'is_featured' => true, 'features' => ['Motorcycle Basics', 'Balance Training', 'Gear Shifting', 'Defensive Riding', 'Night Riding', 'License Preparation']]
        );
        $courses[] = $pdc3;
        CoursePackage::updateOrCreate(['course_id' => $pdc3->id, 'name' => 'Motorcycle 6-Hour'], ['transmission_type' => 'manual', 'vehicle_type' => 'Motorcycle', 'training_hours' => 6, 'price' => 3500.00, 'description' => 'Basic motorcycle riding fundamentals.']);
        CoursePackage::updateOrCreate(['course_id' => $pdc3->id, 'name' => 'Motorcycle 10-Hour'], ['transmission_type' => 'manual', 'vehicle_type' => 'Motorcycle', 'training_hours' => 10, 'price' => 5500.00, 'description' => 'Complete motorcycle course with road practice.', 'is_popular' => true]);

        // TDC 1 – Standard
        $tdc1 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course - Standard (TDC)'],
            ['description' => 'LTO-accredited 15-hour TDC for new applicants. Covers traffic rules, road signs, and defensive driving.', 'type' => 'Theoretical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'features' => ['Traffic Rules & Regulations', 'Road Signs & Markings', 'Defensive Driving', 'Vehicle Operation Basics', 'LTO Written Exam Prep', 'TDC Certificate']]
        );
        $courses[] = $tdc1;
        CoursePackage::updateOrCreate(['course_id' => $tdc1->id, 'name' => 'TDC Standard 15-Hour'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 2500.00, 'description' => 'Complete 15-hour TDC for new license applicants.', 'is_popular' => true]);

        // TDC 2 – Refresher
        $tdc2 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course - Refresher (TDC-R)'],
            ['description' => 'Shortened TDC refresher for license renewal, reinstatement, or returning drivers.', 'type' => 'Theoretical', 'vehicle_type' => 'Car', 'status' => 'active', 'features' => ['Updated Traffic Laws', 'Road Safety Refresher', 'Anti-Distracted Driving Act', 'Quick License Renewal Prep', 'TDC-R Certificate']]
        );
        $courses[] = $tdc2;
        CoursePackage::updateOrCreate(['course_id' => $tdc2->id, 'name' => 'TDC Refresher 8-Hour'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 8, 'price' => 1500.00, 'description' => '8-hour refresher course for experienced drivers.']);

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
            'guest.enrolled1@drivedhub.test' => ['name' => 'Elena Joy Reyes', 'license' => 'verified', 'exp' => 'new_driver', 'course_idx' => 0, 'status' => 'approved', 'pay' => 'paid'],
            'guest.enrolled2@drivedhub.test' => ['name' => 'Mark Anthony Dizon', 'license' => 'verified', 'exp' => 'experienced', 'course_idx' => 3, 'status' => 'approved', 'pay' => 'paid', 'cancellation' => true],
            'guest.new1@drivedhub.test' => ['name' => 'Jamie Lyn Pascual', 'license' => 'none', 'exp' => 'new_driver'],
            'guest.pending@drivedhub.test' => ['name' => 'Carlo Miguel Bautista', 'license' => 'pending', 'exp' => 'new_driver', 'course_idx' => 1, 'status' => 'pending', 'pay' => 'pending'],
            'guest.rejected@drivedhub.test' => ['name' => 'Angelica Mae Soriano', 'license' => 'none', 'exp' => 'new_driver', 'course_idx' => 2, 'status' => 'rejected', 'pay' => 'pending'],
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
