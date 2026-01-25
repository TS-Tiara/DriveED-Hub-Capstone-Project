<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('session_completions', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('session_time');
            $table->time('end_time')->nullable()->after('start_time');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('completed')->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_completions', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'status']);
        });
    }
};
