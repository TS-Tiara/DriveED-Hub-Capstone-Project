<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin Account
        Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        // Create Instructor Account
        Instructor::create([
            'name' => 'John Instructor',
            'email' => 'instructor@gmail.com',
            'password' => Hash::make('password123'),
            'contact' => '09123456789',
            'status' => 'active',
            'availability' => 'available',
            'license_number' => 'LIC-12345',
        ]);

        // Create Student Account
        Student::create([
            'name' => 'Jane Student',
            'email' => 'student@gmail.com',
            'password' => Hash::make('password123'),
            'contact' => '09987654321',
            'address' => '123 Main Street, City',
            'status' => 'active',
            'enrollment_date' => now(),
        ]);

        $this->command->info('Test users created successfully!');
        $this->command->info('Admin: admin@gmail.com / password123');
        $this->command->info('Instructor: instructor@gmail.com / password123');
        $this->command->info('Student: student@gmail.com / password123');
    }
}