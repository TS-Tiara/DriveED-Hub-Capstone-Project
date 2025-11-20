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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->text('features')->nullable(); // JSON field for course features
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('duration_hours', 5, 1)->default(0);
            $table->integer('max_students')->nullable();
            $table->string('type')->default('standard'); // standard, intensive, refresher, etc.
            $table->string('vehicle_type')->nullable(); // sedan, suv, manual, automatic
            $table->string('status')->default('active'); // active, inactive, archived
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('school_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
