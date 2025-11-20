<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('time_slots')) {
            return;
        }

        Schema::table('time_slots', function (Blueprint $table): void {
            if (! Schema::hasColumn('time_slots', 'max_instructors')) {
                $table->unsignedInteger('max_instructors')->default(1)->after('status');
            }
        });

        if (Schema::hasColumn('time_slots', 'max_instructors')) {
            DB::table('time_slots')
                ->whereNull('max_instructors')
                ->update(['max_instructors' => 1]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('time_slots')) {
            return;
        }

        Schema::table('time_slots', function (Blueprint $table): void {
            if (Schema::hasColumn('time_slots', 'max_instructors')) {
                $table->dropColumn('max_instructors');
            }
        });
    }
};
