<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\EnrollmentRequest;
use App\Models\Payment;
use App\Models\SessionCompletion;
use App\Models\TimeSlot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurgeArchivedRecords extends Command
{
    protected $signature = 'records:purge-archived {--days= : Retention period in days (defaults to configured policy)}';
    protected $description = 'Permanently remove archived student records after their retention period';

    public function handle(): int
    {
        $days = max(30, (int) ($this->option('days') ?: config('records.archived_retention_days', 90)));
        $cutoff = now()->subDays($days);
        $attachmentPaths = [];

        $purged = DB::transaction(function () use ($cutoff, &$attachmentPaths) {
            $payments = Payment::whereNotNull('archived_at')->where('archived_at', '<=', $cutoff)->delete();

            $sessions = SessionCompletion::whereNotNull('archived_at')->where('archived_at', '<=', $cutoff)->get(['id', 'attachment_path']);
            $attachmentPaths = $sessions->pluck('attachment_path')->filter()->all();
            SessionCompletion::whereIn('id', $sessions->pluck('id'))->delete();
            $sessionCount = $sessions->count();

            $bookings = Booking::whereNotNull('archived_at')->where('archived_at', '<=', $cutoff)->delete();
            $timeSlots = TimeSlot::whereNotNull('archived_at')->where('archived_at', '<=', $cutoff)
                ->whereDoesntHave('bookings')->delete();

            $enrollments = EnrollmentRequest::whereNotNull('archived_at')->where('archived_at', '<=', $cutoff)
                ->whereDoesntHave('payments')
                ->whereDoesntHave('bookings')
                ->whereDoesntHave('sessionCompletions')
                ->delete();

            return ['payments' => $payments, 'sessions' => $sessionCount, 'bookings' => $bookings, 'timeSlots' => $timeSlots, 'enrollments' => $enrollments];
        });

        foreach ($attachmentPaths as $path) {
            Storage::disk('public')->delete($path);
        }

        $this->info("Purged archived records older than {$days} days: ".implode(', ', array_map(
            fn ($count, $kind) => "{$count} {$kind}",
            array_values($purged),
            ['payments', 'session logs', 'bookings', 'schedules', 'enrollments']
        )).'.');

        return self::SUCCESS;
    }
}
