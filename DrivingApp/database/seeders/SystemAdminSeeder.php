<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * System Admin Seeder
 * 
 * This seeder creates the platform-level system administrators.
 * System admins are NOT associated with any school (school_id = null).
 * 
 * Run with: php artisan db:seed --class=SystemAdminSeeder
 * 
 * IMPORTANT: Only 2 system admins should exist.
 * Do NOT run this seeder multiple times without checking first.
 */
class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating System Administrators...');
        
        // System Admin 1 - Primary (You - Tiara)
        $admin1 = Admin::updateOrCreate(
            ['email' => 'tiara@driveedhub.com'],
            [
                'school_id' => null, // System admins are NOT associated with any school
                'name' => 'Tiara Santos',
                'password' => Hash::make('SystemAdmin@2024'),
                'role' => 'system_admin',
            ]
        );
        
        if ($admin1->wasRecentlyCreated) {
            $this->command->info("✓ Created System Admin: {$admin1->name} ({$admin1->email})");
        } else {
            $this->command->info("→ Updated System Admin: {$admin1->name} ({$admin1->email})");
        }

        // System Admin 2 - Secondary (Backup admin)
        $admin2 = Admin::updateOrCreate(
            ['email' => 'admin@driveedhub.com'],
            [
                'school_id' => null,
                'name' => 'System Administrator',
                'password' => Hash::make('SystemAdmin@2024'),
                'role' => 'system_admin',
            ]
        );
        
        if ($admin2->wasRecentlyCreated) {
            $this->command->info("✓ Created System Admin: {$admin2->name} ({$admin2->email})");
        } else {
            $this->command->info("→ Updated System Admin: {$admin2->name} ({$admin2->email})");
        }

        $this->command->newLine();
        $this->command->info('=== System Admin Credentials ===');
        $this->command->table(
            ['Email', 'Password'],
            [
                ['tiara@driveedhub.com', 'SystemAdmin@2024'],
                ['admin@driveedhub.com', 'SystemAdmin@2024'],
            ]
        );
        $this->command->warn('⚠️  Please change these passwords after first login!');
        $this->command->info('Login at: /system-admin');
    }
}
