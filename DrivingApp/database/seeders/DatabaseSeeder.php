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
        $this->call([
            UnifiedSeeder::class,
            ContentProgressSeeder::class,
        ]);
    }
}
