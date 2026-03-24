<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class SystemAdminSeeder extends Seeder
{
    public function run(): void
    {
        $hashedPassword = Hash::make('P@ssw0rd123');

        $this->command->info('🔐 Creating System Administrators...');

        Admin::updateOrCreate(
            ['email' => 'systemadmin@gmail.com'],
            [
                'school_id' => null, 'name' => 'Tiara Angelica Santos',
                'password' => $hashedPassword, 'role' => 'system_admin', 'is_active' => true,
            ]
        );
        Admin::updateOrCreate(
            ['email' => 'systemadmin2@gmail.com'],
            [
                'school_id' => null, 'name' => 'Ricardo Jose Dela Cruz',
                'password' => $hashedPassword, 'role' => 'system_admin', 'is_active' => true,
            ]
        );

        $this->command->info('   ✓ 2 System Administrators created');
    }
}
