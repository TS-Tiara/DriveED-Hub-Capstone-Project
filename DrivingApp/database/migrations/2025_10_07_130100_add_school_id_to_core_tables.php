<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $defaultSchool = DB::table('schools')->where('slug', 'drivingschool1')->first();

        if (!$defaultSchool) {
            $defaultSchoolId = DB::table('schools')->insertGetId([
                'name' => 'Driving School 1',
                'slug' => 'drivingschool1',
                'timezone' => 'Asia/Manila',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $defaultSchoolId = $defaultSchool->id;
        }

        $this->addSchoolColumnWithData('admins', $defaultSchoolId, 'admins_email_unique', 'admins_school_email_unique');
        $this->addSchoolColumnWithData('students', $defaultSchoolId, 'students_email_unique', 'students_school_email_unique');
        $this->addSchoolColumnWithData('instructors', $defaultSchoolId, 'instructors_email_unique', 'instructors_school_email_unique');
        $this->addSchoolColumnWithData('time_slots', $defaultSchoolId);
        $this->addSchoolColumnWithData('schedules', $defaultSchoolId);
        $this->addSchoolColumnWithData('schedule_instructors', $defaultSchoolId);

        Schema::table('logs', function (Blueprint $table): void {
            if (!Schema::hasColumn('logs', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('id');
            }
        });

        DB::table('logs')->whereNull('school_id')->update(['school_id' => $defaultSchoolId]);

        Schema::table('logs', function (Blueprint $table): void {
            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table): void {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });

        $this->dropSchoolColumn('schedule_instructors');
        $this->dropSchoolColumn('schedules');
        $this->dropSchoolColumn('time_slots');
        $this->dropSchoolColumn('instructors', 'instructors_email_unique', 'instructors_school_email_unique');
        $this->dropSchoolColumn('students', 'students_email_unique', 'students_school_email_unique');
        $this->dropSchoolColumn('admins', 'admins_email_unique', 'admins_school_email_unique');
    }

    protected function addSchoolColumnWithData(string $table, int $schoolId, string $originalUnique = null, string $newUnique = null): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($table, $originalUnique, $newUnique): void {
            if (!Schema::hasColumn($table, 'school_id')) {
                $blueprint->foreignId('school_id')->nullable()->after('id');
            }
        });

    DB::table($table)->update(['school_id' => $schoolId]);

        if ($originalUnique && Schema::hasTable($table)) {
            Schema::table($table, function (Blueprint $blueprint) use ($originalUnique, $newUnique): void {
                $blueprint->dropUnique($originalUnique);
                $blueprint->unique(['school_id', 'email'], $newUnique);
            });
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `school_id` BIGINT UNSIGNED NOT NULL");
        }
    }

    protected function dropSchoolColumn(string $table, string $originalUnique = null, string $newUnique = null): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($originalUnique, $newUnique): void {
            $blueprint->dropForeign(['school_id']);

            if ($newUnique) {
                $blueprint->dropUnique($newUnique);
                $blueprint->unique('email', $originalUnique);
            }

            $blueprint->dropColumn('school_id');
        });
    }
};
