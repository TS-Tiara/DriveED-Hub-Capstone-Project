<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class OldSchoolsSeeder extends Seeder
{
    /**
     * Seed the old schools data (Smart Driving School and LySpeed Driving School)
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Old Schools Data...');

        // Create Smart Driving School
        $smartSchool = School::create([
            'name' => 'Smart Driving School',
            'slug' => 'smart-driving',
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
                'contact_number' => '+63 917 111 2222',
                'email' => 'info@smartdriving.com',
                'address' => 'Angeles City, Pampanga',
                'allow_self_registration' => true,
            ]),
            'instructor_removal_notice_days' => 7,
        ]);

        // Create LySpeed Driving School
        $lyspeedSchool = School::create([
            'name' => 'LySpeed Driving School',
            'slug' => 'lyspeed-driving',
            'timezone' => 'Asia/Manila',
            'branding' => json_encode([
                'logo' => null,
                'colors' => [
                    'primary' => '#8b5cf6',
                    'secondary' => '#6d28d9',
                    'accent' => '#ec4899',
                ]
            ]),
            'settings' => json_encode([
                'contact_number' => '+63 917 333 4444',
                'email' => 'info@lyspeed.com',
                'address' => 'San Fernando, Pampanga',
                'allow_self_registration' => true,
            ]),
            'instructor_removal_notice_days' => 7,
        ]);

        $this->command->info('✓ Schools created');

        // Smart Driving School - Admins
        Admin::create([
            'school_id' => $smartSchool->id,
            'name' => 'School Admin',
            'email' => 'schooladmin@gmail.com',
            'password' => Hash::make('password'),
        ]);

        Admin::create([
            'school_id' => $smartSchool->id,
            'name' => 'System Admin',
            'email' => 'systemadmin@gmail.com',
            'password' => Hash::make('password'),
        ]);

        // LySpeed Driving School - Admins
        Admin::create([
            'school_id' => $lyspeedSchool->id,
            'name' => 'LySpeed Admin',
            'email' => 'lyspeed.admin@gmail.com',
            'password' => Hash::make('password'),
        ]);

        Admin::create([
            'school_id' => $lyspeedSchool->id,
            'name' => 'LySpeed System',
            'email' => 'lyspeed.system@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $this->command->info('✓ Admins created');

        // Smart Driving School - Instructors
        Instructor::create([
            'school_id' => $smartSchool->id,
            'name' => 'Demo Instructor',
            'email' => 'instructor@gmail.com',
            'password' => Hash::make('password'),
            'contact' => '+63 917 555 1111',
            'license_number' => 'LIC-001',
            'course_specializations' => json_encode(['Manual Transmission']),
        ]);

        Instructor::create([
            'school_id' => $smartSchool->id,
            'name' => 'Ana Garcia',
            'email' => 'ana.garcia@gmail.com',
            'password' => Hash::make('password'),
            'contact' => '+63 917 555 2222',
            'license_number' => 'LIC-002',
            'course_specializations' => json_encode(['Automatic Transmission']),
        ]);

        Instructor::create([
            'school_id' => $smartSchool->id,
            'name' => 'Pedro Martinez',
            'email' => 'pedro.martinez@gmail.com',
            'password' => Hash::make('password'),
            'contact' => '+63 917 555 3333',
            'license_number' => 'LIC-003',
            'course_specializations' => json_encode(['Defensive Driving']),
        ]);

        Instructor::create([
            'school_id' => $smartSchool->id,
            'name' => 'Rosa Villanueva',
            'email' => 'rosa.villanueva@gmail.com',
            'password' => Hash::make('password'),
            'contact' => '+63 917 555 4444',
            'license_number' => 'LIC-004',
            'course_specializations' => json_encode(['Road Safety']),
        ]);

        // LySpeed Driving School - Instructors
        Instructor::create([
            'school_id' => $lyspeedSchool->id,
            'name' => 'LySpeed Instructor',
            'email' => 'lyspeed.instructor@gmail.com',
            'password' => Hash::make('password'),
            'contact' => '+63 917 666 1111',
            'license_number' => 'LIC-101',
            'course_specializations' => json_encode(['All Types']),
        ]);

        Instructor::create([
            'school_id' => $lyspeedSchool->id,
            'name' => 'Elena Ramos',
            'email' => 'elena.ramos@gmail.com',
            'password' => Hash::make('password'),
            'contact' => '+63 917 666 2222',
            'license_number' => 'LIC-102',
            'course_specializations' => json_encode(['Manual Transmission']),
        ]);

        Instructor::create([
            'school_id' => $lyspeedSchool->id,
            'name' => 'Fernando Cruz',
            'email' => 'fernando.cruz@gmail.com',
            'password' => Hash::make('password'),
            'contact' => '+63 917 666 3333',
            'license_number' => 'LIC-103',
            'course_specializations' => json_encode(['Highway Driving']),
        ]);

        $this->command->info('✓ Instructors created');

        // Create sample students for both schools
        $this->createStudents($smartSchool, 16, 'smart');
        $this->createStudents($lyspeedSchool, 10, 'lyspeed');

        $this->command->info('✓ Students created');

        $this->command->info('');
        $this->command->info('🎉 Old Schools Data Seeded Successfully!');
        $this->command->info('');
        $this->command->info('🏫 Smart Driving School');
        $this->command->info('   Admin: schooladmin@gmail.com / password');
        $this->command->info('   Instructors: 4 instructors');
        $this->command->info('   Students: 16 students');
        $this->command->info('');
        $this->command->info('🏫 LySpeed Driving School');
        $this->command->info('   Admin: lyspeed.admin@gmail.com / password');
        $this->command->info('   Instructors: 3 instructors');
        $this->command->info('   Students: 10 students');
    }

    private function createStudents(School $school, int $count, string $prefix): void
    {
        $names = [
            'Juan Dela Cruz', 'Maria Santos', 'Jose Reyes', 'Ana Lopez',
            'Carlos Garcia', 'Elena Mendoza', 'Roberto Fernandez', 'Sofia Ramirez',
            'Diego Torres', 'Isabella Morales', 'Miguel Castillo', 'Lucia Ortiz',
            'Antonio Ruiz', 'Carmen Herrera', 'Francisco Jimenez', 'Paula Alvarez'
        ];

        for ($i = 0; $i < $count; $i++) {
            Student::create([
                'school_id' => $school->id,
                'name' => $names[$i] ?? "Student $i",
                'email' => "{$prefix}.student{$i}@gmail.com",
                'password' => Hash::make('password'),
                'contact' => '+63 917 ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'address' => 'Pampanga, Philippines',
                'experience_level' => $i % 3 == 0 ? 'experienced' : 'new_driver',
                'has_passed_theoretical' => $i < 5 ? true : false,
            ]);
        }
    }
}
