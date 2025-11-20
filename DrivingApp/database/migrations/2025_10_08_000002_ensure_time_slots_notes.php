<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('time_slots')) {
            return;
        }

        Schema::table('time_slots', function (Blueprint $table): void {
            if (! Schema::hasColumn('time_slots', 'notes')) {
                $table->text('notes')->nullable()->after('max_instructors');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('time_slots')) {
            return;
        }

        Schema::table('time_slots', function (Blueprint $table): void {
            if (Schema::hasColumn('time_slots', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
