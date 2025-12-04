<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * These indexes significantly improve query performance for common operations:
     * - Login queries (school_id + email lookups)
     * - Dashboard statistics (created_at, status filtering)
     * - Booking queries (scheduled_at, date ranges)
     */
    public function up(): void
    {
        // Helper function to check if index exists
        $indexExists = function(string $table, string $indexName): bool {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        };

        // Indexes for students table
        Schema::table('students', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('students', 'students_status_index')) {
                $table->index('status', 'students_status_index');
            }
            if (!$indexExists('students', 'students_role_index')) {
                $table->index('role', 'students_role_index');
            }
            if (!$indexExists('students', 'students_created_at_index')) {
                $table->index('created_at', 'students_created_at_index');
            }
        });

        // Indexes for instructors table
        Schema::table('instructors', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('instructors', 'instructors_status_index')) {
                $table->index('status', 'instructors_status_index');
            }
            if (!$indexExists('instructors', 'instructors_availability_index')) {
                $table->index('availability', 'instructors_availability_index');
            }
            if (!$indexExists('instructors', 'instructors_created_at_index')) {
                $table->index('created_at', 'instructors_created_at_index');
            }
            if (!$indexExists('instructors', 'instructors_status_availability_index')) {
                $table->index(['school_id', 'status', 'availability'], 'instructors_status_availability_index');
            }
        });

        // Indexes for admins table
        Schema::table('admins', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('admins', 'admins_role_index')) {
                $table->index('role', 'admins_role_index');
            }
        });

        // Indexes for time_slots table
        Schema::table('time_slots', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('time_slots', 'time_slots_date_index')) {
                $table->index('date', 'time_slots_date_index');
            }
            if (!$indexExists('time_slots', 'time_slots_status_index')) {
                $table->index('status', 'time_slots_status_index');
            }
            if (!$indexExists('time_slots', 'time_slots_school_date_status_index')) {
                $table->index(['school_id', 'date', 'status'], 'time_slots_school_date_status_index');
            }
        });

        // Indexes for bookings table
        Schema::table('bookings', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('bookings', 'bookings_student_status_index')) {
                $table->index(['student_id', 'status'], 'bookings_student_status_index');
            }
            if (!$indexExists('bookings', 'bookings_instructor_status_index')) {
                $table->index(['instructor_id', 'status'], 'bookings_instructor_status_index');
            }
            if (!$indexExists('bookings', 'bookings_scheduled_status_index')) {
                $table->index(['scheduled_at', 'status'], 'bookings_scheduled_status_index');
            }
            if (!$indexExists('bookings', 'bookings_payment_status_index')) {
                $table->index('payment_status', 'bookings_payment_status_index');
            }
        });

        // Indexes for enrollment_requests table
        Schema::table('enrollment_requests', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('enrollment_requests', 'enrollment_requests_status_index')) {
                $table->index('status', 'enrollment_requests_status_index');
            }
            if (!$indexExists('enrollment_requests', 'enrollment_requests_learner_index')) {
                $table->index('learner_id', 'enrollment_requests_learner_index');
            }
            if (!$indexExists('enrollment_requests', 'enrollment_requests_school_status_index')) {
                $table->index(['school_id', 'status'], 'enrollment_requests_school_status_index');
            }
        });

        // Indexes for courses table
        Schema::table('courses', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('courses', 'courses_title_index')) {
                $table->index('title', 'courses_title_index');
            }
            if (!$indexExists('courses', 'courses_type_index')) {
                $table->index('type', 'courses_type_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_status_index');
            $table->dropIndex('students_role_index');
            $table->dropIndex('students_created_at_index');
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->dropIndex('instructors_status_index');
            $table->dropIndex('instructors_availability_index');
            $table->dropIndex('instructors_created_at_index');
            $table->dropIndex('instructors_status_availability_index');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropIndex('admins_role_index');
        });

        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropIndex('time_slots_date_index');
            $table->dropIndex('time_slots_status_index');
            $table->dropIndex('time_slots_school_date_status_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_student_status_index');
            $table->dropIndex('bookings_instructor_status_index');
            $table->dropIndex('bookings_scheduled_status_index');
            $table->dropIndex('bookings_payment_status_index');
        });

        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->dropIndex('enrollment_requests_status_index');
            $table->dropIndex('enrollment_requests_learner_index');
            $table->dropIndex('enrollment_requests_school_status_index');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('courses_title_index');
            $table->dropIndex('courses_type_index');
        });
    }
};
