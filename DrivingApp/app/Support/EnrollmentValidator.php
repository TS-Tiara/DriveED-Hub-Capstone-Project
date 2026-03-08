<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Student;
use App\Models\EnrollmentRequest;

class EnrollmentValidator
{
    /**
     * Check if a student can enroll in a specific course
     * 
     * @param Student $student
     * @param Course $course
     * @return array ['allowed' => bool, 'message' => string]
     */
    public static function canEnrollInCourse(Student $student, Course $course): array
    {
        // Theoretical courses: Anyone can enroll
        if ($course->isTheoretical()) {
            return [
                'allowed' => true,
                'message' => 'You can enroll in this theoretical course.'
            ];
        }

        // Practical courses: Must have passed theoretical first
        if ($course->isPractical()) {
            if (!$student->hasPassedTheoretical()) {
                return [
                    'allowed' => false,
                    'message' => 'You must complete and pass a theoretical course before enrolling in practical courses.'
                ];
            }

            /* 
             if (!$student->hasVerifiedLicense()) {
             return [
             'allowed' => false,
             'message' => 'You must have a verified student driver\'s license to enroll in practical courses. Please upload your license from your dashboard.'
             ];
             }
             */

            return [
                'allowed' => true,
                'message' => 'You can proceed with enrollment. Note: A verified license will be required before you can book driving sessions.'
            ];
        }

        return [
            'allowed' => true,
            'message' => 'You can enroll in this course.'
        ];
    }

    /**
     * Validate enrollment request based on experience level and course type
     * 
     * @param array $data Request data containing course_id, experience_level, credentials_file_path
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateEnrollmentRequest(array $data): array
    {
        $course = Course::find($data['course_id']);

        if (!$course) {
            return [
                'valid' => false,
                'message' => 'Course not found.'
            ];
        }

        // If applying for practical course
        if ($course->isPractical()) {
            // New drivers cannot apply for practical courses
            if ($data['experience_level'] === 'new_driver') {
                return [
                    'valid' => false,
                    'message' => 'New drivers must complete a theoretical course first. Please apply for a theoretical course.'
                ];
            }

        // Experienced drivers on practical courses - credential upload is now optional
        /* 
         if ($data['experience_level'] === 'experienced_driver') {
         if (empty($data['credentials_file_path'])) {
         return [
         'valid' => false,
         'message' => 'Please upload proof of your theoretical completion certificate.'
         ];
         }
         }
         */
        }

        return [
            'valid' => true,
            'message' => 'Enrollment request is valid.'
        ];
    }

    /**
     * Check if student has active enrollments
     * 
     * @param Student $student
     * @return bool
     */
    public static function hasActiveEnrollments(Student $student): bool
    {
        return $student->activeEnrollments()->exists();
    }

    /**
     * Get student's current active enrollment
     * 
     * @param Student $student
     * @return \App\Models\Enrollment|null
     */
    public static function getCurrentEnrollment(Student $student)
    {
        return $student->activeEnrollments()->first();
    }

    /**
     * Check if student can be marked as passed theoretical
     * 
     * @param \App\Models\Enrollment $enrollment
     * @return array ['allowed' => bool, 'message' => string]
     */
    public static function canMarkTheoreticalPassed($enrollment): array
    {
        // Must be a theoretical course
        if (!$enrollment->course->isTheoretical()) {
            return [
                'allowed' => false,
                'message' => 'Only theoretical course enrollments can be marked as passed.'
            ];
        }

        // Check if already marked as passed
        if ($enrollment->theoretical_passed) {
            return [
                'allowed' => false,
                'message' => 'This student has already been marked as passed for theoretical.'
            ];
        }

        // Check if student has completed required hours
        $totalHours = $enrollment->total_hours;
        $requiredHours = $enrollment->course->hours_required;

        if ($totalHours < $requiredHours) {
            return [
                'allowed' => false,
                'message' => "Student must complete {$requiredHours} hours. Currently completed: {$totalHours} hours."
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Student can be marked as passed.'
        ];
    }

    /**
     * Validate credential file for experienced drivers
     * 
     * @param \Illuminate\Http\UploadedFile|null $file
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateCredentialFile($file): array
    {
        if (!$file) {
            return [
                'valid' => false,
                'message' => 'Please upload a credential file.'
            ];
        }

        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return [
                'valid' => false,
                'message' => 'File size must not exceed 5MB.'
            ];
        }

        // Check file type
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedTypes)) {
            return [
                'valid' => false,
                'message' => 'File must be PDF, JPG, or PNG format.'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Credential file is valid.'
        ];
    }

    /**
     * Check if enrollment can be completed
     * 
     * @param \App\Models\Enrollment $enrollment
     * @return array ['allowed' => bool, 'message' => string]
     */
    public static function canCompleteEnrollment($enrollment): array
    {
        $totalHours = $enrollment->total_hours;
        $requiredHours = $enrollment->course->hours_required;

        if ($totalHours < $requiredHours) {
            return [
                'allowed' => false,
                'message' => "Student must complete {$requiredHours} hours. Currently completed: {$totalHours} hours."
            ];
        }

        // For theoretical courses, must be marked as passed
        if ($enrollment->course->isTheoretical() && !$enrollment->theoretical_passed) {
            return [
                'allowed' => false,
                'message' => 'Student must be marked as passed theoretical before completing enrollment.'
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Enrollment can be marked as complete.'
        ];
    }
}
