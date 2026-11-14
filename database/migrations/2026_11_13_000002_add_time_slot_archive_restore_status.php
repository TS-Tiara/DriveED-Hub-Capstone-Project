<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('time_slots') && !Schema::hasColumn('time_slots', 'archive_previous_status')) {
            Schema::table('time_slots', function (Blueprint $table) {
                $table->string('archive_previous_status', 30)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('time_slots') && Schema::hasColumn('time_slots', 'archive_previous_status')) {
            Schema::table('time_slots', function (Blueprint $table) {
                $table->dropColumn('archive_previous_status');
            });
        }
    }
};
