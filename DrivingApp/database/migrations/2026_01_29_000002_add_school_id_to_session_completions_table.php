<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('session_completions', function (Blueprint $table) {
            $table->foreignId('school_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('schools')
                  ->onDelete('cascade');
            
            $table->index('school_id');
        });

        // Populate school_id from related enrollment (MySQL only)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("
                UPDATE session_completions sc
                INNER JOIN enrollments e ON sc.enrollment_id = e.id
                INNER JOIN students s ON e.student_id = s.id
                SET sc.school_id = s.school_id
                WHERE sc.school_id IS NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_completions', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
