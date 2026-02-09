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
        Schema::table('module_lessons', function (Blueprint $table) {
            $table->foreignId('school_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('schools')
                  ->onDelete('cascade');
            
            $table->index('school_id');
        });

        // Populate school_id from related module's course (MySQL only)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("
                UPDATE module_lessons ml
                INNER JOIN course_modules cm ON ml.module_id = cm.id
                INNER JOIN courses c ON cm.course_id = c.id
                SET ml.school_id = c.school_id
                WHERE ml.school_id IS NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_lessons', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
