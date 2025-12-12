<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollment_requests', function (Blueprint $table) {
            // Add requested license type
            $table->enum('requested_license_type', ['non_professional', 'professional'])
                  ->after('course_id')
                  ->default('non_professional');
            
            // Add experience level
            $table->enum('experience_level', ['new_driver', 'experienced'])
                  ->after('requested_license_type')
                  ->default('new_driver');
            
            // Add file path for credentials (for experienced drivers)
            $table->string('credentials_file_path')
                  ->after('experience_level')
                  ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->dropColumn([
                'requested_license_type',
                'experience_level',
                'credentials_file_path'
            ]);
        });
    }
};
