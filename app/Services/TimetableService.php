<?php

namespace App\Services;

use App\Models\Student;
use App\Models\TimetableEntry;
use App\Models\TimetablePeriod;

/** Weekly timetable: Monday-Friday, 7 periods a day (Saturday and Sunday are off). */
class TimetableService
{
    public const DAYS = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
    public const PERIODS = 7;

    /** Classes in school order, then any other class that has students. */
    public static function classes(): array
    {
        $extra = Student::query()->whereNotNull('class')->where('class', '!=', '')
            ->distinct()->pluck('class')->map(fn ($c) => (string) $c)
            ->reject(fn ($c) => in_array($c, ResultSessionFix::ORDER, true))->sort()->values()->all();

        return array_merge(ResultSessionFix::ORDER, $extra);
    }

    public static function validClass(?string $class): ?string
    {
        return $class !== null && in_array($class, self::classes(), true) ? $class : null;
    }

    /** @return array<int,array{starts_at:?string,ends_at:?string}> keyed 1..7 */
    public function periods(): array
    {
        $rows = TimetablePeriod::all()->keyBy('period');
        $out = [];
        for ($p = 1; $p <= self::PERIODS; $p++) {
            $r = $rows->get($p);
            $out[$p] = ['starts_at' => $r?->starts_at, 'ends_at' => $r?->ends_at];
        }

        return $out;
    }

    /** Class grid: [day][period] => entry|null */
    public function classGrid(string $class): array
    {
        $grid = $this->emptyGrid();
        foreach (TimetableEntry::with('teacher:id,name')->where('class_name', $class)->get() as $e) {
            if (array_key_exists((int) $e->period, $grid[(int) $e->day] ?? [])) {
                $grid[$e->day][$e->period] = $e;
            }
        }

        return $grid;
    }

    /** One teacher's week across all classes: [day][period] => entry|null */
    public function teacherGrid(string $teacherId): array
    {
        $grid = $this->emptyGrid();
        foreach (TimetableEntry::where('teacher_id', $teacherId)->orderBy('class_name')->get() as $e) {
            if (array_key_exists((int) $e->period, $grid[(int) $e->day] ?? [])) {
                $grid[$e->day][$e->period] = $e;
            }
        }

        return $grid;
    }

    /**
     * Teachers that would be in two classes at the same time.
     * $cells: [day][period] => teacher_id|null for $class.
     * @return string[] human readable clashes
     */
    public function clashes(string $class, array $cells, array $teacherNames): array
    {
        $others = TimetableEntry::where('class_name', '!=', $class)->whereNotNull('teacher_id')->get()
            ->groupBy(fn ($e) => $e->day.'-'.$e->period.'-'.$e->teacher_id);
        $out = [];
        foreach ($cells as $day => $row) {
            foreach ($row as $period => $tid) {
                if (! $tid) {
                    continue;
                }
                $hit = $others->get($day.'-'.$period.'-'.$tid);
                if ($hit) {
                    $out[] = ($teacherNames[$tid] ?? $tid).' pehle se Class '.$hit->first()->class_name.' mein hai ('
                        .self::DAYS[$day].', Period '.$period.')';
                }
            }
        }

        return $out;
    }

    private function emptyGrid(): array
    {
        $g = [];
        foreach (array_keys(self::DAYS) as $d) {
            for ($p = 1; $p <= self::PERIODS; $p++) {
                $g[$d][$p] = null;
            }
        }

        return $g;
    }
}
