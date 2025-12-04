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
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('time_slot_id')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('booking_date')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, confirmed, completed, cancelled, pending, no-show
            
            // Cancellation Details
            $table->string('cancelled_by')->nullable(); // student, instructor, admin
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Attendance & Session Details
            $table->string('attendance_status')->nullable(); // attended, late, absent
            $table->string('session_status')->nullable(); // completed, cancelled, rescheduled, no-show
            $table->timestamp('attendance_marked_at')->nullable();
            $table->decimal('session_grade', 5, 2)->nullable();
            $table->json('skills_practiced')->nullable();
            
            // Payment
            $table->string('payment_status')->default('pending'); // pending, partial, paid, refunded
            $table->decimal('total_amount', 10, 2)->default(0);
            
            // Feedback & Notes
            $table->text('notes')->nullable();
            $table->text('instructor_feedback')->nullable();
            $table->text('student_feedback')->nullable();
            
            $table->timestamps();

            // Indexes for performance
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
