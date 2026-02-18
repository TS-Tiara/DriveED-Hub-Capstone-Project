<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'course_id',
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

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructors()
    {
        return $this->belongsToMany(Instructor::class, 'schedule_instructors')
            ->withTimestamps()
            ->withPivot(['school_id', 'assignment_type']);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'time_slot_id');
    }

    public function hasInstructor(int $instructorId): bool
    {
        return $this->instructors()->wherePivot('instructor_id', $instructorId)->exists();
    }

    public function isFull(): bool
    {
        return $this->instructors()->count() >= ($this->max_instructors ?? 1);
    }

    // Check if instructors can self-select this slot (has available spots)
    public function isOpenForSelection(): bool
    {
        return $this->status === 'open' &&
               $this->instructors()->count() < $this->max_instructors;
    }

    // Get available spots for instructors
    public function getAvailableSpots(): int
    {
        return max(0, $this->max_instructors - $this->instructors()->count());
    }

    // Get count of admin-assigned instructors
    public function getAdminAssignedCount(): int
    {
        return $this->instructors()
            ->wherePivot('assignment_type', 'admin_assigned')
            ->count();
    }

    // Get count of self-selected instructors
    public function getSelfSelectedCount(): int
    {
        return $this->instructors()
            ->wherePivot('assignment_type', 'self_selected')
            ->count();
    }

    // Get instructors by assignment type
    public function getAdminAssignedInstructors()
    {
        return $this->instructors()
            ->wherePivot('assignment_type', 'admin_assigned')
            ->get();
    }

    public function getSelfSelectedInstructors()
    {
        return $this->instructors()
            ->wherePivot('assignment_type', 'self_selected')
            ->get();
    }

    // Check if a specific instructor can select this slot
    public function canInstructorSelect(int $instructorId): bool
    {
        return $this->isOpenForSelection() && 
               !$this->hasInstructor($instructorId);
    }
}