<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\EnrollmentRequest;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;

class ReceiptAccessService
{
    public function canView(string $path, School $school, ?Admin $admin, ?Student $student): bool
    {
        $paths = [];
        foreach ([$path, '/'.$path, 'storage/'.$path, '/storage/'.$path] as $candidate) {
            $paths[] = $candidate;
            $paths[] = str_replace('/', '\\', $candidate);
        }

        $payments = Payment::where('school_id', $school->id)->whereIn('proof_of_payment_path', $paths);
        $enrollments = EnrollmentRequest::where('school_id', $school->id)->whereIn('payment_proof_path', $paths);

        if ($admin) {
            if (!$admin->isSystemAdmin() && (int) $admin->school_id !== (int) $school->id) {
                return false;
            }
            if ($admin->isBranchSecretary()) {
                if (!$admin->branch_id) return false;
                $payments->where(function ($query) use ($admin) {
                    $query->where('branch_id', $admin->branch_id)
                        ->orWhere(function ($legacy) use ($admin) {
                            $legacy->whereNull('branch_id')->where(function ($parent) use ($admin) {
                                $parent->whereHas('enrollmentRequest', fn ($enrollment) => $enrollment->where('branch_id', $admin->branch_id))
                                    ->orWhereHas('booking', fn ($booking) => $booking->where('branch_id', $admin->branch_id));
                            });
                        });
                });
                $enrollments->where('branch_id', $admin->branch_id);
            } elseif (!$admin->isSchoolAdmin() && !$admin->isSystemAdmin()) {
                return false;
            }
        } elseif ($student && (int) $student->school_id === (int) $school->id) {
            $payments->where(function ($query) use ($student) {
                $query->where('payer_user_id', $student->id)
                    ->orWhere(function ($legacy) use ($student) {
                        $legacy->whereNull('payer_user_id')->where(function ($parent) use ($student) {
                            $parent->whereHas('enrollmentRequest', fn ($enrollment) => $enrollment->where('learner_id', $student->id))
                                ->orWhereHas('booking', fn ($booking) => $booking->where('student_id', $student->id));
                        });
                    });
            });
            $enrollments->where('learner_id', $student->id);
        } else {
            return false;
        }

        return $payments->exists() || $enrollments->exists();
    }
}
