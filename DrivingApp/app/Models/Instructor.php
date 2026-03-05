<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Instructor extends Authenticatable
{
    use HasSchoolScope;
    use HasFactory, Notifiable;

    protected $fillable = [
        'school_id',
        'branch_id',
        'name',
        'email',
        'password',
        'contact',
        'status',
        'availability',
        'license_number',
        'bio',
        'profile_picture',
        'course_specializations',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'address', // Added address field
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'course_specializations' => 'array',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function timeSlots()
    {
        return $this->belongsToMany(TimeSlot::class , 'schedule_instructors')
            ->withTimestamps()
            ->withPivot(['school_id', 'assignment_type']);
    }

    public function sessionCompletions()
    {
        return $this->hasMany(SessionCompletion::class);
    }

    public function removalRequests()
    {
        return $this->hasMany(InstructorRemovalRequest::class);
    }
}
