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
        Schema::create('module_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('course_modules')->onDelete('cascade');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->json('attachments')->nullable(); // Array of file paths
            $table->string('video_url')->nullable(); // YouTube/Vimeo embed URL
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['module_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_lessons');
    }
};
