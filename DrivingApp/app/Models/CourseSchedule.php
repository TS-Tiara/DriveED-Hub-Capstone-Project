<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'date',
        'start_time',
        'end_time',
        'max_students',
        'booked_count',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'max_students' => 'integer',
        'booked_count' => 'integer',
    ];

    /**
     * Get the course that owns this schedule
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the instructor assigned to this schedule
     */
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Get bookings for this course schedule
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'course_schedule_id');
    }

    /**
     * Check if schedule is available for booking
     */
    public function isAvailable()
    {
        return $this->status === 'available' && $this->booked_count < $this->max_students;
    }

    /**
     * Increment booked count
     */
    public function incrementBookedCount()
    {
        $this->booked_count++;
        
        if ($this->booked_count >= $this->max_students) {
            $this->status = 'full';
        }
        
        $this->save();
    }

    /**
     * Decrement booked count
     */
    public function decrementBookedCount()
    {
        if ($this->booked_count > 0) {
            $this->booked_count--;
            
            if ($this->status === 'full' && $this->booked_count < $this->max_students) {
                $this->status = 'available';
            }
            
            $this->save();
        }
    }
}
