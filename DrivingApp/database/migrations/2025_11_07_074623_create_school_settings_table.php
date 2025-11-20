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
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->unique()->constrained('schools')->onDelete('cascade');
            
            // Brand Color Settings
            $table->string('primary_color', 7)->default('#2563eb'); // Blue
            $table->string('secondary_color', 7)->default('#fbbf24'); // Yellow/Gold
            $table->string('accent_color', 7)->default('#1e40af'); // Dark Blue
            $table->string('sidebar_bg_color', 7)->default('#ffffff'); // White
            $table->string('sidebar_text_color', 7)->default('#333333'); // Dark Gray
            $table->string('sidebar_hover_color', 7)->default('#f5f5f5'); // Light Gray
            
            // Header Settings
            $table->boolean('use_gradient_header')->default(true);
            $table->string('header_text_color', 7)->default('#ffffff'); // White
            
            // Button Customization
            $table->string('button_primary_bg', 7)->default('#667eea');
            $table->string('button_primary_text', 7)->default('#ffffff');
            $table->string('button_secondary_bg', 7)->default('#6c757d');
            $table->string('button_secondary_text', 7)->default('#ffffff');
            $table->string('button_success_bg', 7)->default('#28a745');
            $table->string('button_success_text', 7)->default('#ffffff');
            $table->string('button_danger_bg', 7)->default('#dc3545');
            $table->string('button_danger_text', 7)->default('#ffffff');
            
            // Border & Shape Customization
            $table->integer('border_radius')->default(8)->comment('Border radius in pixels (0-30)');
            $table->integer('button_border_radius')->default(8)->comment('Button border radius in pixels (0-30)');
            
            // Modal Customization
            $table->string('modal_header_bg', 7)->default('#667eea');
            $table->string('modal_header_text', 7)->default('#ffffff');
            $table->string('modal_border_color', 7)->default('#667eea');
            
            // Card/Panel Customization
            $table->string('card_border_color', 7)->default('#e5e7eb');
            $table->string('card_header_bg', 7)->default('#f9fafb');
            $table->string('page_header_border', 7)->default('#667eea');
            
            // Badge Customization
            $table->string('badge_pending_bg', 7)->default('#fbbf24');
            $table->string('badge_pending_text', 7)->default('#78350f');
            $table->string('badge_approved_bg', 7)->default('#10b981');
            $table->string('badge_approved_text', 7)->default('#065f46');
            $table->string('badge_cancelled_bg', 7)->default('#ef4444');
            $table->string('badge_cancelled_text', 7)->default('#7f1d1d');
            
            // Additional Settings (for future expansion)
            $table->json('custom_css')->nullable();
            $table->json('additional_settings')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
