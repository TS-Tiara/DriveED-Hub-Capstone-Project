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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('generated_by')->constrained('admins')->onDelete('cascade');
            $table->string('report_type'); // enrollment, lessons, practical, financial, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('filters')->nullable(); // Store filter parameters used
            $table->json('data')->nullable(); // Store report data
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->string('file_path')->nullable(); // For exported files
            $table->string('file_type')->nullable(); // pdf, excel, csv
            $table->timestamps();
            
            $table->index(['school_id', 'report_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
