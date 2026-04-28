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
        'license_image',
        'bio',
        'profile_picture',
        'course_specializations',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'last_logout_at',
        'address',
        'must_reset_password',
        'license_status',
        'restriction_codes',
        'license_rejection_reason',
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
            'last_logout_at' => 'datetime',
            'restriction_codes' => 'array',
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

    /**
     * Check if the instructor is legally authorized to teach a specific course.
     */
    public function canTeach($course)
    {
        // 1. Basic Verification Check
        if ($this->license_status !== 'verified') {
            return false;
        }

        // 2. Theoretical (TDC) Exception: Theory only needs a verified Pro license
        if ($course->type === 'theoretical') {
            return true;
        }

        // 3. Practical (PDC) Matching: Check if instructor has the required Code (A, B, C...)
        // This assumes Course model has a 'required_restriction' field or similar logic
        return in_array($course->required_restriction, $this->restriction_codes ?? []);
    }
}
