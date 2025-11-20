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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('user_type')->nullable(); // 'admin', 'instructor', 'student', 'system'
            $table->enum('level', ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])->default('error');
            $table->enum('category', [
                'database', 
                'validation', 
                'authentication', 
                'authorization', 
                'file_upload', 
                'email', 
                'payment', 
                'api', 
                'system', 
                'other'
            ])->default('other');
            $table->string('action')->nullable(); // e.g., 'update_settings', 'create_booking', etc.
            $table->text('message');
            $table->text('exception_class')->nullable();
            $table->longText('stack_trace')->nullable();
            $table->json('context')->nullable(); // Additional context data
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, etc.
            $table->boolean('notified_admin')->default(false);
            $table->boolean('notified_system_admin')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['school_id', 'created_at']);
            $table->index(['level', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index('user_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
