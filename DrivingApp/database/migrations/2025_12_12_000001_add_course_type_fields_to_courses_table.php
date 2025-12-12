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
        Schema::table('courses', function (Blueprint $table) {
            // Add course type (theoretical or practical)
            $table->enum('course_type', ['theoretical', 'practical'])
                  ->after('title')
                  ->default('theoretical');
            
            // Add license type (non-professional or professional)
            $table->enum('license_type', ['non_professional', 'professional'])
                  ->after('course_type')
                  ->default('non_professional');
            
            // Add total hours required for this course
            $table->decimal('hours_required', 5, 2)
                  ->after('license_type')
                  ->default(8.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['course_type', 'license_type', 'hours_required']);
        });
    }
};
