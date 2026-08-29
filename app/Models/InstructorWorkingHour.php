<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class InstructorWorkingHour extends Model
{
    use HasFactory;

    protected $table = 'instructor_working_hours';

    protected $fillable = [
        'instructor_id',
        'day_of_week',
        'shift_start',
        'shift_end',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function isWithinShift(Carbon $checkTime): bool
    {
        return $checkTime >= Carbon::parse($this->shift_start) && $checkTime <= Carbon::parse($this->shift_end);
    }

    public function isDuringBreak(Carbon $checkTime): bool
    {
        if (!$this->break_start || !$this->break_end) {
            return false;
        }
        $breakStart = Carbon::parse($this->break_start);
        $breakEnd = Carbon::parse($this->break_end);
        return $checkTime >= $breakStart && $checkTime <= $breakEnd;
    }

    public function getTeachableHoursAttribute(): float
    {
        $shiftStart = Carbon::parse($this->shift_start);
        $shiftEnd = Carbon::parse($this->shift_end);
        $totalMinutes = $shiftStart->diffInMinutes($shiftEnd);

        if ($this->break_start && $this->break_end) {
            $breakStart = Carbon::parse($this->break_start);
            $breakEnd = Carbon::parse($this->break_end);
            $totalMinutes -= $breakStart->diffInMinutes($breakEnd);
        }

        return $totalMinutes / 60;
    }

    public function getDayOfWeekNameAttribute(): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$this->day_of_week] ?? 'Unknown';
    }

    public function scopeForInstructor($query, int $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeForDay($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }
}
