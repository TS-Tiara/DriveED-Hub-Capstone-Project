<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'content',
        'attachments',
        'video_url',
        'sort_order',
    ];

    protected $casts = [
        'attachments' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get the module that owns this lesson
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    /**
     * Get the course through the module
     */
    public function course()
    {
        return $this->module->course();
    }

    /**
     * Scope to get lessons ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Check if lesson has attachments
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Check if lesson has video
     */
    public function hasVideo(): bool
    {
        return !empty($this->video_url);
    }
}
