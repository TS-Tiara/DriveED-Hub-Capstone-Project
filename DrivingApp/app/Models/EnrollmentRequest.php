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
    ];

    protected $casts = [
        'approved_at' => 'datetime',
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

    public function approve($adminId)
    {
        // Update enrollment request status
        $this->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        // Simply update the learner's role from guest to student
        $this->learner->update(['role' => 'student']);
    }

    public function reject($remarks = null)
    {
        $this->update([
            'status' => 'rejected',
            'remarks' => $remarks,
        ]);
    }
}
