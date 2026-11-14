<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['enrollment_requests', 'payments', 'session_completions', 'time_slots'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'archived_at')) {
                    $table->timestamp('archived_at')->nullable();
                }
                if ($tableName !== 'time_slots' && !Schema::hasColumn($tableName, 'archived_with_user')) {
                    $table->boolean('archived_with_user')->default(false);
                }
            });
        }

        if (Schema::hasTable('session_completions')) {
            Schema::table('session_completions', function (Blueprint $table) {
                if (!Schema::hasColumn('session_completions', 'attachment_path')) {
                    $table->string('attachment_path')->nullable();
                }
                if (!Schema::hasColumn('session_completions', 'attachment_name')) {
                    $table->string('attachment_name')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['enrollment_requests', 'payments', 'session_completions', 'time_slots'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columns = $tableName === 'time_slots' ? ['archived_at'] : ['archived_at', 'archived_with_user'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('session_completions')) {
            Schema::table('session_completions', function (Blueprint $table) {
                foreach (['attachment_path', 'attachment_name'] as $column) {
                    if (Schema::hasColumn('session_completions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
