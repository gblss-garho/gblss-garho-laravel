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
        $start = $this->setting('time_in_window_start', '08:00');
        $end = $this->isFriday($date)
            ? $this->setting('friday_time_out_window_end', '12:30')
            : $this->setting('time_out_window_end', '13:30');

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
        $start = $now->copy()->setTimeFromTimeString($this->setting('time_in_window_start', '08:00'));
        $end = $now->copy()->setTimeFromTimeString($this->setting('time_in_window_end', '08:30'));

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
    public function checkInWindowLabel(): string
    {
        return $this->formatTime($this->setting('time_in_window_start', '08:00'))
            . ' – ' . $this->formatTime($this->setting('time_in_window_end', '08:30'));
    }

    /** Human-readable check-out window for the given day (Friday differs). */
    public function checkOutWindowLabel(Carbon $now): string
    {
        if ($this->isFriday($now)) {
            return $this->formatTime($this->setting('friday_time_out_window_start', '12:15'))
                . ' – ' . $this->formatTime($this->setting('friday_time_out_window_end', '12:30'));
        }

        return $this->formatTime($this->setting('time_out_window_start', '13:15'))
            . ' – ' . $this->formatTime($this->setting('time_out_window_end', '13:30'));
    }

    /** Teacher check-out window (Friday has its own window). */
    public function isWithinCheckOutWindow(Carbon $now): bool
    {
        if (! $this->isSchoolDay($now)) {
            return false;
        }
        if ($this->isFriday($now)) {
            $s = $this->setting('friday_time_out_window_start', '12:15');
            $e = $this->setting('friday_time_out_window_end', '12:30');
        } else {
            $s = $this->setting('time_out_window_start', '13:15');
            $e = $this->setting('time_out_window_end', '13:30');
        }

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
