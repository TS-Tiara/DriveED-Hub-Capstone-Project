<?php

namespace App\Support;

use Carbon\Carbon;
use Throwable;

class SessionTimeWindow
{
    public static function includes(string $start, string $end): bool
    {
        try {
            $startAt = Carbon::parse($start);
            $endAt = Carbon::parse($end);
            $opening = $startAt->copy()->setTime(6, 0);
            $closing = $startAt->copy()->setTime(18, 0);

            return $startAt->greaterThanOrEqualTo($opening)
                && $startAt->lessThanOrEqualTo($closing)
                && $endAt->greaterThan($startAt)
                && $endAt->lessThanOrEqualTo($closing);
        } catch (Throwable) {
            return false;
        }
    }
}
