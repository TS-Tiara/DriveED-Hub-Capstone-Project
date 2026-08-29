<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('shift_start');
            $table->time('shift_end');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->timestamps();

            $table->unique(['instructor_id', 'day_of_week'], 'instructor_working_hours_unique');
            $table->index(['instructor_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_working_hours');
    }
};
