<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedule_instructors')) {
            return;
        }

        Schema::table('schedule_instructors', function (Blueprint $table): void {
            if (! Schema::hasColumn('schedule_instructors', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('instructor_id')->constrained('schools')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('schedule_instructors', 'assignment_type')) {
                $table->enum('assignment_type', ['admin_assigned', 'self_selected'])->default('admin_assigned')->after('school_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('schedule_instructors')) {
            return;
        }

        Schema::table('schedule_instructors', function (Blueprint $table): void {
            if (Schema::hasColumn('schedule_instructors', 'assignment_type')) {
                $table->dropColumn('assignment_type');
            }

            if (Schema::hasColumn('schedule_instructors', 'school_id')) {
                $table->dropConstrainedForeignId('school_id');
            }
        });
    }
};
