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
        'experience_level',
        'has_passed_theoretical',
        'theoretical_passed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'has_passed_theoretical' => 'boolean',
            'theoretical_passed_at' => 'datetime',
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

    /**
     * Get courses the student is enrolled in through their bookings
     */
    public function enrolledCourses()
    {
        return $this->hasManyThrough(
            Course::class,
            Booking::class,
            'student_id', // Foreign key on bookings table
            'id', // Foreign key on courses table
            'id', // Local key on students table
            'course_id' // Local key on bookings table
        )->distinct();
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

    /**
     * Get all enrollments for this student
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get active enrollments
     */
    public function activeEnrollments()
    {
        return $this->enrollments()->where('status', 'active');
    }

    /**
     * Check if student has passed theoretical
     */
    public function hasPassedTheoretical(): bool
    {
        return $this->has_passed_theoretical === true;
    }

    /**
     * Check if student is new driver
     */
    public function isNewDriver(): bool
    {
        return $this->experience_level === 'new_driver';
    }

    /**
     * Check if student is experienced
     */
    public function isExperienced(): bool
    {
        return $this->experience_level === 'experienced';
    }

    /**
     * Check if student can enroll in practical courses
     */
    public function canEnrollPractical(): bool
    {
        return $this->hasPassedTheoretical();
    }

    /**
     * Mark student as passed theoretical
     */
    public function markTheoreticalPassed()
    {
        $this->update([
            'has_passed_theoretical' => true,
            'theoretical_passed_at' => now(),
        ]);
    }

    /**
     * Scope for students who passed theoretical
     */
    public function scopePassedTheoretical($query)
    {
        return $query->where('has_passed_theoretical', true);
    }

    /**
     * Scope for new drivers
     */
    public function scopeNewDrivers($query)
    {
        return $query->where('experience_level', 'new_driver');
    }

    /**
     * Scope for experienced drivers
     */
    public function scopeExperienced($query)
    {
        return $query->where('experience_level', 'experienced');
    }
}