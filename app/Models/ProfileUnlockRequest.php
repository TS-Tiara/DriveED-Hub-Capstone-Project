<?php

namespace App\Models;

use App\Traits\HasSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProfileUnlockRequest extends Model
{
    use HasSchoolScope;
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED = 'denied';

    protected $fillable = [
        'school_id',
        'user_id',
        'user_type',
        'reason',
        'status',
        'handled_by',
        'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public function handledBy()
    {
        return $this->belongsTo(Admin::class, 'handled_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function approve(Admin $admin): bool
    {
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'handled_by' => $admin->id,
            'handled_at' => now(),
        ]);
    }

    public function deny(Admin $admin): bool
    {
        return $this->update([
            'status' => self::STATUS_DENIED,
            'handled_by' => $admin->id,
            'handled_at' => now(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
