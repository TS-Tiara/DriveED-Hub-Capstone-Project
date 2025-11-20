<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'school_id',
        'name',
        'email',
        'password',
        'contact',
        'address',
        'status',
        'role',
        'branch',
        'location',
        'enrollment_date',
        'profile_picture',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function progresses()
    {
        return $this->hasMany(Progress::class);
    }

    public function enrollmentRequests()
    {
        return $this->hasMany(EnrollmentRequest::class, 'learner_id');
    }

    // Helper methods for role
    public function isGuest()
    {
        return $this->role === 'guest';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function promoteToStudent()
    {
        $this->update(['role' => 'student']);
    }
}