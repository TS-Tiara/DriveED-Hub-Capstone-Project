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

    public function resolveView(string $view): string
    {
        $sluggedView = $this->slug . '.' . $view;

        if (view()->exists($sluggedView)) {
            return $sluggedView;
        }

        return 'schools.default.' . $view;
    }
}
