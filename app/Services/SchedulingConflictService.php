<?php

namespace App\Services;

use App\Models\InstructorWorkingHour;
use App\Models\SessionCompletion;
use App\Models\SchoolSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SchedulingConflictService
{
    /**
     * Check if an instructor is available for a specific time slot
     *
     * @param int $instructorId
     * @param string $date Date in Y-m-d format
     * @param string $startTime Time in H:i:s or H:i format
     * @param string $endTime Time in H:i:s or H:i format
     * @param int|null $excludeSessionId Session ID to exclude (for updates)
     * @return array ['available' => bool, 'conflicts' => array, 'message' => string]
     */
    public function checkInstructorAvailability($instructorId, $date, $startTime, $endTime, $excludeSessionId = null)
    {
        $requestStart = Carbon::parse($date . ' ' . $startTime);
        $requestEnd = Carbon::parse($date . ' ' . $endTime);

        $query = SessionCompletion::where('instructor_id', $instructorId)
            ->where('session_date', $date)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->where(function($q) {
                $q->where('status', '!=', 'cancelled')
                  ->orWhereNull('status');
            });

        if ($excludeSessionId) {
            $query->where('id', '!=', $excludeSessionId);
        }

        $existingSessions = $query->get();

        $conflicts = [];
        foreach ($existingSessions as $session) {
            $sessionStart = Carbon::parse($session->session_date . ' ' . $session->start_time);
            $sessionEnd = Carbon::parse($session->session_date . ' ' . $session->end_time);

            if ($requestStart->lessThan($sessionEnd) && $requestEnd->greaterThan($sessionStart)) {
                $conflicts[] = [
                    'id' => $session->id,
                    'student_name' => $session->enrollment->student->name ?? 'Unknown',
                    'start_time' => Carbon::parse($session->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($session->end_time)->format('H:i'),
                    'date' => $session->session_date->format('Y-m-d'),
                    'status' => $session->status ?? 'completed',
                    'session_type' => $session->session_type,
                ];
            }
        }

        if (empty($conflicts)) {
            return [
                'available' => true,
                'conflicts' => [],
                'message' => 'Instructor is available for this time slot.',
            ];
        }

        $conflictTimes = array_map(function ($c) {
            return Carbon::parse($c['start_time'])->format('g:i A') . ' - ' . Carbon::parse($c['end_time'])->format('g:i A');
        }, $conflicts);

        return [
            'available' => false,
            'conflicts' => $conflicts,
            'message' => 'Instructor has conflicting sessions at: ' . implode(', ', $conflictTimes),
        ];
    }

    /**
     * Get available time slots for an instructor on a specific date
     *
     * @param int $instructorId
     * @param string $date Date in Y-m-d format
     * @param int $durationMinutes Duration of the session in minutes
     * @return array Array of available time slots
     */
    public function getAvailableTimeSlots($instructorId, $date, $durationMinutes = 60, $minGapMinutes = 15)
    {
        $workingHours = InstructorWorkingHour::where('instructor_id', $instructorId)
            ->where('day_of_week', (int) Carbon::parse($date)->format('w'))
            ->get();

        if ($workingHours->isEmpty()) {
            $workStart = Carbon::parse($date . ' 08:00:00');
            $workEnd = Carbon::parse($date . ' 18:00:00');
        } else {
            $workStart = null;
            $workEnd = null;
            foreach ($workingHours as $wh) {
                $shiftStart = Carbon::parse($date . ' ' . $wh->shift_start);
                $shiftEnd = Carbon::parse($date . ' ' . $wh->shift_end);
                if (!$workStart || $shiftStart < $workStart) $workStart = $shiftStart;
                if (!$workEnd || $shiftEnd > $workEnd) $workEnd = $shiftEnd;
            }
        }

        // Collect all blocked periods (booked sessions + breaks)
        $blockedPeriods = [];

        // Add break periods
        if ($workingHours->isNotEmpty()) {
            foreach ($workingHours as $wh) {
                if ($wh->break_start && $wh->break_end) {
                    $breakStart = Carbon::parse($date . ' ' . $wh->break_start);
                    $breakEnd = Carbon::parse($date . ' ' . $wh->break_end);
                    if ($breakEnd > $breakStart) {
                        $blockedPeriods[] = ['start' => $breakStart, 'end' => $breakEnd, 'type' => 'break'];
                    }
                }
            }
        }

        // Add booked session periods
        $bookedSessions = SessionCompletion::where('instructor_id', $instructorId)
            ->where('session_date', $date)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->where(function($q) {
                $q->where('status', '!=', 'cancelled')
                  ->orWhereNull('status');
            })
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        foreach ($bookedSessions as $session) {
            $sessionStart = Carbon::parse($date . ' ' . $session->start_time);
            $sessionEnd = Carbon::parse($date . ' ' . $session->end_time);
            $blockedPeriods[] = ['start' => $sessionStart, 'end' => $sessionEnd, 'type' => 'session'];
        }

        // Sort blocked periods by start time
        usort($blockedPeriods, function($a, $b) {
            return $a['start']->lt($b['start']) ? -1 : 1;
        });

        // Find available gaps between blocked periods
        $availableSlots = [];
        $currentTime = $workStart->copy();

        foreach ($blockedPeriods as $period) {
            // Skip periods that end before currentTime
            if ($period['end']->lte($currentTime)) {
                continue;
            }

            // Check if there's enough time before this blocked period
            if ($currentTime->diffInMinutes($period['start']) >= ($durationMinutes + $minGapMinutes)) {
                // Find the actual end of the available slot (limited by work end)
                $slotEnd = $period['start']->copy();
                if ($slotEnd->gt($workEnd)) {
                    $slotEnd = $workEnd->copy();
                }

                // Only add if we have enough duration
                if ($currentTime->diffInMinutes($slotEnd) >= $durationMinutes) {
                    $availableSlots[] = [
                        'start' => $currentTime->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'start_formatted' => $currentTime->format('g:i A'),
                        'end_formatted' => $slotEnd->format('g:i A'),
                    ];
                }
            }

            // Move currentTime to the end of this blocked period
            if ($period['end']->gt($currentTime)) {
                $currentTime = $period['end']->copy();
            }
        }

        // Check remaining time at end of day
        if ($currentTime->diffInMinutes($workEnd) >= $durationMinutes) {
            $availableSlots[] = [
                'start' => $currentTime->format('H:i'),
                'end' => $workEnd->format('H:i'),
                'start_formatted' => $currentTime->format('g:i A'),
                'end_formatted' => $workEnd->format('g:i A'),
            ];
        }

        return $availableSlots;
    }
    /**
     * Find alternative time slots when there's a conflict
     *
     * @param int $instructorId
     * @param string $date
     * @param int $durationMinutes
     * @param int $limit Number of suggestions to return
     * @return array
     */
    public function suggestAlternativeTimeSlots($instructorId, $date, $durationMinutes = 60, $limit = 3)
    {
        $availableSlots = $this->getAvailableTimeSlots($instructorId, $date, $durationMinutes);
        return array_slice($availableSlots, 0, $limit);
    }

    /**
     * Check for conflicts across multiple instructors (useful for batch scheduling)
     *
     * @param array $sessions Array of ['instructor_id' => int, 'date' => string, 'start_time' => string, 'end_time' => string]
     * @return array
     */
    public function checkBulkConflicts(array $sessions)
    {
        $results = [];

        foreach ($sessions as $index => $session) {
            $check = $this->checkInstructorAvailability(
                $session['instructor_id'],
                $session['date'],
                $session['start_time'],
                $session['end_time'],
                $session['exclude_session_id'] ?? null
            );

            $results[$index] = [
                'session' => $session,
                'available' => $check['available'],
                'conflicts' => $check['conflicts'],
                'message' => $check['message'],
            ];
        }

        return $results;
    }

    /**
     * Check if a time slot falls within an instructor's working hours
     *
     * @param int $instructorId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @return array ['within_hours' => bool, 'message' => string, 'working_hours' => array, 'available_hours' => float|null]
     */
    public function checkInstructorWorkingHours($instructorId, $date, $startTime, $endTime)
    {
        $checkStart = Carbon::parse($date . ' ' . $startTime);
        $checkEnd = Carbon::parse($date . ' ' . $endTime);
        $dayOfWeek = (int) $checkStart->format('w');

        $workingHours = InstructorWorkingHour::where('instructor_id', $instructorId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($workingHours->isEmpty()) {
            return [
                'within_hours' => true,
                'message' => 'No working hours defined for this day. Instructor is allowed.',
                'working_hours' => [],
                'available_hours' => null,
            ];
        }

        $inShift = false;
        $withinBreak = false;
        $totalTeachableHours = 0;

        foreach ($workingHours as $wh) {
            $totalTeachableHours += $wh->teachable_hours;

            if ($wh->isWithinShift($checkStart) && $wh->isWithinShift($checkEnd)) {
                $inShift = true;
            }

            if ($wh->isDuringBreak($checkStart) || $wh->isDuringBreak($checkEnd)) {
                $withinBreak = true;
            }
        }

        $withinHours = $inShift && !$withinBreak;

        return [
            'within_hours' => $withinHours,
            'message' => $withinHours
                ? 'Time slot is within working hours.'
                : 'Time slot is outside working hours or during a break period.',
            'working_hours' => $workingHours,
            'available_hours' => $totalTeachableHours,
        ];
    }

    /**
     * Get the total scheduled teaching hours for an instructor on a given date
     *
     * @param int $instructorId
     * @param string $date
     * @return float
     */
    public function getInstructorScheduledHours($instructorId, $date)
    {
        $scheduledSlots = DB::table('schedule_instructors')
            ->join('time_slots', 'schedule_instructors.time_slot_id', '=', 'time_slots.id')
            ->where('schedule_instructors.instructor_id', $instructorId)
            ->whereDate('time_slots.date', $date)
            ->where('time_slots.status', 'open')
            ->get();

        $totalMinutes = 0;
        foreach ($scheduledSlots as $slot) {
            $start = Carbon::parse($slot->start_time);
            $end = Carbon::parse($slot->end_time);
            $totalMinutes += $start->diffInMinutes($end);
        }

        return $totalMinutes / 60;
    }

    /**
     * Enforce session duration limits based on course type and school settings
     *
     * @param string $courseType 'theoretical' (TDC) or 'practical' (PDC)
     * @param string $startTime
     * @param string $endTime
     * @param SchoolSetting|null $schoolSettings
     * @return array ['valid' => bool, 'message' => string, 'duration_minutes' => int]
     */
    public function enforceSessionDurationLimits($courseType, $startTime, $endTime, $schoolSettings = null)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $durationMinutes = $start->diffInMinutes($end);

        $isTdc = $courseType === 'theoretical';

        if ($schoolSettings) {
            $minMinutes = $isTdc
                ? ($schoolSettings->min_tdc_duration_minutes ?? 60)
                : ($schoolSettings->min_pdc_duration_minutes ?? 60);
            $maxMinutes = $isTdc
                ? ($schoolSettings->max_tdc_duration_minutes ?? 300)
                : ($schoolSettings->max_pdc_duration_minutes ?? 180);
        } else {
            $minMinutes = 60;
            $maxMinutes = $isTdc ? 300 : 180;
        }

        if ($durationMinutes < $minMinutes) {
            return [
                'valid' => false,
                'message' => "Session duration ({$durationMinutes} mins) is below the minimum of {$minMinutes} minutes.",
                'duration_minutes' => $durationMinutes,
            ];
        }

        if ($durationMinutes > $maxMinutes) {
            return [
                'valid' => false,
                'message' => "Session duration ({$durationMinutes} mins) exceeds the maximum of {$maxMinutes} minutes.",
                'duration_minutes' => $durationMinutes,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Session duration is within allowed limits.',
            'duration_minutes' => $durationMinutes,
        ];
    }

    /**
     * Check if assigning an instructor to a new time slot would exceed their daily limit
     *
     * @param int $instructorId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int|null $minGapMinutes Minimum gap between sessions
     * @return array ['allowed' => bool, 'message' => string, 'scheduled_hours' => float, 'new_total_hours' => float, 'available_hours' => float|null]
     */
    public function checkDailyTeachingLimit($instructorId, $date, $startTime, $endTime, $minGapMinutes = 15)
    {
        $workingHoursCheck = $this->checkInstructorWorkingHours($instructorId, $date, $startTime, $endTime);
        $scheduledHours = $this->getInstructorScheduledHours($instructorId, $date);
        $slotHours = Carbon::parse($startTime)->diffInMinutes(Carbon::parse($endTime)) / 60;
        $newTotalHours = $scheduledHours + $slotHours;

        if (!$workingHoursCheck['within_hours']) {
            return [
                'allowed' => false,
                'message' => 'Time slot is outside working hours: ' . $workingHoursCheck['message'],
                'scheduled_hours' => $scheduledHours,
                'new_total_hours' => $newTotalHours,
                'available_hours' => $workingHoursCheck['available_hours'],
            ];
        }

        $availableHours = $workingHoursCheck['available_hours'];

        if ($availableHours !== null && $newTotalHours > $availableHours) {
            return [
                'allowed' => false,
                'message' => "Assigning this slot would exceed the daily teaching limit ({$availableHours} hrs). Already scheduled: {$scheduledHours} hrs, this slot: {$slotHours} hrs, total: {$newTotalHours} hrs.",
                'scheduled_hours' => $scheduledHours,
                'new_total_hours' => $newTotalHours,
                'available_hours' => $availableHours,
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Within daily teaching limit.',
            'scheduled_hours' => $scheduledHours,
            'new_total_hours' => $newTotalHours,
            'available_hours' => $availableHours,
        ];
    }
}
