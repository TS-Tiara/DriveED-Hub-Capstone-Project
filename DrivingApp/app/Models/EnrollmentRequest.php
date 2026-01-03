<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'learner_id',
        'course_id',
        'status',
        'payment_status',
        'remarks',
        'branch',
        'location',
        'approved_by',
        'approved_at',
        'requested_license_type',
        'experience_level',
        'credentials_file_path',
        // New enrollment fields
        'enrolled_at',
        'completed_at',
        'cancelled_at',
        'theoretical_passed',
        'theoretical_passed_at',
        'theoretical_passed_by',
        'theoretical_pass_notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'theoretical_passed' => 'boolean',
        'theoretical_passed_at' => 'datetime',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function learner()
    {
        return $this->belongsTo(Student::class, 'learner_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function theoreticalPassedBy()
    {
        return $this->belongsTo(Admin::class, 'theoretical_passed_by');
    }

    // New relationships (replacing old enrollments table)
    public function progress()
    {
        return $this->hasMany(Progress::class, 'enrollment_request_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'enrollment_request_id');
    }

    public function sessionCompletions()
    {
        return $this->hasMany(Progress::class, 'enrollment_request_id')
                    ->where('completed', true);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'enrollment_request_id');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isActive()
    {
        return $this->status === 'approved';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function approve($adminId)
    {
        // Update enrollment request status and set enrolled_at timestamp
        $this->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'enrolled_at' => now(),
        ]);

        // Update the learner's role from guest to student
        $this->learner->update(['role' => 'student']);
    }

    public function reject($remarks = null)
    {
        $this->update([
            'status' => 'rejected',
            'remarks' => $remarks,
        ]);
    }

    public function complete($adminId = null, $notes = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function cancel($remarks = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'remarks' => $remarks,
        ]);
    }

    public function markTheoreticalPassed($adminId, $notes = null)
    {
        $this->update([
            'theoretical_passed' => true,
            'theoretical_passed_at' => now(),
            'theoretical_passed_by' => $adminId,
            'theoretical_pass_notes' => $notes,
        ]);
    }
}
