<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Instructor extends Authenticatable
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
        'enrollment_date',
        'availability',
        'license_number',
        'bio',
        'profile_picture',
        'course_specializations',
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
}
