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
        Schema::create('course_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('name'); // e.g., "SPECIAL", "RUSH", "EXECUTIVE"
            $table->string('transmission_type'); // manual or automatic
            $table->string('vehicle_type')->nullable();
            $table->decimal('price', 10, 2);
            $table->text('features')->nullable(); // JSON array of features
            $table->text('description')->nullable();
            $table->integer('training_hours')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_packages');
    }
};
