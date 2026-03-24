<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Admin;
use App\Models\PaymentStatusLog;
use Illuminate\Support\Facades\DB;

class PaymentVerificationService
{
    /**
     * Approve a payment and handle role transition.
     */
    public function approve(Payment $payment, Admin $admin)
    {
        if ($payment->status !== 'pending') {
            throw new \Exception("Cannot approve payment in '{$payment->status}' status. Must be 'pending'.");
        }

        return DB::transaction(function () use ($payment, $admin) {
            $oldStatus = $payment->status;
            
            $payment->update([
                'status' => 'approved',
                'received_by_admin_id' => $admin->id,
                'received_at' => now(),
            ]);

            // 1. Log transition
            $this->logStatus($payment, $admin, 'approve', $oldStatus, 'approved');

            // 2. Role Transition: Guest -> Student
            if ($payment->payer) {
                $student = $payment->payer;
                if ($student->role === 'guest') {
                    $student->promoteToStudent();
                }
            }

            // 3. Linkage Logic: If enrollment request, mark it as payment confirmed
            if ($payment->enrollment_request_id) {
                $enrollment = $payment->enrollmentRequest;
                $enrollment->update([
                    'payment_status' => 'paid',
                    'payment_confirmed_at' => now(),
                    'payment_confirmed_by' => $admin->id,
                ]);
            }

            return $payment;
        });
    }

    /**
     * Reject a payment.
     */
    public function reject(Payment $payment, Admin $admin, $reasonCode, $reasonNote = null)
    {
        if ($payment->status !== 'pending') {
            throw new \Exception("Cannot reject payment in '{$payment->status}' status. Must be 'pending'.");
        }

        return DB::transaction(function () use ($payment, $admin, $reasonCode, $reasonNote) {
            $oldStatus = $payment->status;

            $payment->update([
                'status' => 'rejected',
                'rejection_reason_code' => $reasonCode,
                'rejection_reason_note' => $reasonNote,
            ]);

            $this->logStatus($payment, $admin, 'reject', $oldStatus, 'rejected', $reasonCode, $reasonNote);

            return $payment;
        });
    }

    /**
     * Refund a payment.
     */
    public function refund(Payment $payment, Admin $admin, $reasonCode, $reasonNote = null)
    {
        if ($payment->status !== 'approved') {
            throw new \Exception("Cannot refund payment in '{$payment->status}' status. Must be 'approved'.");
        }

        if (!($payment->school->enable_refunds ?? false)) {
            throw new \Exception("Refunds are currently disabled for this school.");
        }

        return DB::transaction(function () use ($payment, $admin, $reasonCode, $reasonNote) {
            $oldStatus = $payment->status;

            $payment->update([
                'status' => 'refunded',
                'refunded_by_admin_id' => $admin->id,
                'refunded_at' => now(),
                'refunded_amount' => $payment->amount,
            ]);

            $this->logStatus($payment, $admin, 'refund', $oldStatus, 'refunded', $reasonCode, $reasonNote);

            return $payment;
        });
    }

    /**
     * Internal status logging helper.
     */
    protected function logStatus(Payment $payment, $actor, $actionType, $from, $to, $code = null, $note = null)
    {
        PaymentStatusLog::create([
            'payment_id' => $payment->id,
            'school_id' => $payment->school_id,
            'actor_id' => $actor->id,
            'action_type' => $actionType,
            'from_status' => $from,
            'to_status' => $to,
            'reason_code' => $code,
            'reason_note' => $note,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
