<?php

namespace App\Http\Controllers;

use App\Models\GCashSetting;
use App\Models\School;
use App\Models\Student;
use App\Models\Admin;
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
        $admin = Auth::guard('admin')->user();
        $isAdmin = $admin instanceof Admin && (int) $admin->school_id === (int) $school->id;
        
        abort_unless($isOwner || $isAdmin, 403, 'Unauthorized access to this document.');

        // 2. School Boundary
        abort_if((int)$student->school_id !== (int)$school->id, 404);

        if (!$student->student_license_path || !Storage::disk('local')->exists($student->student_license_path)) {
            abort(404, 'License file not found.');
        }

        return $this->fileResponseFromDisk('local', $student->student_license_path);
    }

    /**
     * Serve an enrollment credential file securely
     */
    public function streamCredential(School $school, EnrollmentRequest $enrollment)
    {
        // 1. Authorization: Student owner or admin
        $isOwner = Auth::guard('student')->check() && (int)Auth::guard('student')->id() === (int)$enrollment->learner_id;
        $admin = Auth::guard('admin')->user();
        $isAdmin = $admin instanceof Admin && (int) $admin->school_id === (int) $school->id;
        
        abort_unless($isOwner || $isAdmin, 403, 'Unauthorized access to this document.');

        // 2. School Boundary
        abort_if((int)$enrollment->school_id !== (int)$school->id, 404);

        if (!$enrollment->credentials_file_path || !Storage::disk('local')->exists($enrollment->credentials_file_path)) {
            abort(404, 'Credential file not found.');
        }

        return $this->fileResponseFromDisk('local', $enrollment->credentials_file_path);
    }

    /**
     * Serve a GCash payment image securely.
     */
    public function streamGcashQr(School $school, GCashSetting $gcashSetting)
    {
        $student = Auth::guard('student')->user();
        $admin = Auth::guard('admin')->user();

        $isStudent = Auth::guard('student')->check()
            && $student instanceof Student
            && (int) $student->school_id === (int) $school->id;
        $isAdmin = Auth::guard('admin')->check()
            && $admin instanceof Admin
            && (int) $admin->school_id === (int) $school->id;

        abort_unless($isStudent || $isAdmin, 403, 'Unauthorized access to this resource.');
        abort_if((int) $gcashSetting->school_id !== (int) $school->id, 404);
        abort_unless($gcashSetting->is_active, 404, 'GCash setting is not active.');

        $disk = Storage::disk('public');
        if (!$gcashSetting->qr_path || !$disk->exists($gcashSetting->qr_path)) {
            abort(404, 'GCash image not found.');
        }

        return $this->fileResponseFromDisk('public', $gcashSetting->qr_path);
    }

    private function fileResponseFromDisk(string $diskName, string $path)
    {
        $disk = Storage::disk($diskName);
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path));
    }
}
