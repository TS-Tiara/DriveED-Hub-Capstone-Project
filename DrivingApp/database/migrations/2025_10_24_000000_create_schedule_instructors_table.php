<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_instructors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('time_slot_id')->constrained('time_slots')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->enum('assignment_type', ['admin_assigned', 'self_selected'])->default('admin_assigned');
            $table->boolean('has_pending_removal_request')->default(false);
            $table->timestamps();

            $table->unique(['time_slot_id', 'instructor_id'], 'schedule_instructors_unique_assignment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_instructors');
    }
};
