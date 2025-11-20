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
    Schema::create('instructors', function (Blueprint $table) {
        $table->id();
        $table->foreignId('school_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->string('contact')->nullable();
        $table->string('license_number')->nullable();
        $table->text('bio')->nullable();
        $table->string('profile_picture')->nullable();
        $table->string('status')->default('active');
        $table->enum('availability', ['available', 'unavailable'])->default('available');
        $table->timestamps();
        
        // Composite unique key for email within school
        $table->unique(['school_id', 'email']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};
