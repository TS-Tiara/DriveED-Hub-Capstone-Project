<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix foreign key constraints on session_completions and phase_progression_requests tables.
 * 
 * Both tables have enrollment_id columns that were originally constrained to the
 * legacy 'enrollments' table, but the system has fully transitioned to using
 * 'enrollment_requests' as the primary enrollment entity. This migration updates
 * the foreign keys to point to the correct table.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Fix session_completions.enrollment_id FK
        Schema::table('session_completions', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollment_requests')
                ->onDelete('cascade');
        });

        // Fix phase_progression_requests.enrollment_id FK
        Schema::table('phase_progression_requests', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollment_requests')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Revert session_completions FK back to enrollments
        Schema::table('session_completions', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->onDelete('cascade');
        });

        // Revert phase_progression_requests FK back to enrollments
        Schema::table('phase_progression_requests', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->onDelete('cascade');
        });
    }
};
