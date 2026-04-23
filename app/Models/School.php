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
        'status',
        'timezone',
        'branding',
        'settings',
        'instructor_removal_notice_days',
    ];

    protected $casts = [
        'branding' => 'array',
        'settings' => 'array',
        'instructor_removal_notice_days' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }

    // Alias used by scoped route model binding for {targetAdmin}.
    public function targetAdmins()
    {
        return $this->admins();
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

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function scheduleInstructors()
    {
        return $this->hasMany(ScheduleInstructor::class);
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }

    public function phaseProgressions()
    {
        return $this->hasMany(PhaseProgression::class);
    }

    public function instructorRemovalRequests()
    {
        return $this->hasMany(InstructorRemovalRequest::class);
    }

    public function registrationRequests()
    {
        return $this->hasMany(RegistrationRequest::class);
    }

    public function studentActionRequests()
    {
        return $this->hasMany(StudentActionRequest::class);
    }


    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function systemLogs()
    {
        return $this->hasMany(SystemLog::class);
    }

    public function sessionCompletions()
    {
        return $this->hasMany(SessionCompletion::class);
    }

    public function gcashSettings()
    {
        return $this->hasMany(GCashSetting::class);
    }

    public function schoolSetting()
    {
        return $this->hasOne(SchoolSetting::class);
    }

    public function branches()
    {
        return $this->hasMany(\App\Models\Branch::class);
    }

    public function invitations()
    {
        return $this->hasMany(\App\Models\Invitation::class);
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
