<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter the category column to include 'booking' and 'schedule' categories
        DB::statement("ALTER TABLE system_logs MODIFY COLUMN category ENUM(
            'database', 
            'validation', 
            'authentication', 
            'authorization', 
            'file_upload', 
            'email', 
            'payment', 
            'api', 
            'system',
            'booking',
            'schedule',
            'user_management',
            'course',
            'other'
        ) DEFAULT 'other'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE system_logs MODIFY COLUMN category ENUM(
            'database', 
            'validation', 
            'authentication', 
            'authorization', 
            'file_upload', 
            'email', 
            'payment', 
            'api', 
            'system', 
            'other'
        ) DEFAULT 'other'");
    }
};
