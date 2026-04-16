<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->unsignedTinyInteger('profile_edit_count')->default(0);
            $table->timestamp('profile_locked_at')->nullable();
        });

        Schema::table('instructors', function (Blueprint $table): void {
            $table->unsignedTinyInteger('profile_edit_count')->default(0);
            $table->timestamp('profile_locked_at')->nullable();
        });

        Schema::create('profile_unlock_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->morphs('user');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'denied'])->default('pending');
            $table->foreignId('handled_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['user_type', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_unlock_requests');

        Schema::table('students', function (Blueprint $table): void {
            $table->dropColumn(['profile_edit_count', 'profile_locked_at']);
        });

        Schema::table('instructors', function (Blueprint $table): void {
            $table->dropColumn(['profile_edit_count', 'profile_locked_at']);
        });
    }
};
