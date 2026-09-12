<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Single source of truth for GBLSS Garho's school days/hours.
 * Change the schedule here only — every attendance check reads from this class.
 */
class SchoolScheduleService
{
    /** Returns [startTime, endTime] as "H:i" strings for the given date, or null if it's not a school day. */
    public function hoursFor(Carbon $date): ?array
    {
        $dayOfWeek = $date->dayOfWeekIso; // 1 = Monday ... 7 = Sunday

        if ($dayOfWeek === 6 || $dayOfWeek === 7) {
            return null; // Saturday, Sunday: school closed
        }

        if ($dayOfWeek === 5) {
            return ['08:00', '12:30']; // Friday
        }

        return ['08:00', '13:30']; // Monday–Thursday
    }

    public function isSchoolDay(Carbon $date): bool
    {
        return $this->hoursFor($date) !== null;
    }

    /** Teacher check-in window: 08:00–08:30 on school days. */
    public function isWithinCheckInWindow(Carbon $now): bool
    {
        if (! $this->isSchoolDay($now)) {
            return false;
        }

        $start = $now->copy()->setTimeFromTimeString('08:00');
        $end = $now->copy()->setTimeFromTimeString('08:30');

        return $now->between($start, $end);
    }

    /** Teacher check-out window: last 15 minutes before school ends. */
    public function isWithinCheckOutWindow(Carbon $now): bool
    {
        $hours = $this->hoursFor($now);
        if ($hours === null) {
            return false;
        }

        $end = $now->copy()->setTimeFromTimeString($hours[1]);
        $start = $end->copy()->subMinutes(15);

        return $now->between($start, $end);
    }
}
