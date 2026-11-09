<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            foreach (['pending', 'approved', 'cancelled'] as $status) {
                $table->boolean('badge_'.$status.'_bold')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', fn (Blueprint $table) => $table->dropColumn(['badge_pending_bold', 'badge_approved_bold', 'badge_cancelled_bold']));
    }
};
