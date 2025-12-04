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
        $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->string('contact', 20)->nullable();
        $table->string('profile_picture')->nullable();
        $table->string('role')->default('school_admin');
        $table->boolean('is_active')->default(true);
        $table->rememberToken();
        $table->timestamps();
        
        // Unique email (system admins have no school, school admins unique per school)
        $table->unique('email');
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
