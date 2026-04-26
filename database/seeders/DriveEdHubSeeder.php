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
use App\Models\CourseModule;
use App\Models\ModuleLesson;
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
        $admin = Admin::updateOrCreate(['email' => 'admin1@driveedhub.test'], [
            'school_id' => $school->id,
            'name' => 'Antonio Francisco Reyes',
            'password' => $hashedPassword,
            'role' => 'school_admin',
            'is_active' => true,
        ]);
        $admin2 = Admin::updateOrCreate(['email' => 'admin2@driveedhub.test'], [
            'school_id' => $school->id,
            'name' => 'School Admin Two',
            'password' => $hashedPassword,
            'role' => 'school_admin',
            'is_active' => true,
        ]);
        Admin::updateOrCreate(['email' => 'manager1@driveedhub.test'], [
            'school_id' => $school->id,
            'branch_id' => $branches[0]->id,
            'name' => 'Patricia Lyn Mendoza',
            'password' => $hashedPassword,
            'role' => 'branch_secretary',
            'is_active' => true,
        ]);
        Admin::updateOrCreate(['email' => 'manager2@driveedhub.test'], [
            'school_id' => $school->id,
            'branch_id' => $branches[1]->id,
            'name' => 'Gabriel Marco Santos',
            'password' => $hashedPassword,
            'role' => 'branch_secretary',
            'is_active' => true,
        ]);

        // ── 3. PRODUCTS (Courses) ──
        $courses = $this->createDriveEdHubCourses($school);

        // Instructors
        $dhInstructors = [
            ['name' => 'Ricardo Antonio Cruz', 'email' => 'instructor1@driveedhub.test', 'contact' => '+63-919-777-3001', 'license' => 'LIC-DH-2024-001', 'bio' => 'Senior Instructor specializing in Manual Transmission and Motorcycle training. 8 years experience.', 'branch' => 0, 'course_idx' => 0], // TDC
            ['name' => 'Maria Victoria Santos', 'email' => 'instructor2@driveedhub.test', 'contact' => '+63-919-777-3002', 'license' => 'LIC-DH-2024-002', 'bio' => 'Expert in Automatic Transmission and Practical Driving. Certified defensive driving instructor.', 'branch' => 0, 'course_idx' => 0], // TDC
            ['name' => 'Angelo Miguel Ramos', 'email' => 'instructor3@driveedhub.test', 'contact' => '+63-919-777-3003', 'license' => 'LIC-DH-2024-003', 'bio' => 'TDC specialist. LTO-certified TDC instructor with 6 years experience.', 'branch' => 1, 'course_idx' => 1], // PDC
            ['name' => 'Sofia Elena Torres', 'email' => 'instructor4@driveedhub.test', 'contact' => '+63-919-777-3004', 'license' => 'LIC-DH-2024-004', 'bio' => 'Motorcycle and Manual Transmission specialist. Former professional rider turned instructor.', 'branch' => 1, 'course_idx' => 1], // PDC
            ['name' => 'Juan Dela Cruz Jr.', 'email' => 'instructor5@driveedhub.test', 'contact' => '+63-919-777-3005', 'license' => 'LIC-DH-2024-005', 'bio' => 'Defensive driving specialist.', 'branch' => 0, 'course_idx' => 2], // COMBO
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
        $students = $this->createDriveEdHubStudents($school, $branches, $courses, $hashedPassword);

        // Guests (Base records only, enrollment logic separated later or handled in createDriveEdHubGuests)
        $admins_arr = [$admin, $admin2];
        $guests = $this->createDriveEdHubGuests($school, [], $admins_arr, $branches, $hashedPassword, true); // Pass true to only create users

        $this->command->info('   ✓ All user identities created (Admins, Instructors, Students, Guests)');

        // Link instructors strictly to their primary course for testing specializations
        foreach ($dhInstructors as $idx => $instData) {
            if (isset($instructors[$idx]) && isset($courses[$instData['course_idx']])) {
                $instructors[$idx]->update(['course_specializations' => [$courses[$instData['course_idx']]->id]]);
            }
        }

        $this->command->info('   ✓ Courses with packages created');

        // ── 3.5 SYLLABUS (Ported from ContentProgressSeeder) ──
        $this->seedCourseContentForDriveEdHub($school, $courses);

        // ── 4. INTERACTIONS (Link everything) ──

        // Course assignments for guests (completing the creation)
        $this->createDriveEdHubGuests($school, $courses, $admins_arr, $branches, $hashedPassword, false);

        // Clean, Open Scheduling (Today -> Friday)
        $this->createTimeSlotsAndAssignments($school, $instructors, $courses, $branches);

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
            ['description' => 'LTO-accredited 15-hour TDC for new applicants. Covers traffic rules, road signs, and defensive driving.', 'type' => 'Theoretical', 'course_type' => 'theoretical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'features' => ['Traffic Rules & Regulations', 'Road Signs & Markings', 'Defensive Driving', 'LTO Written Exam Prep']]
        );
        $courses[] = $tdc;
        CoursePackage::updateOrCreate(['course_id' => $tdc->id, 'name' => 'TDC Standard 15-Hour'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 2000.00, 'description' => 'Complete 15-hour TDC for new license applicants.', 'is_popular' => true]);

        // 2. PDC
        $pdc = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course (PDC)'],
            ['description' => 'Hands-on practical driving. Master vehicle control, parking, and safe driving.', 'type' => 'Practical', 'course_type' => 'practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'features' => ['Vehicle Operation Basics', 'Parking Techniques', 'City Driving']]
        );
        $courses[] = $pdc;
        CoursePackage::updateOrCreate(['course_id' => $pdc->id, 'name' => 'PDC 10-Hour Manual'], ['transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 10, 'price' => 5000.00, 'description' => 'Manual driving 10 hours', 'is_popular' => true]);
        CoursePackage::updateOrCreate(['course_id' => $pdc->id, 'name' => 'PDC 10-Hour Automatic'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 10, 'price' => 5500.00, 'description' => 'Automatic driving 10 hours']);

        // 3. COMBO
        $combo = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'TDC + PDC Combo Course'],
            ['description' => 'Complete beginner comprehensive package. TDC and PDC bundled together.', 'type' => 'Combo', 'course_type' => 'combo', 'vehicle_type' => 'Car', 'status' => 'active', 'features' => ['15H Theory Classes', '10H Practical Hand-on', 'License Full Processing Help']]
        );
        $courses[] = $combo;
        CoursePackage::updateOrCreate(['course_id' => $combo->id, 'name' => 'Combo 15H TDC + 10H PDC'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 25, 'price' => 6800.00, 'description' => 'Full combined package.']);

        return $courses;
    }

    private function createDriveEdHubStudents(School $school, array $branches, array $courses, string $password): array
    {
        $data = [
            ['name' => 'Juan Miguel Dela Cruz', 'email' => 'student1@driveedhub.test', 'level' => 'new_driver'],
            ['name' => 'Maria Victoria Garcia', 'email' => 'student2@driveedhub.test', 'level' => 'new_driver'],
            ['name' => 'Pedro Jose Santos', 'email' => 'student3@driveedhub.test', 'level' => 'new_driver'],
            ['name' => 'Ana Patricia Reyes', 'email' => 'student4@driveedhub.test', 'level' => 'new_driver'],
            ['name' => 'Carlos Manuel Mendoza', 'email' => 'student5@driveedhub.test', 'level' => 'experienced'],
            ['name' => 'Sofia Angelica Torres', 'email' => 'student6@driveedhub.test', 'level' => 'experienced'],
            ['name' => 'Miguel Francisco Ramos', 'email' => 'student7@driveedhub.test', 'level' => 'new_driver'],
            ['name' => 'Isabella Rose Cruz', 'email' => 'student8@driveedhub.test', 'level' => 'new_driver'],
            ['name' => 'Diego Emmanuel Fernandez', 'email' => 'student9@driveedhub.test', 'level' => 'new_driver'],
            ['name' => 'Luna Marie Martinez', 'email' => 'student10@driveedhub.test', 'level' => 'experienced'],
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

            // Ensure every student has an approved/paid enrollment request so they aren't "ghosts"
            $course = $courses[$i % count($courses)];
            $package = \App\Models\CoursePackage::where('course_id', $course->id)->first();
            
            \App\Models\EnrollmentRequest::updateOrCreate(
                ['school_id' => $school->id, 'learner_id' => $student->id, 'course_id' => $course->id],
                [
                    'status' => 'approved',
                    'payment_status' => 'paid',
                    'experience_level' => $s['level'],
                    'requested_license_type' => 'non_professional',
                    'branch_id' => $student->branch_id,
                    'price' => $package ? $package->price : 0,
                    'payment_method' => 'cash',
                    'payment_reference' => 'DH-SEED-' . strtoupper(\Illuminate\Support\Str::random(8)),
                    'approved_by' => 1, // System/Admin
                    'approved_at' => now()->subDays(10),
                    'enrolled_at' => now()->subDays(10),
                ]
            );
        }
        return $students;
    }

    private function createDriveEdHubGuests(School $school, array $courses, array $admins, array $branches, string $password, bool $onlyUsers = false): array
    {
        $guests = [];

        // Definition of guests to create
        $guestData = [
            'guest1@driveedhub.test' => ['name' => 'Elena Joy Reyes', 'license' => 'verified', 'exp' => 'new_driver', 'course_idx' => 0, 'status' => 'approved', 'pay' => 'paid'],
            'guest2@driveedhub.test' => ['name' => 'Mark Anthony Dizon', 'license' => 'verified', 'exp' => 'experienced', 'course_idx' => 2, 'status' => 'approved', 'pay' => 'paid', 'cancellation' => true],
            'guest3@driveedhub.test' => ['name' => 'Jamie Lyn Pascual', 'license' => 'none', 'exp' => 'new_driver'],
            'guest4@driveedhub.test' => ['name' => 'Carlo Miguel Bautista', 'license' => 'pending', 'exp' => 'new_driver', 'course_idx' => 1, 'status' => 'pending', 'pay' => 'pending'],
            'guest5@driveedhub.test' => ['name' => 'Angelica Mae Soriano', 'license' => 'none', 'exp' => 'new_driver', 'course_idx' => 2, 'status' => 'rejected', 'pay' => 'cancelled'],
            'guest6@driveedhub.test' => ['name' => 'Miguel Francisco Ramos', 'license' => 'verified', 'exp' => 'new_driver', 'course_idx' => 0, 'status' => 'approved', 'pay' => 'paid'],
            'guest7@driveedhub.test' => ['name' => 'Isabella Rose Cruz', 'license' => 'none', 'exp' => 'new_driver'],
            'guest8@driveedhub.test' => ['name' => 'Diego Fernandez', 'license' => 'pending', 'exp' => 'new_driver', 'course_idx' => 1, 'status' => 'pending', 'pay' => 'pending'],
            'guest9@driveedhub.test' => ['name' => 'Luna Martinez', 'license' => 'none', 'exp' => 'new_driver', 'course_idx' => 0, 'status' => 'rejected', 'pay' => 'cancelled'],
            'guest10@driveedhub.test' => ['name' => 'Sofia Torres', 'license' => 'verified', 'exp' => 'experienced', 'course_idx' => 2, 'status' => 'approved', 'pay' => 'paid'],
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
                    'experience_level' => $data['exp'],
                    'branch_id' => $branches[array_search($email, array_keys($guestData)) % count($branches)]->id,
                ]
            );
            $g->role = 'guest';
            $g->email_verified_at = $g->email_verified_at ?? now();
            $g->verification_code = null;
            $g->verification_code_expires_at = null;
            $g->verification_attempts = 0;
            $g->last_verification_attempt_at = null;
            $g->save();

            // Promote to student if approved
            if (isset($data['status']) && $data['status'] === 'approved') {
                $g->update(['role' => 'student']);
            }

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
                    $ed['rejected_at'] = now()->subDays(2);
                    $ed['payment_status'] = 'cancelled';
                    
                    // Sync learner license status for rejected enrollments
                    $g->update([
                        'student_license_status' => 'rejected',
                        'student_license_rejection_reason' => $ed['remarks']
                    ]);
                }

                if ($data['status'] === 'cancelled' || ($data['cancellation'] ?? false)) {
                    $ed['status'] = 'cancelled';
                    $ed['cancelled_at'] = now()->subDays(1);
                    $ed['payment_status'] = 'cancelled';
                    
                    // Sync learner license status for cancelled enrollments
                    if ($g->student_license_status === 'pending') {
                        $g->update(['student_license_status' => 'none']);
                    }
                }

                \App\Models\EnrollmentRequest::updateOrCreate(
                    ['school_id' => $school->id, 'learner_id' => $g->id, 'course_id' => $course->id],
                    $ed
                );
            }
        }

        return $guests;
    }

    // ================================================================
    //  SYLLABUS / CURRICULUM PORTED FROM CONTENTPROGRESSSEEDER
    // ================================================================

    private function seedCourseContentForDriveEdHub(School $school, array $courses): void
    {
        $this->command->info('');
        $this->command->info("   Creating course syllabus constraints...");

        foreach ($courses as $course) {
            $isTheoretical = in_array(strtolower($course->type), ['theoretical']) || ($course->course_type === 'theoretical');

            $modules = $isTheoretical ? $this->getTheoreticalModules() : $this->getPracticalModules($course);

            foreach ($modules as $sortOrder => $moduleData) {
                $module = CourseModule::updateOrCreate(
                    ['school_id' => $school->id, 'course_id' => $course->id, 'title' => $moduleData['title']],
                    ['description' => $moduleData['description'], 'module_type' => $moduleData['module_type'], 'sort_order' => $sortOrder + 1]
                );

                foreach ($moduleData['lessons'] as $lSort => $lesson) {
                    ModuleLesson::updateOrCreate(
                        ['school_id' => $school->id, 'module_id' => $module->id, 'title' => $lesson['title']],
                        [
                            'content' => $lesson['content'],
                            'video_url' => $lesson['video_url'] ?? null,
                            'attachments' => $lesson['attachments'] ?? null,
                            'sort_order' => $lSort + 1,
                        ]
                    );
                }
            }
        }
        $this->command->info("   ✓ Syllabus inserted.");
    }

    private function getTheoreticalModules(): array
    {
        return [
            [
                'title' => 'Introduction to Philippine Traffic Laws',
                'description' => 'Overview of Republic Act 4136, RA 10913, and other relevant traffic legislation.',
                'module_type' => 'theoretical',
                'lessons' => [
                    ['title' => 'History of Philippine Traffic Laws', 'content' => "This lesson covers the evolution of traffic legislation in the Philippines.\n\n## Key Laws\n- **RA 4136** - Land Transportation and Traffic Code\n- **RA 10913** - Anti-Distracted Driving Act\n- **RA 10586** - Anti-Drunk and Drugged Driving Act\n- **RA 11229** - Child Safety in Motor Vehicles Act\n\n## Learning Objectives\n1. Identify the major traffic laws\n2. Understand the penalties for common violations\n3. Know the rights and responsibilities of drivers"],
                    ['title' => 'LTO Rules and Regulations', 'content' => "The Land Transportation Office (LTO) is the primary government agency responsible for driver licensing.\n\n## License Categories\n- **Student Permit** - Valid for 1 year\n- **Non-Professional License** - For private vehicle use\n- **Professional License** - For public utility and commercial vehicles\n\n## Requirements\n1. Valid student permit\n2. TDC Certificate\n3. PDC Certificate\n4. Medical certificate\n5. Drug test clearance"],
                    ['title' => 'Penalties and Fines Schedule', 'content' => "Understanding penalties helps drivers maintain proper road behavior.\n\n## Common Violations and Fines\n| Violation | First Offense | Second Offense |\n|-----------|--------------|----------------|\n| No license | P3,000 | P5,000 |\n| Beating red light | P1,000 | P2,000 |\n| Over-speeding | P1,000-P2,000 | P2,000-P5,000 |", 'video_url' => 'https://www.youtube.com/watch?v=example_traffic_fines'],
                ],
            ],
            [
                'title' => 'Road Signs, Signals, and Markings',
                'description' => 'Complete guide to Philippine road signs, traffic signals, and pavement markings.',
                'module_type' => 'theoretical',
                'lessons' => [
                    ['title' => 'Regulatory Signs', 'content' => "Regulatory signs inform road users of traffic laws.\n\n## Categories\n### Prohibitory Signs\n- No Entry\n- No Left/Right Turn\n- No U-Turn\n- Speed Limit\n\n### Mandatory Signs\n- Keep Right/Left\n- Roundabout\n\nRegulatory signs MUST be obeyed."],
                    ['title' => 'Warning Signs', 'content' => "Warning signs alert drivers to potential hazards. They are typically diamond-shaped with a yellow background.\n\n## Common Warning Signs\n- Curve Ahead\n- Steep Grade\n- Slippery When Wet\n- Pedestrian Crossing\n- School Zone\n\nWhen you see a warning sign, reduce speed and be prepared."],
                    ['title' => 'Pavement Markings and Traffic Signals', 'content' => "## Pavement Markings\n- **Solid Yellow Line** - No overtaking zone\n- **Broken White Line** - Lane division, overtaking permitted\n- **Solid White Line** - Edge of road\n- **Crosswalk** - Pedestrian crossing area\n\n## Traffic Signals\n- **Green** - Proceed with caution\n- **Yellow** - Prepare to stop\n- **Red** - Full stop\n- **Flashing Red** - Treat as STOP sign", 'video_url' => 'https://www.youtube.com/watch?v=example_road_signs'],
                ],
            ],
            [
                'title' => 'Defensive Driving and Road Safety',
                'description' => 'Principles of defensive driving, hazard perception, and accident prevention.',
                'module_type' => 'theoretical',
                'lessons' => [
                    ['title' => 'Principles of Defensive Driving', 'content' => "Defensive driving means preventing accidents regardless of what other drivers do.\n\n## SIPDE Process\n1. **S**can - Check surroundings\n2. **I**dentify - Recognize hazards\n3. **P**redict - Anticipate behavior\n4. **D**ecide - Choose best response\n5. **E**xecute - Act on your decision\n\n## Smith System\n1. Aim high in steering\n2. Get the big picture\n3. Keep your eyes moving\n4. Leave yourself an out\n5. Make sure they see you"],
                    ['title' => 'Common Driving Hazards in the Philippines', 'content' => "Philippine roads present unique challenges.\n\n## Common Hazards\n- **Flooding** - Avoid driving through deep flood waters\n- **Jaywalkers** - Always watch for pedestrians\n- **Motorcycles weaving** - Check mirrors frequently\n- **Construction zones** - Reduce speed\n\n## Emergency Procedures\n1. Tire blowout: Grip steering, ease off gas\n2. Brake failure: Pump brakes, use engine braking\n3. Engine fire: Pull over, turn off engine, evacuate"],
                ],
            ],
        ];
    }

    private function getPracticalModules(object $course): array
    {
        $isManual = stripos($course->title, 'manual') !== false;

        return [
            [
                'title' => 'Vehicle Familiarization',
                'description' => 'Understanding vehicle controls, dashboard indicators, and pre-drive checks.',
                'module_type' => 'practical_prep',
                'lessons' => [
                    ['title' => 'Vehicle Controls and Dashboard', 'content' => "Familiarize yourself with all vehicle controls before driving.\n\n## Primary Controls\n- **Steering Wheel** - Turn to steer\n- **Accelerator** (right pedal) - Controls speed\n- **Brake** (middle pedal) - Slows and stops\n" . ($isManual ? "- **Clutch** (left pedal) - Transmission engagement\n" : "- **Gear Selector** - P, R, N, D\n") . "\n## Dashboard Indicators\n- Red = Stop immediately\n- Yellow = Caution\n- Green/Blue = Information\n\n## Pre-Drive Routine\n1. Adjust seat and mirrors\n2. Fasten seatbelt\n3. Check mirrors\n4. Start engine"],
                    ['title' => 'Seat Position, Mirrors, and Safety', 'content' => "## Seat Adjustment\n- Feet reach all pedals comfortably\n- Knees slightly bent\n- Arms slightly bent at 9-and-3\n\n## Mirror Setup\n- Rearview: frame rear window\n- Left mirror: lean left, barely see car\n- Right mirror: lean right, barely see car\n\nAlways wear your seatbelt (RA 8750)."],
                ],
            ],
            [
                'title' => ($isManual ? 'Clutch Control and Gear Shifting' : 'Basic Driving Techniques'),
                'description' => $isManual
                    ? 'Master the friction zone, smooth gear changes, and hill starts.'
                    : 'Steering, braking, accelerating, and basic maneuvering.',
                'module_type' => 'practical_prep',
                'lessons' => $isManual ? [
                    ['title' => 'Understanding the Friction Zone', 'content' => "The friction zone is where the clutch begins to engage.\n\n## Finding It\n1. Press clutch fully\n2. Shift into 1st gear\n3. Slowly release clutch until car starts to pull\n4. That point = friction zone\n\n## Common Mistakes\n- Releasing clutch too fast = stall\n- Too much gas + slow clutch = excessive wear\n- Riding the clutch = premature wear"],
                    ['title' => 'Gear Shifting Patterns', 'content' => "## Upshifting\n1. Release accelerator\n2. Press clutch fully\n3. Move gear lever to next gear\n4. Release clutch smoothly with gas\n\n## When to Shift\n| Gear | Speed Range |\n|------|------------|\n| 1st | 0-15 km/h |\n| 2nd | 15-30 km/h |\n| 3rd | 30-45 km/h |\n| 4th | 45-60 km/h |\n| 5th | 60+ km/h |"],
                    ['title' => 'Hill Start Technique', 'content' => "## Handbrake Method\n1. Stop on hill with handbrake engaged\n2. Press clutch, shift to 1st\n3. Find friction zone\n4. Release handbrake while adding gas\n5. Fully release clutch once moving\n\n## Tips\n- Start on gentle inclines first\n- Use handbrake to prevent rolling"],
                ] : [
                    ['title' => 'Smooth Acceleration and Braking', 'content' => "## Acceleration\n- Press accelerator gradually\n- Maintain steady pressure for constant speed\n- Ease off before curves\n\n## Braking\n- Progressive braking: start light, increase, ease off before stop\n- Keep both hands on wheel\n- 3-second following distance rule"],
                    ['title' => 'Steering Techniques', 'content' => "## Hand Position\n- 9-and-3 position (recommended for airbag safety)\n- Keep both hands on wheel\n\n## Methods\n- Push-pull: Push up one hand, pull down other\n- Hand-over-hand: For tight turns\n\n## Avoid\n- One-hand driving\n- Palming the wheel\n- Over-correcting"],
                ],
            ],
            [
                'title' => 'On-Road Driving Skills',
                'description' => 'City driving, highway driving, parking, and real-world practice.',
                'module_type' => 'practical_prep',
                'lessons' => [
                    ['title' => 'City and Urban Driving', 'content' => "City driving requires constant awareness.\n\n## Key Skills\n- Intersection management (check left-right-left)\n- Stay centered in lane\n- 3+ seconds following distance\n- Check blind spots before lane changes\n\n## Common Hazards\n- Pedestrians crossing unexpectedly\n- Motorcycles splitting lanes\n- Buses stopping suddenly\n- Doors opening from parked cars"],
                    ['title' => 'Parking Techniques', 'content' => "## Perpendicular (90 degrees) Parking\n1. Signal and slow down\n2. Position 1m from parked cars\n3. When shoulder aligns with space, turn fully\n4. Straighten when centered\n\n## Parallel Parking\n1. Pull alongside car in front\n2. Reverse and turn toward curb\n3. Straighten briefly at 45 degrees\n4. Turn away from curb\n5. Straighten and center\n\n## Hill Parking\n- Uphill with curb: wheels LEFT\n- Downhill with curb: wheels RIGHT\n- Always engage parking brake", 'video_url' => 'https://www.youtube.com/watch?v=example_parking'],
                ],
            ],
        ];
    }

    // ================================================================
    //  OVERRIDE: Clean Scheduling (Today -> Friday, Unassigned)
    // ================================================================

    protected function createTimeSlotsAndAssignments(School $school, array $instructors, array $courses, array $branches): void
    {
        $this->command->info('   Generating clean scheduling blocks (Today until Friday)...');

        $times = [
            ['08:00:00', '10:00:00'],
            ['10:00:00', '12:00:00'],
            ['13:00:00', '15:00:00'],
            ['15:00:00', '17:00:00'],
        ];

        // Start today, end when we process Friday
        $now = now();
        $daysOffset = -3; // Start 3 days ago to show history
        $branchIdx = 0;
        $allCreatedSlots = [];

        for ($d = 0; $d < 8; $d++) { // 3 past + 5 future = 8 days
            $dateObj = $now->copy()->addDays($daysOffset);

            // Skip Sundays if applicable
            if ($dateObj->dayOfWeek == 0) {
                $daysOffset++;
                continue;
            }

            $date = $dateObj->format('Y-m-d');

            // Create slots across all courses
            foreach ($courses as $i => $course) {
                // Determine 2 to 4 slots per course per day
                $slotCount = rand(2, 4);
                $timeIndices = (array) array_rand($times, $slotCount);

                foreach ($timeIndices as $idx => $timeIndex) {
                    $branch = $branches[$branchIdx % count($branches)];
                    $branchIdx++;

                    // Determine Session Type (No Guessing)
                    $resolvedSessionType = $course->course_type;
                    if ($course->course_type === 'combo') {
                        // For combo, alternate based on index
                        $resolvedSessionType = ($idx % 2 === 0) ? 'theoretical' : 'practical';
                    }

                    $isTheoretical = $resolvedSessionType === 'theoretical';

                    $timeSlot = \App\Models\TimeSlot::create([
                        'school_id' => $school->id,
                        'branch_id' => $branch->id,
                        'course_id' => $course->id,
                        'session_type' => $resolvedSessionType, // Explicitly set to avoid guessing
                        'date' => $date,
                        'start_time' => $times[$timeIndex][0],
                        'end_time' => $times[$timeIndex][1],
                        'status' => 'open',
                        'max_instructors' => 1,
                        'max_students' => $isTheoretical ? rand(15, 25) : 1, // Classroom vs 1-on-1
                    ]);

                    // Assign an instructor to about 80% of slots
                    if (($i + $idx) % 5 !== 0) {
                        $instructor = $instructors[($i + $idx) % count($instructors)];
                        $timeSlot->instructors()->attach($instructor->id, [
                            'school_id' => $school->id,
                            'assignment_type' => 'admin_assigned',
                        ]);
                        $allCreatedSlots[] = $timeSlot;
                    }
                }
            }

            if ($dateObj->dayOfWeek == 5) break;
            $daysOffset++;
        }

        // Now create some bookings to show the "Who is assigned to who" feature
        $this->createSampleBookings($school, $allCreatedSlots);
    }

    private function createSampleBookings(School $school, array $slots): void
    {
        $this->command->info('   Creating distributed sample bookings (Done, Verified, Cancelled, Scheduled)...');
        
        $students = \App\Models\Student::where('school_id', $school->id)
            ->where('role', 'student')
            ->limit(15)
            ->get();

        if ($students->isEmpty()) return;

        $studentIdx = 0;
        $statuses = [
            'done',       // Awaiting Verification
            'completed',  // Verified
            'scheduled',  // Future
            'cancelled',  // Flagged
            'no-show',    // Flagged
            'scheduled',  // Future
            'done',       // Awaiting Verification
        ];

        foreach ($slots as $idx => $slot) {
            // Only book for slots that have an instructor
            $instructor = $slot->instructors->first();
            if (!$instructor) continue;

            // Determine if the slot is in the past or future
            $slotDate = \Carbon\Carbon::parse($slot->date);
            $isPast = $slotDate->isPast();

            // Determine how many students to book
            $numToBook = $slot->course->course_type === 'theoretical' ? rand(3, 5) : 1;

            for ($i = 0; $i < $numToBook; $i++) {
                $student = $students[$studentIdx % $students->count()];
                $studentIdx++;

                // Logic-based status selection
                if ($isPast) {
                    // Past slots are either Completed (Verified), Done (Awaiting), No-Show, or Cancelled
                    $pastRoll = rand(1, 100);
                    if ($pastRoll <= 50) $status = 'completed';      // 50% Verified
                    elseif ($pastRoll <= 75) $status = 'done';        // 25% Awaiting Verification
                    elseif ($pastRoll <= 90) $status = 'no-show';     // 15% No-Show
                    else $status = 'cancelled';                       // 10% Voided
                } else {
                    // Future slots are always Scheduled
                    $status = 'scheduled';
                }

                // Find enrollment request for this student/course
                $enrollmentRequest = \App\Models\EnrollmentRequest::where('learner_id', $student->id)
                    ->where('course_id', $slot->course_id)
                    ->first();

                $booking = \App\Models\Booking::create([
                    'school_id' => $school->id,
                    'branch_id' => $slot->branch_id,
                    'student_id' => $student->id,
                    'instructor_id' => $instructor->id,
                    'course_id' => $slot->course_id,
                    'enrollment_request_id' => $enrollmentRequest ? $enrollmentRequest->id : null,
                    'time_slot_id' => $slot->id,
                    'booking_date' => $slot->date,
                    'scheduled_at' => \Carbon\Carbon::parse(($slot->date instanceof \Carbon\Carbon ? $slot->date->toDateString() : $slot->date) . ' ' . $slot->start_time),
                    'status' => $status,
                    'attendance_status' => in_array($status, ['done', 'completed']) ? 'present' : ($status === 'no-show' ? 'absent' : 'pending'),
                    'payment_status' => 'paid',
                ]);

                // Create SessionCompletion for 'completed' bookings to populate training logs and hours
                if ($status === 'completed' && $enrollmentRequest) {
                    \App\Models\SessionCompletion::create([
                        'school_id' => $school->id,
                        'enrollment_id' => $enrollmentRequest->id,
                        'instructor_id' => $instructor->id,
                        'session_type' => $slot->session_type,
                        'session_date' => $slot->date,
                        'session_time' => $slot->start_time . ' - ' . $slot->end_time,
                        'hours_completed' => 2.0, 
                        'notes' => 'Session verified and completed.',
                    ]);
                }
            }
            
            // Limit to a reasonable amount of data
            if ($studentIdx > 60) break;
        }
    }
}
