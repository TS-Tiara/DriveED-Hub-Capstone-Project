<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Run: php artisan db:seed
     * Or for fresh migration: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        // Use the new Unified Seeder for comprehensive test data
        $this->call([
            UnifiedSeeder::class,
        ]);

        // Legacy seeders (kept for reference, commented out)
        // $this->call([
        //     OldSchoolsSeeder::class,
        //     Alpha2TestSeeder::class,
        //     DemoSeeder::class,
        //     QuickTestSeeder::class,
        //     SystemAdminSeeder::class,
        // ]);
    }
}
