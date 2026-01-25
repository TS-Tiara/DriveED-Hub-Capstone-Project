<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\School;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Course;
use App\Models\EnrollmentRequest;

/**
 * Quick Test Seeder - Backdoor for Easy Testing
 * 
 * Creates test accounts with simple, memorable credentials:
 * - All passwords: "password"
 * - Easy to remember emails
 * 
 * Run with: php artisan db:seed --class=QuickTestSeeder
 * Or add to DatabaseSeeder for automatic seeding
 */
class QuickTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating Quick Test Accounts...');

        // Get or create test school
        $school = School::firstOrCreate(
            ['slug' => 'test-school'],
            [
                'name' => 'Test Driving School',
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
                    'contact_number' => '+63 917 999 8888',
                    'email' => 'test@testschool.com',
                    'address' => 'Test City, Philippines',
                    'allow_self_registration' => true,
                ]),
                'instructor_removal_notice_days' => 7,
            ]
        );

        $this->command->info("✅ School: {$school->name} (slug: test-school)");

        // Create Admin Account
        $admin = Admin::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'school_id' => $school->id,
                'name' => 'Test Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        $this->command->info("✅ Admin: admin@test.com / password");

        // Create Instructor Accounts
        $instructor1 = Instructor::updateOrCreate(
            ['email' => 'instructor@test.com'],
            [
                'school_id' => $school->id,
                'name' => 'Test Instructor',
                'password' => Hash::make('password'),
                'contact' => '09111111111',
                'license_number' => 'INS-001',
                'status' => 'active',
            ]
        );
        $this->command->info("✅ Instructor: instructor@test.com / password");

        $instructor2 = Instructor::updateOrCreate(
            ['email' => 'instructor2@test.com'],
            [
                'school_id' => $school->id,
                'name' => 'John Instructor',
                'password' => Hash::make('password'),
                'contact' => '09222222222',
                'license_number' => 'INS-002',
                'status' => 'active',
            ]
        );
        $this->command->info("✅ Instructor 2: instructor2@test.com / password");

        // Create Student Accounts (Regular Students)
        $student1 = Student::updateOrCreate(
            ['email' => 'student@test.com', 'school_id' => $school->id],
            [
                'name' => 'Test Student',
                'password' => Hash::make('password'),
                'contact' => '09333333333',
                'role' => 'student',
                'status' => 'active',
            ]
        );
        $this->command->info("✅ Student: student@test.com / password");

        $student2 = Student::updateOrCreate(
            ['email' => 'student2@test.com', 'school_id' => $school->id],
            [
                'name' => 'Jane Student',
                'password' => Hash::make('password'),
                'contact' => '09444444444',
                'role' => 'student',
                'status' => 'active',
            ]
        );
        $this->command->info("✅ Student 2: student2@test.com / password");

        // Create Guest Accounts (Not yet approved)
        $guest1 = Student::updateOrCreate(
            ['email' => 'guest@test.com', 'school_id' => $school->id],
            [
                'name' => 'Test Guest',
                'password' => Hash::make('password'),
                'contact' => '09555555555',
                'role' => 'guest',
                'status' => 'active',
            ]
        );
        $this->command->info("✅ Guest: guest@test.com / password");

        $guest2 = Student::updateOrCreate(
            ['email' => 'guest2@test.com', 'school_id' => $school->id],
            [
                'name' => 'Mary Guest',
                'password' => Hash::make('password'),
                'contact' => '09666666666',
                'role' => 'guest',
                'status' => 'active',
            ]
        );
        $this->command->info("✅ Guest 2: guest2@test.com / password");

        // Create Courses
        $theoreticalCourse = Course::updateOrCreate(
            [
                'school_id' => $school->id,
                'title' => 'Theoretical Driving Course',
                'type' => 'theoretical'
            ],
            [
                'description' => 'Learn traffic rules and road signs',
                'duration_hours' => 15,
                'price' => 3000,
                'status' => 'active',
            ]
        );
        $this->command->info("✅ Course: Theoretical Driving Course");

        $practicalCourse = Course::updateOrCreate(
            [
                'school_id' => $school->id,
                'title' => 'Practical Driving Course',
                'type' => 'practical'
            ],
            [
                'description' => 'Hands-on driving practice',
                'duration_hours' => 20,
                'price' => 8000,
                'status' => 'active',
            ]
        );
        $this->command->info("✅ Course: Practical Driving Course");

        // Create Pending Enrollment Requests (for testing approval)
        EnrollmentRequest::updateOrCreate(
            [
                'learner_id' => $guest1->id,
                'course_id' => $theoreticalCourse->id,
            ],
            [
                'school_id' => $school->id,
                'status' => 'pending',
            ]
        );
        $this->command->info("✅ Enrollment Request: Test Guest → Theoretical (Pending)");

        EnrollmentRequest::updateOrCreate(
            [
                'learner_id' => $guest2->id,
                'course_id' => $practicalCourse->id,
            ],
            [
                'school_id' => $school->id,
                'status' => 'pending',
            ]
        );
        $this->command->info("✅ Enrollment Request: Mary Guest → Practical (Pending)");

        // Create Approved Enrollment (for testing student features)
        EnrollmentRequest::updateOrCreate(
            [
                'learner_id' => $student1->id,
                'course_id' => $theoreticalCourse->id,
            ],
            [
                'school_id' => $school->id,
                'status' => 'approved',
                'approved_at' => now()->subDays(4),
                'approved_by' => $admin->id,
            ]
        );
        $this->command->info("✅ Enrollment: Test Student → Theoretical (Approved)");

        $this->command->info('');
        $this->command->info('🎉 Quick Test Accounts Created Successfully!');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('✨ VIEW ALL TEST CREDENTIALS IN YOUR BROWSER:');
        $this->command->info('');
        $this->command->info('   🔗 http://localhost:8000/test/credentials/test-school');
        $this->command->info('');
        $this->command->info('   Features:');
        $this->command->info('   • Beautiful UI with all test accounts');
        $this->command->info('   • Copy buttons for quick credential access');
        $this->command->info('   • Direct login links for each account');
        $this->command->info('   • Mobile-friendly responsive design');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('📝 Quick Reference (All passwords: "password"):');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('🏫 School URL: http://localhost:8000/test-school');
        $this->command->info('');
        $this->command->info('👤 ADMIN:');
        $this->command->info('   Email: admin@test.com');
        $this->command->info('   Password: password');
        $this->command->info('   URL: http://localhost:8000/test-school/admin/dashboard');
        $this->command->info('');
        $this->command->info('👨‍🏫 INSTRUCTORS:');
        $this->command->info('   Email: instructor@test.com | instructor2@test.com');
        $this->command->info('   Password: password');
        $this->command->info('   URL: http://localhost:8000/test-school/instructor/dashboard');
        $this->command->info('');
        $this->command->info('🎓 STUDENTS (Approved):');
        $this->command->info('   Email: student@test.com | student2@test.com');
        $this->command->info('   Password: password');
        $this->command->info('   URL: http://localhost:8000/test-school/student/dashboard');
        $this->command->info('');
        $this->command->info('👤 GUESTS (Pending Approval):');
        $this->command->info('   Email: guest@test.com | guest2@test.com');
        $this->command->info('   Password: password');
        $this->command->info('   URL: http://localhost:8000/test-school/guest/dashboard');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
