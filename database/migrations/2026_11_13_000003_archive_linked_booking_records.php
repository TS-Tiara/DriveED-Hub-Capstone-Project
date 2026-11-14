<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['bookings', 'payments', 'session_completions'] as $tableName) {
            if (!Schema::hasTable($tableName)) continue;
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'archived_at')) $table->timestamp('archived_at')->nullable();
                if (!Schema::hasColumn($tableName, 'archived_with_user')) $table->boolean('archived_with_user')->default(false);
                if (!Schema::hasColumn($tableName, 'archived_with_schedule')) $table->boolean('archived_with_schedule')->default(false);
            });
        }
    }

    public function down(): void
    {
        foreach (['bookings', 'payments', 'session_completions'] as $tableName) {
            if (!Schema::hasTable($tableName)) continue;
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['archived_at', 'archived_with_user', 'archived_with_schedule'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) $table->dropColumn($column);
                }
            });
        }
    }
};
