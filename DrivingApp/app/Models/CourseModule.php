<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'course_id',
        'title',
        'description',
        'module_type',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the school
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the course that owns this module
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get all lessons for this module
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(ModuleLesson::class, 'module_id')->orderBy('sort_order');
    }

    /**
     * Scope to get modules ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope to get modules by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('module_type', $type);
    }
}
