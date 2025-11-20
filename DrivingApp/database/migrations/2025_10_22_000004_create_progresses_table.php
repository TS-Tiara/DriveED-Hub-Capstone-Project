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
        Schema::create('progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->decimal('completion_percent', 5, 2)->default(0);
            $table->dateTime('last_updated')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('course_id');
            $table->index('completion_percent');
            $table->unique(['student_id', 'course_id']); // One progress record per student per course
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progresses');
    }
};
