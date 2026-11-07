<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for GBLSS Garho's school days/hours.
 * Values come from the school_info settings row (admin School Settings page),
 * with safe defaults if a value is missing.
 */
class SchoolScheduleService
{
    private ?object $row = null;
    private bool $loaded = false;

    private function setting(string $key, string $default): string
    {
        if (! $this->loaded) {
            $this->loaded = true;
            try {
                $this->row = DB::table('school_info')->where('id', 1)->first();
            } catch (\Throwable $e) {
                $this->row = null;
            }
        }
        $v = $this->row->$key ?? null;

        return ($v === null || trim((string) $v) === '') ? $default : trim((string) $v);
    }

    /**
     * Sindh Government school timings (single shift, secondary).
     * Mon-Thu & Sat: summer (1 Apr - 31 Oct) 08:00 - 13:30, winter (1 Nov - 31 Mar) 08:30 - 14:00.
     * Friday: summer 08:00 - 12:00, winter 08:30 - 12:30.
     * Check-in window = first 30 min; check-out window = last 20 min of the day.
     */
    private function season(Carbon $d): array
    {
        $summer = $d->month >= 4 && $d->month <= 10;
        $start = $summer ? '08:00' : '08:30';
        if ($d->dayOfWeek === 5) {
            $end = $summer ? '12:00' : '12:30';
        } else {
            $end = $summer ? '13:30' : '14:00';
        }

        return [
            'in_start' => $start,
            'in_end' => Carbon::createFromFormat('H:i', $start)->addMinutes(30)->format('H:i'),
            'out_start' => Carbon::createFromFormat('H:i', $end)->subMinutes(20)->format('H:i'),
            'out_end' => $end,
        ];
    }

    private function offDays(): array
    {
        $raw = $this->setting('weekly_off_days', '0,6');

        return array_map('intval', array_filter(explode(',', $raw), fn ($x) => trim($x) !== ''));
    }

    private function isFriday(Carbon $d): bool
    {
        return $d->dayOfWeek === 5;
    }

    /** Returns [startTime, endTime] as "H:i" strings for the given date, or null if it's not a school day. */
    public function hoursFor(Carbon $date): ?array
    {
        if (in_array($date->dayOfWeek, $this->offDays(), true)) {
            return null;
        }
        $season = $this->season($date);
        $start = $season['in_start'];
        $end = $season['out_end'];

        return [$start, $end];
    }

    public function isSchoolDay(Carbon $date): bool
    {
        return $this->hoursFor($date) !== null;
    }

    /** Teacher check-in window on school days. */
    public function isWithinCheckInWindow(Carbon $now): bool
    {
        if (! $this->isSchoolDay($now)) {
            return false;
        }
        $season = $this->season($now);
        $start = $now->copy()->setTimeFromTimeString($season['in_start']);
        $end = $now->copy()->setTimeFromTimeString($season['in_end']);

        return $now->between($start, $end);
    }

    private function formatTime(string $hhmm): string
    {
        try {
            return Carbon::createFromFormat('H:i', substr($hhmm, 0, 5))->format('g:i A');
        } catch (\Throwable $e) {
            return $hhmm;
        }
    }

    /** Human-readable check-in window from the settings, e.g. "8:30 AM – 9:00 AM". */
    public function checkInWindowLabel(?Carbon $now = null): string
    {
        $season = $this->season($now ?? Carbon::now());

        return $this->formatTime($season['in_start']) . ' – ' . $this->formatTime($season['in_end']);
    }

    /** Human-readable check-out window for the given day (Friday differs). */
    public function checkOutWindowLabel(Carbon $now): string
    {
        $season = $this->season($now);

        return $this->formatTime($season['out_start']) . ' – ' . $this->formatTime($season['out_end']);
    }

    /** Teacher check-out window (Friday has its own window). */
    public function isWithinCheckOutWindow(Carbon $now): bool
    {
        if (! $this->isSchoolDay($now)) {
            return false;
        }
        $season = $this->season($now);
        $s = $season['out_start'];
        $e = $season['out_end'];

        return $now->between($now->copy()->setTimeFromTimeString($s), $now->copy()->setTimeFromTimeString($e));
    }

    /** True on school days between the day's start and end time. */
    public function isWithinSchoolHours(Carbon $now): bool
    {
        $hours = $this->hoursFor($now);
        if ($hours === null) {
            return false;
        }

        return $now->between(
            $now->copy()->setTimeFromTimeString($hours[0]),
            $now->copy()->setTimeFromTimeString($hours[1])
        );
    }

    /** Returns the academic year string (e.g. "2025-2026") for the given date. Academic year runs 1 Apr - 31 Mar. */
    public function academicYearFor(Carbon $date): string
    {
        if ($date->month >= 4) {
            return $date->year . '-' . ($date->year + 1);
        }

        return ($date->year - 1) . '-' . $date->year;
    }
}
