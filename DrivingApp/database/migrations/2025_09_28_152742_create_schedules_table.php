<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_id');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['available', 'removed', 'booked'])->default('available');
            $table->unsignedBigInteger('created_by')->nullable(); // admin
            $table->timestamps();

            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
            // $table->foreign('created_by')->references('id')->on('admins')->onDelete('cascade'); //uncommment when need
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};