<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('branches') && !Schema::hasColumn('branches', 'archived_previous_is_active')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->boolean('archived_previous_is_active')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('branches') && Schema::hasColumn('branches', 'archived_previous_is_active')) {
            Schema::table('branches', fn (Blueprint $table) => $table->dropColumn('archived_previous_is_active'));
        }
    }
};
