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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('school_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('schools')
                  ->onDelete('cascade');
            
            $table->index('school_id');
        });

        // Populate school_id from related student (MySQL only)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("
                UPDATE enrollments e
                INNER JOIN students s ON e.student_id = s.id
                SET e.school_id = s.school_id
                WHERE e.school_id IS NULL
            ");
        }

        // Now make it non-nullable (skip for SQLite as it doesn't support this change)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->foreignId('school_id')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
