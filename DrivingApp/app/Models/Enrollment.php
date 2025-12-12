<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'enrollment_request_id',
        'status',
        'enrolled_at',
        'completed_at',
        'theoretical_passed',
        'theoretical_passed_at',
        'theoretical_passed_by',
        'theoretical_pass_notes',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'theoretical_passed' => 'boolean',
        'theoretical_passed_at' => 'datetime',
    ];

    /**
     * Get the student that owns this enrollment
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the course for this enrollment
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the enrollment request that led to this enrollment
     */
    public function enrollmentRequest(): BelongsTo
    {
        return $this->belongsTo(EnrollmentRequest::class);
    }

    /**
     * Get the user who marked theoretical as passed
     */
    public function theoreticalPassedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'theoretical_passed_by');
    }

    /**
     * Get all session completions for this enrollment
     */
    public function sessionCompletions(): HasMany
    {
        return $this->hasMany(SessionCompletion::class)->orderBy('session_date', 'desc');
    }

    /**
     * Calculate total hours completed
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->sessionCompletions()->sum('hours_completed');
    }

    /**
     * Calculate completion percentage
     */
    public function getCompletionPercentageAttribute(): float
    {
        $required = $this->course->hours_required;
        $completed = $this->total_hours;
        
        if ($required <= 0) return 0;
        
        return min(100, round(($completed / $required) * 100, 2));
    }

    /**
     * Check if enrollment is complete
     */
    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if enrollment is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope for active enrollments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed enrollments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for theoretical courses that passed
     */
    public function scopeTheoreticalPassed($query)
    {
        return $query->where('theoretical_passed', true);
    }
}
