<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'max_instructors',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function instructors()
    {
        return $this->belongsToMany(Instructor::class, 'schedule_instructors')
            ->withTimestamps()
            ->withPivot(['school_id', 'assignment_type']);
    }

    public function hasInstructor(int $instructorId): bool
    {
        return $this->instructors()->wherePivot('instructor_id', $instructorId)->exists();
    }

    public function isFull(): bool
    {
        return $this->instructors()->count() >= ($this->max_instructors ?? 1);
    }
}