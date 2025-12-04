<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class AdminAccountSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $schools = School::all();
        
        foreach ($schools as $school) {
            // Create Admin Account
            $existingAdmin = Admin::where('school_id', $school->id)
                ->where('email', 'admin@gmail.com')
                ->first();
            
            if (!$existingAdmin) {
                Admin::create([
                    'school_id' => $school->id,
                    'name' => 'Admin',
                    'email' => 'admin@gmail.com',
                    'password' => Hash::make('password123'),
                    'role' => 'school_admin',
                ]);
                $this->command->info("✓ Created admin account for {$school->name}");
            } else {
                $this->command->info("  Admin account already exists for {$school->name}");
            }
            
            // Create Instructor Account
            $existingInstructor = Instructor::where('school_id', $school->id)
                ->where('email', 'instructor@gmail.com')
                ->first();
            
            if (!$existingInstructor) {
                Instructor::create([
                    'school_id' => $school->id,
                    'name' => 'Instructor',
                    'email' => 'instructor@gmail.com',
                    'password' => Hash::make('password123'),
                    'license_number' => 'INST-' . $school->id . '-001',
                    'status' => 'active',
                    'availability' => 'available',
                ]);
                $this->command->info("✓ Created instructor account for {$school->name}");
            } else {
                $this->command->info("  Instructor account already exists for {$school->name}");
            }
            
            // Create Student Account
            $existingStudent = Student::where('school_id', $school->id)
                ->where('email', 'student@gmail.com')
                ->first();
            
            if (!$existingStudent) {
                Student::create([
                    'school_id' => $school->id,
                    'name' => 'Student',
                    'email' => 'student@gmail.com',
                    'password' => Hash::make('password123'),
                    'status' => 'active',
                    'role' => 'student',
                ]);
                $this->command->info("✓ Created student account for {$school->name}");
            } else {
                $this->command->info("  Student account already exists for {$school->name}");
            }
            
            $this->command->info(""); // Empty line between schools
        }
        
        $this->command->info("🎉 All accounts created successfully!\n");
        $this->command->info("📧 Admin Email: admin@gmail.com");
        $this->command->info("📧 Instructor Email: instructor@gmail.com");
        $this->command->info("📧 Student Email: student@gmail.com");
        $this->command->info("🔑 Password (all): password123");
    }
}
