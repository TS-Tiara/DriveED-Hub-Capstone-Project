<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'timezone',
        'branding',
        'settings',
        'instructor_removal_notice_days',
    ];

    protected $casts = [
        'branding' => 'array',
        'settings' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }

    public function instructors()
    {
        return $this->hasMany(Instructor::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function enrollmentRequests()
    {
        return $this->hasMany(EnrollmentRequest::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function scheduleInstructors()
    {
        return $this->hasMany(ScheduleInstructor::class);
    }

    public function schoolSetting()
    {
        return $this->hasOne(SchoolSetting::class);
    }

    public function resolveView(string $view): string
    {
        $sluggedView = $this->slug . '.' . $view;

        if (view()->exists($sluggedView)) {
            return $sluggedView;
        }

        return 'school.' . $view;
    }
}
