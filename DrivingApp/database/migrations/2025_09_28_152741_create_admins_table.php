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
    Schema::create('admins', function (Blueprint $table) {
        $table->id();
        $table->foreignId('school_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->string('contact', 20)->nullable();
        $table->string('profile_picture')->nullable();
        $table->string('role')->default('school_admin');
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
        Schema::dropIfExists('admins');
    }
};
