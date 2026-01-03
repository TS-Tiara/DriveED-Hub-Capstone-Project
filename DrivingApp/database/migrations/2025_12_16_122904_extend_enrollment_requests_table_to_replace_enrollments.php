<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollment_requests', function (Blueprint $table) {
            // Update status enum to include completed and cancelled
            $table->enum('status', ['pending', 'approved', 'completed', 'cancelled', 'rejected'])
                  ->default('pending')
                  ->change();
            
            // Add enrollment lifecycle timestamps
            $table->timestamp('enrolled_at')->nullable()->after('approved_at');
            $table->timestamp('completed_at')->nullable()->after('enrolled_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            
            // Add theoretical completion tracking (from enrollments table)
            $table->boolean('theoretical_passed')->default(false)->after('cancelled_at');
            $table->timestamp('theoretical_passed_at')->nullable()->after('theoretical_passed');
            $table->foreignId('theoretical_passed_by')->nullable()->constrained('admins')->onDelete('set null')->after('theoretical_passed_at');
            $table->text('theoretical_pass_notes')->nullable()->after('theoretical_passed_by');
        });
        
        // Migrate data from enrollments to enrollment_requests
        DB::statement("
            UPDATE enrollment_requests er
            INNER JOIN enrollments e ON e.enrollment_request_id = er.id
            SET 
                er.enrolled_at = e.enrolled_at,
                er.completed_at = e.completed_at,
                er.theoretical_passed = e.theoretical_passed,
                er.theoretical_passed_at = e.theoretical_passed_at,
                er.theoretical_passed_by = e.theoretical_passed_by,
                er.theoretical_pass_notes = e.theoretical_pass_notes,
                er.status = CASE
                    WHEN e.status = 'completed' THEN 'completed'
                    WHEN e.status = 'cancelled' THEN 'cancelled'
                    WHEN e.status = 'active' THEN 'approved'
                    ELSE er.status
                END
            WHERE e.enrollment_request_id = er.id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_requests', function (Blueprint $table) {
            // Revert status enum to original
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
            
            // Drop added columns
            $table->dropColumn([
                'enrolled_at',
                'completed_at',
                'cancelled_at',
                'theoretical_passed',
                'theoretical_passed_at',
                'theoretical_passed_by',
                'theoretical_pass_notes',
            ]);
        });
    }
};
