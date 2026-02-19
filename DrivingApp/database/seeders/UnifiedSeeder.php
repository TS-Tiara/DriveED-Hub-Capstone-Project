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
use App\Models\Branch;

/**
 * Unified Seeder - Comprehensive Test Data
 * 
 * This seeder creates a complete test environment with:
 * - 2 System Administrators (platform level)
 * - Smart Driving School (25 branches)
 * - LySpeed Driving School (10 branches)
 * - DriveED Hub Driving School (2 branches, demo school)
 * 
 * Run with: php artisan db:seed --class=UnifiedSeeder
 * 
 * All passwords: "P@ssw0rd123" (except system admins: "P@ssw0rd123" as well)
 */
class UnifiedSeeder extends Seeder
{
    private string $password;

    public function __construct()
    {
        $this->password = 'P@ssw0rd123';
    }

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

    // ═══════════════════════════════════════════════════════════════
    //  SYSTEM ADMINISTRATORS
    // ═══════════════════════════════════════════════════════════════

    private function createSystemAdmins(): void
    {
        $this->command->info('🔐 Creating System Administrators...');

        Admin::updateOrCreate(
            ['email' => 'systemadmin@gmail.com'],
            [
                'school_id' => null,
                'name' => 'Tiara Angelica Santos',
                'password' => Hash::make($this->password),
                'role' => 'system_admin',
                'is_active' => true,
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'systemadmin2@gmail.com'],
            [
                'school_id' => null,
                'name' => 'Ricardo Jose Dela Cruz',
                'password' => Hash::make($this->password),
                'role' => 'system_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('   ✓ 2 System Administrators created');
    }

    // ═══════════════════════════════════════════════════════════════
    //  SMART DRIVING SCHOOL  (25 branches)
    // ═══════════════════════════════════════════════════════════════

    private function createSmartDrivingSchool(): void
    {
        $this->command->info('');
        $this->command->info('🏫 Creating Smart Driving School (25 branches)...');

        $school = School::updateOrCreate(
            ['slug' => 'smart-driving'],
            [
                'name' => 'Smart Driving School',
                'timezone' => 'Asia/Manila',
                'branding' => json_encode([
                    'logo' => null,
                    'colors' => ['primary' => '#3b82f6', 'secondary' => '#1e40af', 'accent' => '#f59e0b'],
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
                'enable_branches' => true,
            ]
        );

        // ── 25 Branches ──
        $branchList = [
            ['name' => 'Main Branch - Angeles City', 'address' => '123 MacArthur Highway, Angeles City, Pampanga', 'contact_number' => '+63-917-123-4501', 'email' => 'angeles@smartdriving.com'],
            ['name' => 'Clark Branch', 'address' => '45 M.A. Roxas Highway, Clark Freeport Zone, Pampanga', 'contact_number' => '+63-917-123-4502', 'email' => 'clark@smartdriving.com'],
            ['name' => 'Dau Branch', 'address' => '789 Jose Abad Santos Ave, Dau, Mabalacat, Pampanga', 'contact_number' => '+63-917-123-4503', 'email' => 'dau@smartdriving.com'],
            ['name' => 'San Fernando Branch', 'address' => '456 Olongapo-San Fernando Rd, San Fernando, Pampanga', 'contact_number' => '+63-917-123-4504', 'email' => 'sanfernando@smartdriving.com'],
            ['name' => 'Mabalacat Branch', 'address' => '321 MacArthur Highway, Mabalacat, Pampanga', 'contact_number' => '+63-917-123-4505', 'email' => 'mabalacat@smartdriving.com'],
            ['name' => 'Porac Branch', 'address' => '55 Porac-Gapan Rd, Porac, Pampanga', 'contact_number' => '+63-917-123-4506', 'email' => 'porac@smartdriving.com'],
            ['name' => 'Guagua Branch', 'address' => '88 San Nicolas, Guagua, Pampanga', 'contact_number' => '+63-917-123-4507', 'email' => 'guagua@smartdriving.com'],
            ['name' => 'Lubao Branch', 'address' => '12 Municipal Road, Lubao, Pampanga', 'contact_number' => '+63-917-123-4508', 'email' => 'lubao@smartdriving.com'],
            ['name' => 'Apalit Branch', 'address' => '67 Apalit Town Center, Apalit, Pampanga', 'contact_number' => '+63-917-123-4509', 'email' => 'apalit@smartdriving.com'],
            ['name' => 'Mexico Branch', 'address' => '233 Mexico Town Proper, Mexico, Pampanga', 'contact_number' => '+63-917-123-4510', 'email' => 'mexico@smartdriving.com'],
            ['name' => 'Bacolor Branch', 'address' => '100 Bacolor Town Proper, Bacolor, Pampanga', 'contact_number' => '+63-917-123-4511', 'email' => 'bacolor@smartdriving.com'],
            ['name' => 'Magalang Branch', 'address' => '78 Magalang Public Market, Magalang, Pampanga', 'contact_number' => '+63-917-123-4512', 'email' => 'magalang@smartdriving.com'],
            ['name' => 'Arayat Branch', 'address' => '55 Arayat Town Center, Arayat, Pampanga', 'contact_number' => '+63-917-123-4513', 'email' => 'arayat@smartdriving.com'],
            ['name' => 'Candaba Branch', 'address' => '31 Candaba Swamp Rd, Candaba, Pampanga', 'contact_number' => '+63-917-123-4514', 'email' => 'candaba@smartdriving.com'],
            ['name' => 'Floridablanca Branch', 'address' => '22 Floridablanca Town Proper, Floridablanca, Pampanga', 'contact_number' => '+63-917-123-4515', 'email' => 'floridablanca@smartdriving.com'],
            ['name' => 'Santa Ana Branch', 'address' => '14 Santa Ana Town Center, Santa Ana, Pampanga', 'contact_number' => '+63-917-123-4516', 'email' => 'santaana@smartdriving.com'],
            ['name' => 'Santa Rita Branch', 'address' => '99 Santa Rita Main Rd, Santa Rita, Pampanga', 'contact_number' => '+63-917-123-4517', 'email' => 'santarita@smartdriving.com'],
            ['name' => 'Santo Tomas Branch', 'address' => '45 Santo Tomas Rd, Santo Tomas, Pampanga', 'contact_number' => '+63-917-123-4518', 'email' => 'santotomas@smartdriving.com'],
            ['name' => 'Sasmuan Branch', 'address' => '76 Sasmuan Town Center, Sasmuan, Pampanga', 'contact_number' => '+63-917-123-4519', 'email' => 'sasmuan@smartdriving.com'],
            ['name' => 'Tarlac City Branch', 'address' => '200 F. Tañedo St, Tarlac City, Tarlac', 'contact_number' => '+63-917-123-4520', 'email' => 'tarlac@smartdriving.com'],
            ['name' => 'Olongapo Branch', 'address' => '321 Rizal Avenue, Olongapo City, Zambales', 'contact_number' => '+63-917-123-4521', 'email' => 'olongapo@smartdriving.com'],
            ['name' => 'Subic Branch', 'address' => '105 Subic Bay Freeport Zone, Subic, Zambales', 'contact_number' => '+63-917-123-4522', 'email' => 'subic@smartdriving.com'],
            ['name' => 'Cabanatuan Branch', 'address' => '88 Maharlika Highway, Cabanatuan City, Nueva Ecija', 'contact_number' => '+63-917-123-4523', 'email' => 'cabanatuan@smartdriving.com'],
            ['name' => 'Meycauayan Branch', 'address' => '55 MacArthur Highway, Meycauayan, Bulacan', 'contact_number' => '+63-917-123-4524', 'email' => 'meycauayan@smartdriving.com'],
            ['name' => 'Balanga Branch', 'address' => '33 Capitol Drive, Balanga City, Bataan', 'contact_number' => '+63-917-123-4525', 'email' => 'balanga@smartdriving.com'],
        ];

        $branches = $this->createBranches($school, $branchList);
        $this->command->info('   ✓ 25 Branches created');

        // ── School Admins ──
        $sdAdmins = [
            ['name' => 'Maria Cristina Santos', 'email' => 'maria.santos@smartdriving.com'],
            ['name' => 'Jose Antonio Reyes', 'email' => 'jose.reyes@smartdriving.com'],
            ['name' => 'Carmen Rosa Villanueva', 'email' => 'carmen.villanueva@smartdriving.com'],
        ];
        foreach ($sdAdmins as $a) {
            Admin::updateOrCreate(['email' => $a['email']], [
                'school_id' => $school->id, 'name' => $a['name'],
                'password' => Hash::make($this->password), 'role' => 'school_admin', 'is_active' => true,
            ]);
        }
        Admin::updateOrCreate(['email' => 'schooladmin@gmail.com'], [
            'school_id' => $school->id, 'name' => 'Demo School Admin',
            'password' => Hash::make($this->password), 'role' => 'school_admin', 'is_active' => true,
        ]);
        $this->command->info('   ✓ 4 School Admins created');

        // ── Branch Secretaries (one per branch) ──
        $secNames = [
            'Rosa Marie Lim', 'Fernando Bautista', 'Lorna Aguilar', 'Cecilia Tan',
            'Eduardo Gomez', 'Myrna Torres', 'Reynaldo Santos', 'Gloria Pascual',
            'Nestor Cruz', 'Erlinda Ramos', 'Virgilio Lopez', 'Teresita Mendoza',
            'Danilo Garcia', 'Rosario Flores', 'Arturo Rivera', 'Corazon Hernandez',
            'Benjamin Dizon', 'Felicidad Navarro', 'Rodolfo Medina', 'Leonora Jimenez',
            'Gregorio Alvarez', 'Milagros Ruiz', 'Alfredo Sanchez', 'Esperanza Ramirez',
            'Ricardo Castillo',
        ];
        foreach ($secNames as $i => $name) {
            $slug = strtolower(str_replace(' ', '.', $name));
            Admin::updateOrCreate(['email' => "{$slug}@smartdriving.com"], [
                'school_id' => $school->id,
                'branch_id' => $branches[$i]->id,
                'name' => $name,
                'password' => Hash::make($this->password),
                'role' => 'branch_secretary', 'is_active' => true,
            ]);
        }
        Admin::updateOrCreate(['email' => 'secretary@gmail.com'], [
            'school_id' => $school->id, 'branch_id' => $branches[0]->id,
            'name' => 'Demo Branch Secretary', 'password' => Hash::make($this->password),
            'role' => 'branch_secretary', 'is_active' => true,
        ]);
        $this->command->info('   ✓ 26 Branch Secretaries created');

        // ── Instructors (2 per branch = 50, + 1 demo) ──
        $instFirstNames = ['Juan Carlos', 'Ana Maria', 'Pedro Miguel', 'Rosa Elena', 'Roberto Luis',
            'Angelo', 'Catalina', 'Domingo', 'Elena', 'Francisco',
            'Guillermo', 'Hazel', 'Isidro', 'Jasmine', 'Kenneth',
            'Lourdes', 'Manuel', 'Nora', 'Oscar', 'Patricia',
            'Quirino', 'Rosalinda', 'Salvador', 'Thelma', 'Ulysses',
            'Victoria', 'Wilfredo', 'Ximena', 'Yolanda', 'Zenaida',
            'Abelardo', 'Benigno', 'Carmela', 'Diosdado', 'Emilia',
            'Felix', 'Geraldine', 'Hernando', 'Imelda', 'Juanito',
            'Kristine', 'Leonardo', 'Marcelina', 'Nemesio', 'Olivia',
            'Primitivo', 'Querubin', 'Remigio', 'Soledad', 'Teodoro'];
        $instLastNames = ['Dela Cruz', 'Garcia', 'Martinez', 'Villanueva', 'Fernandez',
            'Bautista', 'Santos', 'Reyes', 'Torres', 'Ramos',
            'Cruz', 'Lopez', 'Gonzales', 'Flores', 'Rivera',
            'Hernandez', 'Dizon', 'Navarro', 'Medina', 'Jimenez',
            'Alvarez', 'Ruiz', 'Sanchez', 'Ramirez', 'Castillo',
            'Domingo', 'Espino', 'Ferrer', 'Galang', 'Ilagan',
            'Julian', 'Lara', 'Manalo', 'Oliva', 'Pineda',
            'Salazar', 'Tolentino', 'Umali', 'Valencia', 'Yap',
            'Abad', 'Buenaventura', 'Cordero', 'Dela Rosa', 'Enriquez',
            'Francisco', 'Guevara', 'Hidalgo', 'Ignacio', 'Javier'];

        $instructors = [];
        for ($i = 0; $i < 50; $i++) {
            $name = $instFirstNames[$i] . ' ' . $instLastNames[$i];
            $emailSlug = strtolower(str_replace([' ', '.'], ['', ''], $instFirstNames[$i])) . '.' . strtolower(str_replace(' ', '', $instLastNames[$i]));
            $instructors[] = Instructor::updateOrCreate(
                ['email' => "{$emailSlug}@smartdriving.com"],
                [
                    'school_id' => $school->id,
                    'branch_id' => $branches[$i % count($branches)]->id,
                    'name' => $name,
                    'contact' => '+63-917-555-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'password' => Hash::make($this->password),
                    'license_number' => 'LIC-SD-2024-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'bio' => 'Experienced driving instructor at Smart Driving School.',
                    'status' => 'active',
                    'availability' => 'available',
                ]
            );
        }
        $instructors[] = Instructor::updateOrCreate(
            ['email' => 'instructor@gmail.com'],
            [
                'school_id' => $school->id, 'branch_id' => $branches[0]->id,
                'name' => 'Demo Instructor', 'contact' => '+63-917-555-0000',
                'password' => Hash::make($this->password),
                'license_number' => 'LIC-SD-TEST-001',
                'bio' => 'Test instructor account for demo purposes.',
                'status' => 'active', 'availability' => 'available',
            ]
        );
        $this->command->info('   ✓ 51 Instructors created');

        // ── Courses ──
        $courses = $this->createSmartDrivingCourses($school);
        $this->command->info('   ✓ 3 Courses with packages created');

        // ── Students (60 students across 25 branches) ──
        $students = $this->createSmartDrivingStudents($school, $branches);
        $this->command->info('   ✓ ' . count($students) . ' Students created');

        // ── Time Slots, Bookings, Payments ──
        $this->createTimeSlotsAndAssignments($school, $instructors, $courses, $branches);
        $this->createBookingsAndPayments($school, $students, $instructors, $courses, $branches);
        $this->command->info('   ✓ Time slots, bookings, and payments created');

        // ── Guests & Enrollment Requests ──
        $admins = Admin::where('school_id', $school->id)->where('role', 'school_admin')->get()->all();
        $guests = $this->createGuestsAndEnrollmentRequests($school, $courses, $admins);
        $this->command->info('   ✓ Guest students and enrollment requests created');

        // ── Notifications ──
        $this->createSampleNotifications($school, $students, $instructors, $admins, $guests);
        $this->command->info('   ✓ Sample notifications created');
    }

    // ═══════════════════════════════════════════════════════════════
    //  LYSPEED DRIVING SCHOOL  (10 branches)
    // ═══════════════════════════════════════════════════════════════

    private function createLySpeedDrivingSchool(): void
    {
        $this->command->info('');
        $this->command->info('🏫 Creating LySpeed Driving School (10 branches)...');

        $school = School::updateOrCreate(
            ['slug' => 'lyspeed-driving'],
            [
                'name' => 'LySpeed Driving School',
                'timezone' => 'Asia/Manila',
                'branding' => json_encode([
                    'logo' => null,
                    'colors' => ['primary' => '#8B0000', 'secondary' => '#ffffff', 'accent' => '#B22222'],
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
                'enable_branches' => true,
            ]
        );

        // ── 10 Branches ──
        $branchList = [
            ['name' => 'Main Branch - San Fernando', 'address' => '456 Jose Abad Santos Ave, San Fernando, Pampanga', 'contact_number' => '+63-918-234-5601', 'email' => 'sanfernando@lyspeed.com'],
            ['name' => 'Guagua Branch', 'address' => '321 San Nicolas, Guagua, Pampanga', 'contact_number' => '+63-918-234-5602', 'email' => 'guagua@lyspeed.com'],
            ['name' => 'Angeles City Branch', 'address' => '77 Miranda St, Angeles City, Pampanga', 'contact_number' => '+63-918-234-5603', 'email' => 'angeles@lyspeed.com'],
            ['name' => 'Mabalacat Branch', 'address' => '89 MacArthur Highway, Mabalacat, Pampanga', 'contact_number' => '+63-918-234-5604', 'email' => 'mabalacat@lyspeed.com'],
            ['name' => 'Mexico Branch', 'address' => '15 Mexico Town Center, Mexico, Pampanga', 'contact_number' => '+63-918-234-5605', 'email' => 'mexico@lyspeed.com'],
            ['name' => 'Apalit Branch', 'address' => '44 Apalit Bypass Rd, Apalit, Pampanga', 'contact_number' => '+63-918-234-5606', 'email' => 'apalit@lyspeed.com'],
            ['name' => 'Porac Branch', 'address' => '29 National Highway, Porac, Pampanga', 'contact_number' => '+63-918-234-5607', 'email' => 'porac@lyspeed.com'],
            ['name' => 'Bacolor Branch', 'address' => '10 Bacolor Town Proper, Bacolor, Pampanga', 'contact_number' => '+63-918-234-5608', 'email' => 'bacolor@lyspeed.com'],
            ['name' => 'Lubao Branch', 'address' => '66 Lubao Bypass Rd, Lubao, Pampanga', 'contact_number' => '+63-918-234-5609', 'email' => 'lubao@lyspeed.com'],
            ['name' => 'Magalang Branch', 'address' => '38 Magalang-Concepcion Rd, Magalang, Pampanga', 'contact_number' => '+63-918-234-5610', 'email' => 'magalang@lyspeed.com'],
        ];

        $branches = $this->createBranches($school, $branchList);
        $this->command->info('   ✓ 10 Branches created');

        // ── School Admins ──
        $lsAdmins = [
            ['name' => 'Carlos Miguel Villanueva', 'email' => 'carlos.villanueva@lyspeed.com'],
            ['name' => 'Elena Rose Gonzales', 'email' => 'elena.gonzales@lyspeed.com'],
        ];
        foreach ($lsAdmins as $a) {
            Admin::updateOrCreate(['email' => $a['email']], [
                'school_id' => $school->id, 'name' => $a['name'],
                'password' => Hash::make($this->password), 'role' => 'school_admin', 'is_active' => true,
            ]);
        }
        Admin::updateOrCreate(['email' => 'lyspeed.admin@gmail.com'], [
            'school_id' => $school->id, 'name' => 'LySpeed Demo Admin',
            'password' => Hash::make($this->password), 'role' => 'school_admin', 'is_active' => true,
        ]);
        $this->command->info('   ✓ 3 School Admins created');

        // ── Branch Secretaries (one per branch) ──
        $lsSecNames = [
            'Angelina Reyes', 'Benito Aquino', 'Cristina Dela Peña', 'Dominador Ocampo',
            'Evelyn Pangilinan', 'Florante Manansala', 'Gilda Cunanan', 'Honesto David',
            'Imelda Lugtu', 'Josefino Pineda',
        ];
        foreach ($lsSecNames as $i => $name) {
            $slug = strtolower(str_replace(' ', '.', $name));
            $slug = str_replace('ñ', 'n', $slug);
            Admin::updateOrCreate(['email' => "{$slug}@lyspeed.com"], [
                'school_id' => $school->id,
                'branch_id' => $branches[$i]->id,
                'name' => $name,
                'password' => Hash::make($this->password),
                'role' => 'branch_secretary', 'is_active' => true,
            ]);
        }
        $this->command->info('   ✓ 10 Branch Secretaries created');

        // ── Instructors (20 across 10 branches + 1 demo) ──
        $lsInstNames = [
            'Miguel Angel Santos', 'Elena Patricia Ramos', 'Fernando Jose Cruz',
            'Maribel Torres', 'Ernesto Galang', 'Lorena Espino',
            'Rodolfo Manalo', 'Ana Liza Salazar', 'Delfin Tolentino',
            'Barbara Valencia', 'Crisanto Yap', 'Divina Abad',
            'Emilio Buenaventura', 'Fatima Cordero', 'Gerardo Dela Rosa',
            'Helen Enriquez', 'Ignacio Francisco', 'Julieta Guevara',
            'Kevin Hidalgo', 'Ligaya Javier',
        ];
        $instructors = [];
        foreach ($lsInstNames as $i => $name) {
            $emailSlug = strtolower(str_replace(' ', '.', $name));
            $instructors[] = Instructor::updateOrCreate(
                ['email' => "{$emailSlug}@lyspeed.com"],
                [
                    'school_id' => $school->id,
                    'branch_id' => $branches[$i % count($branches)]->id,
                    'name' => $name,
                    'contact' => '+63-918-666-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'password' => Hash::make($this->password),
                    'license_number' => 'LIC-LS-2024-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'bio' => 'Professional driving instructor at LySpeed Driving School.',
                    'status' => 'active', 'availability' => 'available',
                ]
            );
        }
        $instructors[] = Instructor::updateOrCreate(
            ['email' => 'lyspeed.instructor@gmail.com'],
            [
                'school_id' => $school->id, 'branch_id' => $branches[0]->id,
                'name' => 'LySpeed Demo Instructor', 'contact' => '+63-918-666-0000',
                'password' => Hash::make($this->password),
                'license_number' => 'LIC-LS-TEST-001',
                'bio' => 'Test instructor account for demo purposes.',
                'status' => 'active', 'availability' => 'available',
            ]
        );
        $this->command->info('   ✓ 21 Instructors created');

        // ── Courses ──
        $courses = $this->createLySpeedCourses($school);
        $this->command->info('   ✓ 3 Courses with packages created');

        // ── Students (30 across 10 branches) ──
        $students = $this->createLySpeedStudents($school, $branches);
        $this->command->info('   ✓ ' . count($students) . ' Students created');

        // ── Time Slots, Bookings, Payments ──
        $this->createTimeSlotsAndAssignments($school, $instructors, $courses, $branches);
        $this->createBookingsAndPayments($school, $students, $instructors, $courses, $branches);
        $this->command->info('   ✓ Time slots, bookings, and payments created');

        // ── Guests & Enrollment Requests ──
        $admins = Admin::where('school_id', $school->id)->where('role', 'school_admin')->get()->all();
        $guests = $this->createGuestsAndEnrollmentRequests($school, $courses, $admins);
        $this->command->info('   ✓ Guest students and enrollment requests created');

        // ── Notifications ──
        $this->createSampleNotifications($school, $students, $instructors, $admins, $guests);
        $this->command->info('   ✓ Sample notifications created');
    }

    // ═══════════════════════════════════════════════════════════════
    //  DRIVED HUB DRIVING SCHOOL  (2 branches – demo school)
    // ═══════════════════════════════════════════════════════════════

    private function createDriveEdHubSchool(): void
    {
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
                'use_gradient_header' => true,
                'header_text_color' => '#ffffff',
                'background_type' => 'gradient',
                'background_color' => '#f8fafc',
                'sidebar_bg_color' => '#ffffff',
                'sidebar_text_color' => '#667eea',
                'instructor_selection_mode' => 'admin_assigned',
                'enable_booking_queue' => true,
                'booking_queue_days' => 3,
                'enable_branches' => true,
            ]
        );

        // ── 2 Branches ──
        $branches = $this->createBranches($school, [
            ['name' => 'Main Campus - Clark', 'address' => '789 Del Pilar Street, Clark Freeport Zone, Pampanga', 'contact_number' => '+63-919-345-6789', 'email' => 'clark@drivedhub.com'],
            ['name' => 'Balibago Branch', 'address' => '456 Fields Avenue, Balibago, Angeles City, Pampanga', 'contact_number' => '+63-919-345-6790', 'email' => 'balibago@drivedhub.com'],
        ]);
        $this->command->info('   ✓ 2 Branches created');

        // ── 1 School Admin ──
        $admin = Admin::updateOrCreate(['email' => 'admin@gmail.com'], [
            'school_id' => $school->id, 'name' => 'Antonio Francisco Reyes',
            'password' => Hash::make($this->password), 'role' => 'school_admin', 'is_active' => true,
        ]);
        $this->command->info('   ✓ 1 School Admin created');

        // ── 2 Branch Managers (secretaries) ──
        $bm1 = Admin::updateOrCreate(['email' => 'manager.clark@drivedhub.com'], [
            'school_id' => $school->id, 'branch_id' => $branches[0]->id,
            'name' => 'Patricia Lyn Mendoza', 'password' => Hash::make($this->password),
            'role' => 'branch_secretary', 'is_active' => true,
        ]);
        $bm2 = Admin::updateOrCreate(['email' => 'manager.balibago@drivedhub.com'], [
            'school_id' => $school->id, 'branch_id' => $branches[1]->id,
            'name' => 'Gabriel Marco Santos', 'password' => Hash::make($this->password),
            'role' => 'branch_secretary', 'is_active' => true,
        ]);
        $this->command->info('   ✓ 2 Branch Managers created');

        // ── 4 Instructors (2 per branch) ──
        $dhInstructors = [
            ['name' => 'Ricardo Antonio Cruz', 'email' => 'ricardo.cruz@drivedhub.com', 'contact' => '+63-919-777-3001', 'license' => 'LIC-DH-2024-001', 'bio' => 'Senior Instructor specializing in Manual Transmission and Motorcycle training. 8 years of experience.', 'branch' => 0],
            ['name' => 'Maria Victoria Santos', 'email' => 'maria.santos@drivedhub.com', 'contact' => '+63-919-777-3002', 'license' => 'LIC-DH-2024-002', 'bio' => 'Expert in Automatic Transmission and Practical Driving. Certified defensive driving instructor.', 'branch' => 0],
            ['name' => 'Angelo Miguel Ramos', 'email' => 'angelo.ramos@drivedhub.com', 'contact' => '+63-919-777-3003', 'license' => 'LIC-DH-2024-003', 'bio' => 'Theoretical Driving Course specialist. LTO-certified TDC instructor with 6 years experience.', 'branch' => 1],
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
                    'password' => Hash::make($this->password),
                    'license_number' => $inst['license'],
                    'bio' => $inst['bio'],
                    'status' => 'active', 'availability' => 'available',
                ]
            );
        }
        $this->command->info('   ✓ 4 Instructors created');

        // ── 5 Courses (3 PDC + 2 TDC) ──
        $courses = $this->createDriveEdHubCourses($school);
        $this->command->info('   ✓ 5 Courses with packages created');

        // ── 10 Students (enrolled) ──
        $students = $this->createDriveEdHubStudents($school, $branches);
        $this->command->info('   ✓ ' . count($students) . ' Students created');

        // ── Time Slots ──
        $this->createTimeSlotsAndAssignments($school, $instructors, $courses, $branches);
        $this->command->info('   ✓ Time slots created');

        // ── Bookings & Payments ──
        $this->createBookingsAndPayments($school, $students, $instructors, $courses, $branches);
        $this->command->info('   ✓ Bookings and payments created');

        // ── 5 Guests (2 enrolled, 3 not enrolled) ──
        $admins = [$admin];
        $guests = $this->createDriveEdHubGuests($school, $courses, $admins, $branches);
        $this->command->info('   ✓ 5 Guest students created (2 enrolled, 3 not enrolled)');

        // ── Notifications ──
        $this->createSampleNotifications($school, $students, $instructors, $admins, $guests);
        $this->command->info('   ✓ Notifications created');
    }

    // ═══════════════════════════════════════════════════════════════
    //  SHARED HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function createBranches(School $school, array $branchData): array
    {
        $branches = [];
        foreach ($branchData as $index => $data) {
            $branches[] = Branch::updateOrCreate(
                ['school_id' => $school->id, 'name' => $data['name']],
                [
                    'address' => $data['address'] ?? null,
                    'contact_number' => $data['contact_number'] ?? null,
                    'email' => $data['email'] ?? null,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
        return $branches;
    }

    // ───────────────────────────────────────────────
    //  COURSES
    // ───────────────────────────────────────────────

    private function createSmartDrivingCourses(School $school): array
    {
        $courses = [];

        $course1 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course (Manual)'],
            [
                'description' => 'Master manual transmission driving with comprehensive hands-on training.',
                'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true,
                'features' => ['Manual Transmission', 'Clutch Control', 'Hill Start', 'Parking Techniques', 'Defensive Driving'],
            ]
        );
        $courses[] = $course1;
        CoursePackage::updateOrCreate(['course_id' => $course1->id, 'name' => '10-Hour Package'], [
            'transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 10, 'price' => 5500.00,
            'description' => 'Basic manual driving course for beginners.',
        ]);
        CoursePackage::updateOrCreate(['course_id' => $course1->id, 'name' => '15-Hour Package'], [
            'transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 7500.00,
            'description' => 'Complete manual driving course with advanced techniques.', 'is_popular' => true,
        ]);

        $course2 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course (Automatic)'],
            [
                'description' => 'Learn to drive automatic transmission vehicles with confidence.',
                'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true,
                'features' => ['Automatic Transmission', 'City Driving', 'Parking Techniques', 'Defensive Driving'],
            ]
        );
        $courses[] = $course2;
        CoursePackage::updateOrCreate(['course_id' => $course2->id, 'name' => '8-Hour Package'], [
            'transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 8, 'price' => 4800.00,
            'description' => 'Automatic driving course for beginners.', 'is_popular' => true,
        ]);

        $course3 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course (TDC)'],
            [
                'description' => 'Comprehensive road rules and traffic signs education. Required for LTO written exam.',
                'type' => 'Theoretical', 'vehicle_type' => 'Car', 'status' => 'active',
                'features' => ['Traffic Rules', 'Road Signs', 'LTO Written Exam Prep', 'Certificate Included'],
            ]
        );
        $courses[] = $course3;
        CoursePackage::updateOrCreate(['course_id' => $course3->id, 'name' => 'TDC 15-Hour Course'], [
            'transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 1500.00,
            'description' => 'Complete TDC for LTO exam preparation.',
        ]);

        return $courses;
    }

    private function createLySpeedCourses(School $school): array
    {
        $courses = [];

        $course1 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Basic Driving Course'],
            [
                'description' => 'Affordable driving lessons for beginners. Learn the fundamentals of safe driving.',
                'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true,
                'features' => ['Basic Vehicle Control', 'Traffic Navigation', 'Parking Skills', 'Road Safety'],
            ]
        );
        $courses[] = $course1;
        CoursePackage::updateOrCreate(['course_id' => $course1->id, 'name' => '8-Hour Starter'], [
            'transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 8, 'price' => 4000.00,
            'description' => 'Beginner automatic driving course.',
        ]);
        CoursePackage::updateOrCreate(['course_id' => $course1->id, 'name' => '12-Hour Complete'], [
            'transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 12, 'price' => 5500.00,
            'description' => 'Complete automatic driving course.', 'is_popular' => true,
        ]);

        $course2 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Motorcycle Riding Course'],
            [
                'description' => 'Learn to ride motorcycles safely. Includes scooter and manual motorcycle training.',
                'type' => 'Practical', 'vehicle_type' => 'Motorcycle', 'status' => 'active',
                'features' => ['Balance Training', 'Gear Shifting', 'Defensive Riding', 'License Preparation'],
            ]
        );
        $courses[] = $course2;
        CoursePackage::updateOrCreate(['course_id' => $course2->id, 'name' => '6-Hour Package'], [
            'transmission_type' => 'manual', 'vehicle_type' => 'Motorcycle', 'training_hours' => 6, 'price' => 3000.00,
            'description' => 'Motorcycle riding fundamentals.',
        ]);

        $course3 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course (TDC)'],
            [
                'description' => 'LTO-accredited theoretical driving course covering traffic rules and regulations.',
                'type' => 'Theoretical', 'vehicle_type' => 'Car', 'status' => 'active',
                'features' => ['Traffic Rules', 'Road Signs', 'LTO Accredited', 'Certificate'],
            ]
        );
        $courses[] = $course3;
        CoursePackage::updateOrCreate(['course_id' => $course3->id, 'name' => 'TDC 15-Hour'], [
            'transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 1200.00,
            'description' => 'Complete TDC for LTO written exam.',
        ]);

        return $courses;
    }

    /**
     * DriveED Hub: 3 PDC (Motorcycle, Manual, Automatic) + 2 TDC (Standard, Refresher)
     */
    private function createDriveEdHubCourses(School $school): array
    {
        $courses = [];

        // PDC 1 – Manual Transmission
        $pdc1 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course - Manual Transmission'],
            [
                'description' => 'Hands-on manual transmission driving training. Master clutch control, gear shifting, hill starts, and defensive driving techniques with our certified instructors.',
                'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true,
                'features' => ['Manual Transmission', 'Clutch Control', 'Hill Start', 'Gear Shifting', 'Defensive Driving', 'Parking Techniques'],
            ]
        );
        $courses[] = $pdc1;
        CoursePackage::updateOrCreate(['course_id' => $pdc1->id, 'name' => 'Manual 10-Hour'], [
            'transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 10, 'price' => 6000.00,
            'description' => 'Beginner manual driving – 10 hours of practical training.',
        ]);
        CoursePackage::updateOrCreate(['course_id' => $pdc1->id, 'name' => 'Manual 15-Hour'], [
            'transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 8500.00,
            'description' => 'Complete manual driving with highway & city practice.', 'is_popular' => true,
        ]);
        CoursePackage::updateOrCreate(['course_id' => $pdc1->id, 'name' => 'Manual 20-Hour'], [
            'transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 20, 'price' => 10500.00,
            'description' => 'Advanced manual driving – includes LTO exam preparation.',
        ]);

        // PDC 2 – Automatic Transmission
        $pdc2 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course - Automatic Transmission'],
            [
                'description' => 'Learn to drive automatic vehicles with confidence. Perfect for city driving, commuting, and everyday use.',
                'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true,
                'features' => ['Automatic Transmission', 'City Driving', 'Highway Driving', 'Parking', 'Defensive Driving'],
            ]
        );
        $courses[] = $pdc2;
        CoursePackage::updateOrCreate(['course_id' => $pdc2->id, 'name' => 'Automatic 8-Hour'], [
            'transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 8, 'price' => 5000.00,
            'description' => 'Quick starter course for automatic vehicles.',
        ]);
        CoursePackage::updateOrCreate(['course_id' => $pdc2->id, 'name' => 'Automatic 12-Hour'], [
            'transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 12, 'price' => 7000.00,
            'description' => 'Complete automatic driving with city & highway practice.', 'is_popular' => true,
        ]);

        // PDC 3 – Motorcycle
        $pdc3 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course - Motorcycle'],
            [
                'description' => 'Comprehensive motorcycle riding course. From basic balance to highway riding. Scooter and manual bikes covered.',
                'type' => 'Practical', 'vehicle_type' => 'Motorcycle', 'status' => 'active', 'is_featured' => true,
                'features' => ['Motorcycle Basics', 'Balance Training', 'Gear Shifting', 'Defensive Riding', 'Night Riding', 'License Preparation'],
            ]
        );
        $courses[] = $pdc3;
        CoursePackage::updateOrCreate(['course_id' => $pdc3->id, 'name' => 'Motorcycle 6-Hour'], [
            'transmission_type' => 'manual', 'vehicle_type' => 'Motorcycle', 'training_hours' => 6, 'price' => 3500.00,
            'description' => 'Basic motorcycle riding fundamentals.',
        ]);
        CoursePackage::updateOrCreate(['course_id' => $pdc3->id, 'name' => 'Motorcycle 10-Hour'], [
            'transmission_type' => 'manual', 'vehicle_type' => 'Motorcycle', 'training_hours' => 10, 'price' => 5500.00,
            'description' => 'Complete motorcycle course with road practice.', 'is_popular' => true,
        ]);

        // TDC 1 – Standard
        $tdc1 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course - Standard (TDC)'],
            [
                'description' => 'LTO-accredited 15-hour TDC for new applicants. Covers traffic rules, road signs, vehicle operation, and defensive driving principles.',
                'type' => 'Theoretical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true,
                'features' => ['Traffic Rules & Regulations', 'Road Signs & Markings', 'Defensive Driving', 'Vehicle Operation Basics', 'LTO Written Exam Prep', 'TDC Certificate'],
            ]
        );
        $courses[] = $tdc1;
        CoursePackage::updateOrCreate(['course_id' => $tdc1->id, 'name' => 'TDC Standard 15-Hour'], [
            'transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 2500.00,
            'description' => 'Complete 15-hour TDC for new license applicants.', 'is_popular' => true,
        ]);

        // TDC 2 – Refresher
        $tdc2 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course - Refresher (TDC-R)'],
            [
                'description' => 'Shortened TDC refresher for license renewal, reinstatement, or drivers returning after a long break. Updated traffic laws included.',
                'type' => 'Theoretical', 'vehicle_type' => 'Car', 'status' => 'active',
                'features' => ['Updated Traffic Laws', 'Road Safety Refresher', 'Anti-Distracted Driving Act', 'Quick License Renewal Prep', 'TDC-R Certificate'],
            ]
        );
        $courses[] = $tdc2;
        CoursePackage::updateOrCreate(['course_id' => $tdc2->id, 'name' => 'TDC Refresher 8-Hour'], [
            'transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 8, 'price' => 1500.00,
            'description' => '8-hour refresher course for experienced drivers.',
        ]);

        return $courses;
    }

    // ───────────────────────────────────────────────
    //  STUDENTS
    // ───────────────────────────────────────────────

    private function createSmartDrivingStudents(School $school, array $branches): array
    {
        $names = [
            'Sofia Angelica Reyes', 'Luis Antonio Cruz', 'Isabella Marie Flores', 'Diego Rafael Torres',
            'Carmen Elena Ramos', 'Rafael Jose Mendoza', 'Valentina Rose Garcia', 'Gabriel Miguel Lopez',
            'Lucia Patricia Perez', 'Daniel Carlos Rivera', 'Angela Maria Castro', 'Roberto Francisco Ortiz',
            'Cristina Grace Martinez', 'Fernando Jose Fernandez', 'Natalia Anne Santos', 'Enrique Paolo Dizon',
            'Mariana Joy Navarro', 'Benito Carlo Medina', 'Teresa Lyn Jimenez', 'Oscar Manuel Alvarez',
            'Paulina Faith Ruiz', 'Ricardo James Sanchez', 'Gloria Dawn Ramirez', 'Alejandro Neil Castillo',
            'Cecilia Hope Domingo', 'Marco Luis Espino', 'Bianca Joy Ferrer', 'Dario Miguel Galang',
            'Elena Rose Ilagan', 'Franco Kai Julian', 'Gemma Rae Lara', 'Hector Roy Manalo',
            'Iris May Oliva', 'Jaime Cruz Pineda', 'Karla Jean Salazar', 'Lorenzo Miguel Tolentino',
            'Mila Grace Umali', 'Noel Patrick Valencia', 'Olive Ruth Yap', 'Pablo John Abad',
            'Queenie Mae Buenaventura', 'Renzo Dale Cordero', 'Samantha Claire Dela Rosa', 'Tito Vince Enriquez',
            'Ursula Faith Francisco', 'Victor Hugo Guevara', 'Wendy Joy Hidalgo', 'Xavier Luis Ignacio',
            'Yvette Marie Javier', 'Zenaida Pearl Laurel', 'Adrian Luke Mercado', 'Bella Sophia Nicolas',
            'Carlo Jude Panganiban', 'Diana Rose Quinto', 'Edison Marc Roxas', 'Florence May Soriano',
            'Gonzalo Ray Tan', 'Hazel Marie Uy', 'Ivan James Velasco', 'Josephine Anne Zamora',
        ];

        $students = [];
        foreach ($names as $i => $name) {
            $emailSlug = strtolower(str_replace(' ', '.', $name));
            $students[] = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => "{$emailSlug}@gmail.com"],
                [
                    'name' => $name,
                    'branch_id' => $branches[$i % count($branches)]->id,
                    'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'password' => Hash::make($this->password),
                    'status' => 'active', 'role' => 'student',
                    'enrollment_date' => now()->subDays(rand(7, 90)),
                ]
            );
        }

        // Demo student account
        $students[] = Student::updateOrCreate(
            ['school_id' => $school->id, 'email' => 'student@gmail.com'],
            [
                'name' => 'Demo Student', 'branch_id' => $branches[0]->id,
                'contact' => '+63-900-000-0001', 'password' => Hash::make($this->password),
                'status' => 'active', 'role' => 'student', 'enrollment_date' => now()->subDays(30),
            ]
        );

        return $students;
    }

    private function createLySpeedStudents(School $school, array $branches): array
    {
        $names = [
            'Maria Josephine Rodriguez', 'Antonio Rafael Hernandez', 'Teresa Lyn Jimenez',
            'Marco Paulo Ruiz', 'Laura Anne Sanchez', 'Pedro Miguel Ramirez',
            'Ana Christina Alvarez', 'Jorge Luis Medina', 'Beatriz Elena Navarro',
            'Carlos Manuel Espiritu', 'Diana Grace Villanueva', 'Edgar Paolo Manansala',
            'Felisa Joy Cunanan', 'Gerardo Jose David', 'Hazel Anne Lugtu',
            'Isidro Roy Pineda', 'Jasmine Faith Ocampo', 'Kenneth Marc Pangilinan',
            'Lourdes Marie Aquino', 'Manuel Jose Dela Peña', 'Nora Beth Castro',
            'Oliver James Reyes', 'Patricia Rose Bautista', 'Quintin Luke Flores',
            'Rosalinda May Garcia', 'Salvador John Torres', 'Thelma Grace Lopez',
            'Ulysses Ray Gonzales', 'Victoria Anne Rivera', 'Wilfredo Jose Hernandez',
        ];

        $students = [];
        foreach ($names as $i => $name) {
            $emailSlug = strtolower(str_replace([' ', 'ñ'], ['.', 'n'], $name));
            $students[] = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => "{$emailSlug}@gmail.com"],
                [
                    'name' => $name,
                    'branch_id' => $branches[$i % count($branches)]->id,
                    'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'password' => Hash::make($this->password),
                    'status' => 'active', 'role' => 'student',
                    'enrollment_date' => now()->subDays(rand(7, 60)),
                ]
            );
        }

        // Demo student account
        $students[] = Student::updateOrCreate(
            ['school_id' => $school->id, 'email' => 'lyspeed.student@gmail.com'],
            [
                'name' => 'LySpeed Demo Student', 'branch_id' => $branches[0]->id,
                'contact' => '+63-918-999-0001', 'password' => Hash::make($this->password),
                'status' => 'active', 'role' => 'student', 'enrollment_date' => now()->subDays(30),
            ]
        );

        return $students;
    }

    private function createDriveEdHubStudents(School $school, array $branches): array
    {
        $studentData = [
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
        foreach ($studentData as $i => $s) {
            $students[] = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => $s['email']],
                [
                    'name' => $s['name'],
                    'branch_id' => $branches[$i % count($branches)]->id,
                    'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'password' => Hash::make($this->password),
                    'status' => 'active', 'role' => 'student',
                    'experience_level' => $s['level'],
                    'enrollment_date' => now()->subDays(rand(7, 60)),
                ]
            );
        }

        return $students;
    }

    // ───────────────────────────────────────────────
    //  DRIVED HUB – 5 GUESTS (2 enrolled, 3 not)
    // ───────────────────────────────────────────────

    private function createDriveEdHubGuests(School $school, array $courses, array $admins, array $branches): array
    {
        $guests = [];

        // Guest 1 – Enrolled (approved)
        $g1 = Student::updateOrCreate(
            ['school_id' => $school->id, 'email' => 'guest.enrolled1@drivedhub.test'],
            [
                'name' => 'Elena Joy Reyes',
                'contact' => '+63-919-800-1001',
                'password' => Hash::make($this->password),
                'status' => 'active', 'role' => 'guest',
                'student_license_status' => 'verified',
                'student_license_verified_at' => now()->subDays(10),
                'experience_level' => 'new_driver',
            ]
        );
        $guests[] = $g1;
        if (!empty($courses)) {
            $course = $courses[0]; // PDC Manual
            EnrollmentRequest::updateOrCreate(
                ['school_id' => $school->id, 'learner_id' => $g1->id, 'course_id' => $course->id],
                [
                    'status' => 'approved',
                    'payment_status' => 'paid',
                    'experience_level' => 'new_driver',
                    'requested_license_type' => 'non_professional',
                    'approved_by' => !empty($admins) ? $admins[0]->id : null,
                    'approved_at' => now()->subDays(5),
                    'enrolled_at' => now()->subDays(5),
                    'branch_id' => $branches[0]->id,
                ]
            );
        }

        // Guest 2 – Enrolled (approved, different course)
        $g2 = Student::updateOrCreate(
            ['school_id' => $school->id, 'email' => 'guest.enrolled2@drivedhub.test'],
            [
                'name' => 'Mark Anthony Dizon',
                'contact' => '+63-919-800-1002',
                'password' => Hash::make($this->password),
                'status' => 'active', 'role' => 'guest',
                'student_license_status' => 'verified',
                'student_license_verified_at' => now()->subDays(8),
                'experience_level' => 'experienced',
            ]
        );
        $guests[] = $g2;
        if (count($courses) > 3) {
            $course = $courses[3]; // TDC Standard
            EnrollmentRequest::updateOrCreate(
                ['school_id' => $school->id, 'learner_id' => $g2->id, 'course_id' => $course->id],
                [
                    'status' => 'approved',
                    'payment_status' => 'paid',
                    'experience_level' => 'experienced',
                    'requested_license_type' => 'non_professional',
                    'approved_by' => !empty($admins) ? $admins[0]->id : null,
                    'approved_at' => now()->subDays(3),
                    'enrolled_at' => now()->subDays(3),
                    'branch_id' => $branches[1]->id,
                ]
            );
        }

        // Guest 3 – NOT enrolled (no request)
        $g3 = Student::updateOrCreate(
            ['school_id' => $school->id, 'email' => 'guest.new1@drivedhub.test'],
            [
                'name' => 'Jamie Lyn Pascual',
                'contact' => '+63-919-800-1003',
                'password' => Hash::make($this->password),
                'status' => 'active', 'role' => 'guest',
                'student_license_status' => 'none',
                'experience_level' => 'new_driver',
            ]
        );
        $guests[] = $g3;

        // Guest 4 – NOT enrolled (pending request, not yet approved)
        $g4 = Student::updateOrCreate(
            ['school_id' => $school->id, 'email' => 'guest.pending@drivedhub.test'],
            [
                'name' => 'Carlo Miguel Bautista',
                'contact' => '+63-919-800-1004',
                'password' => Hash::make($this->password),
                'status' => 'active', 'role' => 'guest',
                'student_license_status' => 'pending',
                'experience_level' => 'new_driver',
            ]
        );
        $guests[] = $g4;
        if (!empty($courses)) {
            $course = $courses[1]; // PDC Automatic
            EnrollmentRequest::updateOrCreate(
                ['school_id' => $school->id, 'learner_id' => $g4->id, 'course_id' => $course->id],
                [
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'experience_level' => 'new_driver',
                    'requested_license_type' => 'non_professional',
                ]
            );
        }

        // Guest 5 – NOT enrolled (rejected)
        $g5 = Student::updateOrCreate(
            ['school_id' => $school->id, 'email' => 'guest.rejected@drivedhub.test'],
            [
                'name' => 'Angelica Mae Soriano',
                'contact' => '+63-919-800-1005',
                'password' => Hash::make($this->password),
                'status' => 'active', 'role' => 'guest',
                'student_license_status' => 'none',
                'experience_level' => 'new_driver',
            ]
        );
        $guests[] = $g5;
        if (count($courses) > 2) {
            $course = $courses[2]; // PDC Motorcycle
            EnrollmentRequest::updateOrCreate(
                ['school_id' => $school->id, 'learner_id' => $g5->id, 'course_id' => $course->id],
                [
                    'status' => 'rejected',
                    'payment_status' => 'pending',
                    'experience_level' => 'new_driver',
                    'requested_license_type' => 'non_professional',
                    'remarks' => 'Incomplete documentation. Please re-submit with valid student license.',
                ]
            );
        }

        return $guests;
    }

    // ───────────────────────────────────────────────
    //  GENERIC GUESTS (Smart Driving / LySpeed)
    // ───────────────────────────────────────────────

    private function createGuestsAndEnrollmentRequests(School $school, array $courses, array $admins): array
    {
        $slug = $school->slug;
        $guestData = [
            ['name' => 'Elena Joy Reyes', 'email' => "guest1@{$slug}.test", 'license_status' => 'none', 'enrollment_status' => 'pending'],
            ['name' => 'Mark Anthony Dizon', 'email' => "guest2@{$slug}.test", 'license_status' => 'pending', 'enrollment_status' => 'pending'],
            ['name' => 'Jamie Lyn Pascual', 'email' => "guest3@{$slug}.test", 'license_status' => 'verified', 'enrollment_status' => 'rejected'],
            ['name' => 'Carlo Miguel Bautista', 'email' => "guest4@{$slug}.test", 'license_status' => 'none', 'enrollment_status' => null],
        ];

        $guests = [];
        foreach ($guestData as $g) {
            $guest = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => $g['email']],
                [
                    'name' => $g['name'],
                    'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'password' => Hash::make($this->password),
                    'status' => 'active', 'role' => 'guest',
                    'student_license_status' => $g['license_status'],
                    'student_license_verified_at' => $g['license_status'] === 'verified' ? now()->subDays(5) : null,
                ]
            );
            $guests[] = $guest;

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

    // ───────────────────────────────────────────────
    //  TIME SLOTS & INSTRUCTOR ASSIGNMENTS
    // ───────────────────────────────────────────────

    private function createTimeSlotsAndAssignments(School $school, array $instructors, array $courses, array $branches): void
    {
        $times = [
            ['08:00:00', '09:00:00'], ['09:00:00', '10:00:00'],
            ['10:00:00', '11:00:00'], ['11:00:00', '12:00:00'],
            ['13:00:00', '14:00:00'], ['14:00:00', '15:00:00'],
            ['15:00:00', '16:00:00'], ['16:00:00', '17:00:00'],
        ];

        $branchIndex = 0;

        for ($day = 0; $day < 14; $day++) {
            $date = now()->addDays($day)->format('Y-m-d');
            if (now()->addDays($day)->dayOfWeek == 0) continue; // skip Sundays

            foreach ($courses as $course) {
                $slotCount = rand(4, min(6, count($times)));
                $daySlots = array_rand($times, $slotCount);
                if (!is_array($daySlots)) $daySlots = [$daySlots];

                foreach ($daySlots as $slotIndex) {
                    $branch = $branches[$branchIndex % count($branches)];
                    $branchIndex++;

                    $timeSlot = TimeSlot::create([
                        'school_id' => $school->id,
                        'branch_id' => $branch->id,
                        'course_id' => $course->id,
                        'date' => $date,
                        'start_time' => $times[$slotIndex][0],
                        'end_time' => $times[$slotIndex][1],
                        'status' => 'open',
                        'max_instructors' => 1,
                    ]);

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

    // ───────────────────────────────────────────────
    //  BOOKINGS & PAYMENTS
    // ───────────────────────────────────────────────

    private function createBookingsAndPayments(School $school, array $students, array $instructors, array $courses, array $branches): void
    {
        if (empty($students) || empty($instructors) || empty($courses)) return;

        $bookingCount = rand(8, 12);
        $statuses = ['confirmed', 'confirmed', 'confirmed', 'completed', 'completed', 'pending', 'cancelled'];

        for ($i = 0; $i < $bookingCount; $i++) {
            $student = $students[array_rand($students)];
            $instructor = $instructors[array_rand($instructors)];
            $course = $courses[array_rand($courses)];
            $package = CoursePackage::where('course_id', $course->id)->inRandomOrder()->first();
            if (!$package) continue;

            $status = $statuses[array_rand($statuses)];
            $bookingDate = $status == 'completed' ? now()->subDays(rand(1, 30)) : now()->addDays(rand(1, 14));
            $branch = $branches[array_rand($branches)];

            $booking = Booking::create([
                'school_id' => $school->id,
                'branch_id' => $branch->id,
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

            if ($status == 'completed') {
                Payment::create([
                    'school_id' => $school->id,
                    'booking_id' => $booking->id,
                    'amount' => $package->price,
                    'paid_on' => $bookingDate,
                    'method' => ['cash', 'gcash', 'bank_transfer'][rand(0, 2)],
                    'status' => 'completed',
                ]);

                $existingProgress = Progress::where('student_id', $student->id)->where('course_id', $course->id)->first();
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

    // ───────────────────────────────────────────────
    //  NOTIFICATIONS
    // ───────────────────────────────────────────────

    private function createSampleNotifications(School $school, array $students, array $instructors, array $admins, array $guests): void
    {
        $slug = $school->slug;

        if (!empty($admins)) {
            $admin = $admins[0];
            Notification::send($admin, 'new_enrollment_request', 'New Enrollment Request',
                'A new student has requested enrollment in a driving course.', 'enrollment', "/{$slug}/admin/enrollments");
            Notification::send($admin, 'license_uploaded', 'License Pending Review',
                'A student has uploaded a driver\'s license for verification.', 'license', "/{$slug}/admin/enrollments");
        }

        if (!empty($students)) {
            Notification::send($students[0], 'enrollment_approved', 'Enrollment Approved!',
                'Your enrollment has been approved. Welcome aboard!', 'success', "/{$slug}/student");
            Notification::send($students[0], 'session_reminder', 'Upcoming Session',
                'You have a driving session tomorrow. Don\'t forget to bring your license!', 'session', "/{$slug}/student/schedule");
            if (count($students) > 1) {
                Notification::send($students[1], 'session_reminder', 'Session Tomorrow',
                    'Reminder: You have a practical driving session scheduled for tomorrow morning.', 'session', "/{$slug}/student/schedule");
            }
        }

        if (!empty($instructors)) {
            Notification::send($instructors[0], 'session_reminder', 'Upcoming Session',
                'You have a driving session with a student tomorrow at 9:00 AM.', 'session', "/{$slug}/instructor/my-schedule");
        }

        if (!empty($guests)) {
            Notification::send($guests[0], 'enrollment_received', 'Enrollment Request Submitted',
                'Your enrollment request has been submitted and is under review.', 'enrollment', "/{$slug}/guest/enrollment-requests");
            if (count($guests) > 2) {
                Notification::send($guests[2], 'enrollment_rejected', 'Enrollment Request Update',
                    'Your enrollment request was not approved. Check your email for details.', 'warning', "/{$slug}/guest/enrollment-requests");
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  CREDENTIALS SUMMARY
    // ═══════════════════════════════════════════════════════════════

    private function printCredentialsSummary(): void
    {
        $pw = $this->password;

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════╗');
        $this->command->info('║                    LOGIN CREDENTIALS                          ║');
        $this->command->info('╚══════════════════════════════════════════════════════════════╝');
        $this->command->info('');
        $this->command->info("   All passwords: {$pw}");
        $this->command->info('');
        $this->command->info('🔐 SYSTEM ADMINISTRATORS');
        $this->command->info('   systemadmin@gmail.com');
        $this->command->info('   systemadmin2@gmail.com');
        $this->command->info('   URL: /system-admin');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('🏫 SMART DRIVING SCHOOL  (25 branches) — slug: smart-driving');
        $this->command->info('   ADMIN:      schooladmin@gmail.com');
        $this->command->info('   SECRETARY:  secretary@gmail.com');
        $this->command->info('   INSTRUCTOR: instructor@gmail.com');
        $this->command->info('   STUDENT:    student@gmail.com');
        $this->command->info('   URL: /smart-driving');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('🏫 LYSPEED DRIVING SCHOOL  (10 branches) — slug: lyspeed-driving');
        $this->command->info('   ADMIN:      lyspeed.admin@gmail.com');
        $this->command->info('   INSTRUCTOR: lyspeed.instructor@gmail.com');
        $this->command->info('   STUDENT:    lyspeed.student@gmail.com');
        $this->command->info('   URL: /lyspeed-driving');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('🏫 DRIVED HUB DRIVING SCHOOL  (2 branches) — slug: drived-hub');
        $this->command->info('   ADMIN:       admin@gmail.com');
        $this->command->info('   MANAGER 1:   manager.clark@drivedhub.com');
        $this->command->info('   MANAGER 2:   manager.balibago@drivedhub.com');
        $this->command->info('   INSTRUCTORS: ricardo.cruz@drivedhub.com');
        $this->command->info('                maria.santos@drivedhub.com');
        $this->command->info('                angelo.ramos@drivedhub.com');
        $this->command->info('                sofia.torres@drivedhub.com');
        $this->command->info('   STUDENTS:    student1-10@gmail.com');
        $this->command->info('   GUESTS:      guest.enrolled1@drivedhub.test (enrolled)');
        $this->command->info('                guest.enrolled2@drivedhub.test (enrolled)');
        $this->command->info('                guest.new1@drivedhub.test (not enrolled)');
        $this->command->info('                guest.pending@drivedhub.test (pending request)');
        $this->command->info('                guest.rejected@drivedhub.test (rejected request)');
        $this->command->info('   COURSES:     3 PDC (Manual, Automatic, Motorcycle)');
        $this->command->info('                2 TDC (Standard 15hr, Refresher 8hr)');
        $this->command->info('   URL: /drived-hub');
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════╗');
        $this->command->info('║              ✓ UNIFIED SEEDER COMPLETED!                     ║');
        $this->command->info('╚══════════════════════════════════════════════════════════════╝');
        $this->command->info('');
    }
}
