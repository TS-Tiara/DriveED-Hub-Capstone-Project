<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSetting extends Model
{
    protected $fillable = [
        'school_id',
        'primary_color',
        'secondary_color',
        'accent_color',
        'sidebar_bg_color',
        'sidebar_text_color',
        'sidebar_hover_color',
        'use_gradient_header',
        'header_text_color',
        'button_primary_bg',
        'button_primary_text',
        'button_secondary_bg',
        'button_secondary_text',
        'button_success_bg',
        'button_success_text',
        'button_danger_bg',
        'button_danger_text',
        'border_radius',
        'button_border_radius',
        'modal_header_bg',
        'modal_header_text',
        'modal_border_color',
        'card_border_color',
        'card_header_bg',
        'page_header_border',
        'badge_pending_bg',
        'badge_pending_text',
        'badge_approved_bg',
        'badge_approved_text',
        'badge_cancelled_bg',
        'badge_cancelled_text',
        'custom_css',
        'additional_settings',
    ];

    protected $casts = [
        'use_gradient_header' => 'boolean',
        'custom_css' => 'array',
        'additional_settings' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the gradient CSS for the header
     */
    public function getHeaderGradient(): string
    {
        if ($this->use_gradient_header) {
            return "linear-gradient(135deg, {$this->primary_color} 0%, {$this->secondary_color} 100%)";
        }
        return $this->primary_color;
    }

    /**
     * Get all colors as an array
     */
    public function getColorsArray(): array
    {
        return [
            'primary' => $this->primary_color,
            'secondary' => $this->secondary_color,
            'accent' => $this->accent_color,
            'sidebar_bg' => $this->sidebar_bg_color,
            'sidebar_text' => $this->sidebar_text_color,
            'sidebar_hover' => $this->sidebar_hover_color,
            'header_text' => $this->header_text_color,
        ];
    }

    /**
     * Generate CSS variables for all customization settings
     */
    public function getCssVariables(): string
    {
        return <<<CSS
        :root {
            /* Brand Colors */
            --primary-color: {$this->primary_color};
            --secondary-color: {$this->secondary_color};
            --accent-color: {$this->accent_color};
            
            /* Sidebar */
            --sidebar-bg: {$this->sidebar_bg_color};
            --sidebar-text: {$this->sidebar_text_color};
            --sidebar-hover: {$this->sidebar_hover_color};
            
            /* Header */
            --header-gradient: {$this->getHeaderGradient()};
            --header-text: {$this->header_text_color};
            --page-header-border: {$this->page_header_border};
            
            /* Buttons */
            --btn-primary-bg: {$this->button_primary_bg};
            --btn-primary-text: {$this->button_primary_text};
            --btn-secondary-bg: {$this->button_secondary_bg};
            --btn-secondary-text: {$this->button_secondary_text};
            --btn-success-bg: {$this->button_success_bg};
            --btn-success-text: {$this->button_success_text};
            --btn-danger-bg: {$this->button_danger_bg};
            --btn-danger-text: {$this->button_danger_text};
            
            /* Borders & Shapes */
            --border-radius: {$this->border_radius}px;
            --button-border-radius: {$this->button_border_radius}px;
            
            /* Modals */
            --modal-header-bg: {$this->modal_header_bg};
            --modal-header-text: {$this->modal_header_text};
            --modal-border-color: {$this->modal_border_color};
            
            /* Cards */
            --card-border-color: {$this->card_border_color};
            --card-header-bg: {$this->card_header_bg};
            
            /* Badges */
            --badge-pending-bg: {$this->badge_pending_bg};
            --badge-pending-text: {$this->badge_pending_text};
            --badge-approved-bg: {$this->badge_approved_bg};
            --badge-approved-text: {$this->badge_approved_text};
            --badge-cancelled-bg: {$this->badge_cancelled_bg};
            --badge-cancelled-text: {$this->badge_cancelled_text};
        }
        CSS;
    }
}
