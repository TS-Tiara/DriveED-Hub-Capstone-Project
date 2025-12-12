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
        Schema::table('students', function (Blueprint $table) {
            // Add experience level (new driver or experienced)
            $table->enum('experience_level', ['new_driver', 'experienced'])
                  ->after('status')
                  ->default('new_driver');
            
            // Add flag to track if student has passed theoretical
            $table->boolean('has_passed_theoretical')
                  ->after('experience_level')
                  ->default(false);
            
            // Add timestamp when theoretical was passed
            $table->timestamp('theoretical_passed_at')
                  ->after('has_passed_theoretical')
                  ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'experience_level',
                'has_passed_theoretical',
                'theoretical_passed_at'
            ]);
        });
    }
};
