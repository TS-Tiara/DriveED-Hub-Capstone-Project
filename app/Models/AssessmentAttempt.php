<?php

namespace App\Models;

use App\Traits\HasSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AssessmentAttempt extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'enrollment_request_id',
        'course_id',
        'module_id',
        'score',
        'total_points',
        'percentage',
        'passed',
        'answers',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'passed' => 'boolean',
            'answers' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollmentRequest(): BelongsTo
    {
        return $this->belongsTo(EnrollmentRequest::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }
}
