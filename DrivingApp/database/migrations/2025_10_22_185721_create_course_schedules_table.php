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
        Schema::create('course_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('instructors')->onDelete('set null');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('max_students')->default(1); // How many students can book this slot
            $table->integer('booked_count')->default(0); // Current bookings
            $table->enum('status', ['available', 'full', 'cancelled'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'date', 'status']);
            $table->index('instructor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_schedules');
    }
};
