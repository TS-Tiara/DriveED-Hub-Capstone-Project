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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('enrollment_request_id')->nullable()->constrained('enrollment_requests')->onDelete('set null');
            
            // Enrollment status
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            
            // Timestamps for enrollment lifecycle
            $table->timestamp('enrolled_at');
            $table->timestamp('completed_at')->nullable();
            
            // Theoretical completion tracking (for theoretical courses only)
            $table->boolean('theoretical_passed')->default(false);
            $table->timestamp('theoretical_passed_at')->nullable();
            $table->foreignId('theoretical_passed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('theoretical_pass_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['student_id', 'status']);
            $table->index(['course_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
