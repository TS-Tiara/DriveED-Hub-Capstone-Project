<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Student;
use App\Models\EnrollmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageController extends Controller
{
    /**
     * Serve a student license file securely
     */
    public function streamLicense(School $school, Student $student)
    {
        // 1. Authorization: Only the student themselves or an admin from the same school can view
        $isOwner = Auth::guard('student')->check() && Auth::guard('student')->id() === $student->id;
        $isAdmin = Auth::guard('admin')->check() && Auth::guard('admin')->user()->school_id === $school->id;
        
        abort_unless($isOwner || $isAdmin, 403, 'Unauthorized access to this document.');

        // 2. School Boundary
        abort_if((int)$student->school_id !== (int)$school->id, 404);

        if (!$student->student_license_path || !Storage::disk('local')->exists($student->student_license_path)) {
            abort(404, 'License file not found.');
        }

        return Storage::disk('local')->response($student->student_license_path);
    }

    /**
     * Serve an enrollment credential file securely
     */
    public function streamCredential(School $school, EnrollmentRequest $enrollment)
    {
        // 1. Authorization: Student owner or admin
        $isOwner = Auth::guard('student')->check() && (int)Auth::guard('student')->id() === (int)$enrollment->learner_id;
        $isAdmin = Auth::guard('admin')->check() && (int)Auth::guard('admin')->user()->school_id === (int)$school->id;
        
        abort_unless($isOwner || $isAdmin, 403, 'Unauthorized access to this document.');

        // 2. School Boundary
        abort_if((int)$enrollment->school_id !== (int)$school->id, 404);

        if (!$enrollment->credentials_file_path || !Storage::disk('local')->exists($enrollment->credentials_file_path)) {
            abort(404, 'Credential file not found.');
        }

        return Storage::disk('local')->response($enrollment->credentials_file_path);
    }
}
