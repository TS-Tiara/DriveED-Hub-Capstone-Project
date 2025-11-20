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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('instructors')->onDelete('set null');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->dateTime('scheduled_at');
            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled, no-show
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('instructor_id');
            $table->index('course_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
