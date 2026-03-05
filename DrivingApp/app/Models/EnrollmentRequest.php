<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentRequest extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'learner_id',
        'course_id',
        'status',
        'payment_status',
        'payment_confirmed_by',
        'payment_confirmed_at',
        'payment_confirmation_notes',
        'remarks',
        'branch',
        'location',
        'requested_license_type',
        'experience_level',
        'credentials_file_path',
        'verification_notes',
        'approved_by',
        'approved_at',
        'enrolled_at',
        'completed_at',
        'cancelled_at',
        'rejected_at',
        'theoretical_passed',
        'theoretical_passed_at',
        'theoretical_passed_by',
        'theoretical_pass_notes',
        // New enrollment fields
        'package_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'theoretical_passed' => 'boolean',
        'theoretical_passed_at' => 'datetime',
        'payment_confirmed_at' => 'datetime',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branchRelation()
    {
        return $this->belongsTo(\App\Models\Branch::class , 'branch_id');
    }

    public function learner()
    {
        return $this->belongsTo(Student::class , 'learner_id');
    }

    // Alias for backward compatibility
    public function student()
    {
        return $this->learner();
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function package()
    {
        return $this->belongsTo(CoursePackage::class , 'package_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class , 'approved_by');
    }

    public function theoreticalPassedBy()
    {
        return $this->belongsTo(Admin::class , 'theoretical_passed_by');
    }

    public function paymentConfirmedBy()
    {
        return $this->belongsTo(Admin::class , 'payment_confirmed_by');
    }

    // New relationships (replacing old enrollments table)
    public function progress()
    {
        return $this->hasMany(Progress::class , 'enrollment_request_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class , 'enrollment_request_id');
    }

    public function sessionCompletions()
    {
        return $this->hasMany(SessionCompletion::class , 'enrollment_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class , 'enrollment_request_id');
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

    /**
     * Mark enrollment as completed with full lifecycle handling
     * (Unlock student from course and mark theoretical passed if applicable)
     */
    public function markAsCompleted(): void
    {
        $this->complete();

        // Unlock the student
        if ($this->learner) {
            $this->learner->unlockFromCourse();

            // If this was a theoretical course, mark student as passed theoretical
            if ($this->course && $this->course->course_type === 'theoretical') {
                $this->learner->markTheoreticalPassed();
            }
        }
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

    /**
     * Check if this request requires student permit verification
     * (Only practical courses require permit)
     */
    public function requiresPermitVerification(): bool
    {
        return $this->course && $this->course->course_type === 'practical';
    }

    /**
     * Check if student permit has been uploaded
     */
    public function hasStudentPermit(): bool
    {
        return !empty($this->credentials_file_path);
    }

    /**
     * Get the student permit file URL
     */
    public function getStudentPermitUrl(): ?string
    {
        if (!$this->hasStudentPermit()) {
            return null;
        }
        return asset('storage/' . $this->credentials_file_path);
    }

    /**
     * Check if learner has completed any theoretical course at this school
     */
    public function learnerHasCompletedTheoreticalHere(): bool
    {
        return $this->learner && $this->learner->has_passed_theoretical;
    }

    /**
     * Get learner's completed courses at this school (for internal history)
     */
    public function getLearnerCourseHistory()
    {
        if (!$this->learner) {
            return collect();
        }

        return self::where('learner_id', $this->learner_id)
            ->where('school_id', $this->school_id)
            ->where('status', 'completed')
            ->with('course')
            ->orderBy('completed_at', 'desc')
            ->get();
    }
}
