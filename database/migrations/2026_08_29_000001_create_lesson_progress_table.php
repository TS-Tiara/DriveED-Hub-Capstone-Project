<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('enrollment_request_id')->constrained('enrollment_requests')->cascadeOnDelete();
            $table->foreignId('module_lesson_id')->constrained('module_lessons')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_request_id', 'module_lesson_id']);
            $table->index(['student_id', 'module_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};