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
        Schema::create('session_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('instructors')->onDelete('cascade');
            
            // Session details
            $table->enum('session_type', ['theoretical', 'practical']);
            $table->decimal('hours_completed', 5, 2); // e.g., 2.5 hours
            $table->date('session_date');
            $table->time('session_time')->nullable();
            $table->text('notes')->nullable();
            
            // Who logged this session
            $table->foreignId('logged_by')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['enrollment_id', 'session_date']);
            $table->index(['instructor_id', 'session_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_completions');
    }
};
