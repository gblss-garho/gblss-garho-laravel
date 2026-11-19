<?php

namespace App\Services;

use App\Models\AttendanceMark;
use App\Models\AttendanceRecord;
use App\Models\Student;
use Carbon\Carbon;

/** Builds per-student attendance for a class over a week or a month. */
class ClassAttendanceReport
{
    public const REGULAR_AT = 85;
    public const AVERAGE_AT = 70;

    /**
     * @return array{days: array, students: array, summary: array}
     */
    public function build(string $class, Carbon $from, Carbon $to, string $mode, string $q = '', ?string $dayDate = null, ?string $dayStatus = null): array
    {
        $records = AttendanceRecord::where('class_name', $class)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->date)->toDateString());

        $marks = $records->isEmpty() ? collect() : AttendanceMark::whereIn('attendance_record_id', $records->pluck('id'))
            ->get(['attendance_record_id', 'student_id', 'status'])
            ->groupBy('student_id');
        $dateOfRecord = $records->mapWithKeys(fn ($r, $d) => [$r->id => $d]);

        // Columns: week = every calendar day; month = only days the class had attendance taken.
        $days = [];
        if ($mode === 'week') {
            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $days[] = ['date' => $d->toDateString(), 'label' => $d->format('D'), 'num' => $d->format('j'), 'taken' => $records->has($d->toDateString())];
            }
        } else {
            foreach ($records->keys()->sort() as $date) {
                $c = Carbon::parse($date);
                $days[] = ['date' => $date, 'label' => $c->format('D'), 'num' => $c->format('j'), 'taken' => true];
            }
        }
        $classDays = $records->count();

        $students = Student::where('class', $class)->where('passed_out', false)
            ->when($q !== '', fn ($x) => $x->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('gr_number', 'like', "%{$q}%")->orWhere('father_name', 'like', "%{$q}%");
            }))
            ->orderByRaw('CAST(gr_number AS UNSIGNED)')->orderBy('gr_number')->get();

        $out = [];
        $sumPct = 0;
        $withData = 0;
        foreach ($students as $s) {
            $byDate = [];
            foreach ($marks->get($s->id, collect()) as $m) {
                $d = $dateOfRecord->get($m->attendance_record_id);
                if ($d) {
                    $byDate[$d] = $m->status;
                }
            }
            $cells = [];
            $p = $a = $l = 0;
            foreach ($days as $day) {
                $st = null;
                if ($records->has($day['date'])) {
                    $raw = $byDate[$day['date']] ?? 'A';
                    $st = $raw === 'P' ? 'P' : ($raw === 'L' ? 'L' : 'A');
                    $st === 'P' ? $p++ : ($st === 'L' ? $l++ : $a++);
                }
                $cells[] = $st;
            }
            $counted = $p + $a;
            $pct = $counted > 0 ? (int) round($p * 100 / $counted) : null;
            $level = $pct === null ? 'none' : ($pct >= self::REGULAR_AT ? 'regular' : ($pct >= self::AVERAGE_AT ? 'average' : 'irregular'));

            $dayState = null;
            if ($dayDate) {
                $dayState = $records->has($dayDate) ? (($byDate[$dayDate] ?? 'A') === 'P' ? 'P' : (($byDate[$dayDate] ?? 'A') === 'L' ? 'L' : 'A')) : null;
                if ($dayStatus && $dayStatus !== 'all') {
                    $want = ['present' => 'P', 'absent' => 'A', 'leave' => 'L'][$dayStatus] ?? null;
                    if ($want && $dayState !== $want) {
                        continue;
                    }
                }
            }
            if ($pct !== null) {
                $sumPct += $pct;
                $withData++;
            }
            $out[] = ['student' => $s, 'cells' => $cells, 'p' => $p, 'a' => $a, 'l' => $l, 'pct' => $pct, 'level' => $level, 'dayState' => $dayState];
        }

        return [
            'days' => $days,
            'students' => $out,
            'summary' => [
                'class_days' => $classDays,
                'avg' => $withData ? (int) round($sumPct / $withData) : null,
                'regular' => count(array_filter($out, fn ($r) => $r['level'] === 'regular')),
                'average' => count(array_filter($out, fn ($r) => $r['level'] === 'average')),
                'irregular' => count(array_filter($out, fn ($r) => $r['level'] === 'irregular')),
                'total' => count($out),
            ],
        ];
    }
}
