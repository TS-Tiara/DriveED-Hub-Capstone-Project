<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->integer('max_tdc_duration_minutes')->default(300)->after('min_pdc_duration_minutes');
            $table->integer('max_pdc_duration_minutes')->default(180)->after('max_tdc_duration_minutes');
            $table->integer('min_gap_minutes_between_sessions')->default(15)->after('max_pdc_duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn([
                'max_tdc_duration_minutes',
                'max_pdc_duration_minutes',
                'min_gap_minutes_between_sessions',
            ]);
        });
    }
};
