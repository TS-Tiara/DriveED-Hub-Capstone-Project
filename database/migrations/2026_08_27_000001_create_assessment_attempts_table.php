<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('enrollment_request_id')->constrained('enrollment_requests')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->unsignedInteger('score');
            $table->unsignedInteger('total_points');
            $table->decimal('percentage', 5, 2);
            $table->boolean('passed')->default(false);
            $table->json('answers')->nullable();
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->index(['student_id', 'module_id', 'passed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_attempts');
    }
};
