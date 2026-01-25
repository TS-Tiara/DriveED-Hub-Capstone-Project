<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'instructor_id',
        'session_type',
        'hours_completed',
        'session_date',
        'session_time',
        'start_time',
        'end_time',
        'status',
        'notes',
        'logged_by',
    ];

    protected $casts = [
        'hours_completed' => 'decimal:2',
        'session_date' => 'date',
        'session_time' => 'datetime:H:i',
    ];

    /**
     * Get the enrollment that owns this session
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(EnrollmentRequest::class);
    }

    /**
     * Get the instructor who conducted this session
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Get the user who logged this session
     */
    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    /**
     * Get the student through enrollment
     */
    public function student()
    {
        return $this->enrollment->student();
    }

    /**
     * Get the course through enrollment
     */
    public function course()
    {
        return $this->enrollment->course();
    }

    /**
     * Scope for theoretical sessions
     */
    public function scopeTheoretical($query)
    {
        return $query->where('session_type', 'theoretical');
    }

    /**
     * Scope for practical sessions
     */
    public function scopePractical($query)
    {
        return $query->where('session_type', 'practical');
    }

    /**
     * Scope for sessions by date range
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('session_date', [$start, $end]);
    }

    /**
     * Scope for recent sessions
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('session_date', '>=', now()->subDays($days));
    }
}
