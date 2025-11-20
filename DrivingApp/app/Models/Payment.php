<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'booking_id',
        'amount',
        'paid_on',
        'method',
        'reference',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_on' => 'datetime',
    ];

    /**
     * Get the school that owns the payment.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the booking for the payment.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Scope a query to only include payments for a specific school.
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope a query to only include payments with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include payments with a specific method.
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }
}
