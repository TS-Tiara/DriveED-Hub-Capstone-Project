<?php

namespace App\Models;

use App\Traits\HasSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentLessonProgress extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $table = 'lesson_progress';

    protected $fillable = [
        'school_id',
        'enrollment_request_id',
        'module_lesson_id',
        'student_id',
        'course_id',
        'module_id',
        'status',
        'completed_at',
        'completed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'completed_at' => 'datetime',
        ];
    }

    public function enrollmentRequest(): BelongsTo
    {
        return $this->belongsTo(EnrollmentRequest::class);
    }

    public function moduleLesson(): BelongsTo
    {
        return $this->belongsTo(ModuleLesson::class, 'module_lesson_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'completed_by');
    }

    public function markCompleted(?int $adminId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $adminId,
            'notes' => $notes,
        ]);
    }

    public function markInProgress(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    public function resetProgress(): void
    {
        $this->update([
            'status' => 'not_started',
            'completed_at' => null,
            'completed_by' => null,
            'notes' => null,
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isNotStarted(): bool
    {
        return $this->status === 'not_started';
    }

    public function scopeForEnrollment($query, int $enrollmentId)
    {
        return $query->where('enrollment_request_id', $enrollmentId);
    }

    public function scopeForModule($query, int $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}