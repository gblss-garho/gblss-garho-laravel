<?php

namespace App\Services;

use App\Models\AttendanceMark;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** School-wide attendance for the admin: every class and every staff member over any week or month. */
class AttendanceOverview
{
    public function __construct(protected SchoolScheduleService $schedule, protected ClassAttendanceReport $classReport)
    {
    }

    /** One row per class with present / absent / leave totals for the period. */
    public function students(Carbon $from, Carbon $to, string $mode): array
    {
        $order = ResultSessionFix::ORDER;
        $classes = Student::where('passed_out', false)->distinct()->pluck('class')->filter()->values()->all();
        usort($classes, function ($a, $b) use ($order) {
            $ia = array_search($a, $order, true);
            $ib = array_search($b, $order, true);

            return [$ia === false ? 99 : $ia, $a] <=> [$ib === false ? 99 : $ib, $b];
        });

        $rows = [];
        $tot = ['strength' => 0, 'p' => 0, 'a' => 0, 'l' => 0];
        foreach ($classes as $class) {
            $d = $this->classReport->build($class, $from, $to, $mode);
            $p = array_sum(array_column($d['students'], 'p'));
            $a = array_sum(array_column($d['students'], 'a'));
            $l = array_sum(array_column($d['students'], 'l'));
            $rows[] = [
                'class' => $class, 'strength' => $d['summary']['total'], 'days' => $d['summary']['class_days'],
                'p' => $p, 'a' => $a, 'l' => $l, 'pct' => $this->pct($p, $a),
                'regular' => $d['summary']['regular'], 'irregular' => $d['summary']['irregular'],
            ];
            $tot['strength'] += $d['summary']['total'];
            $tot['p'] += $p;
            $tot['a'] += $a;
            $tot['l'] += $l;
        }
        $tot['pct'] = $this->pct($tot['p'], $tot['a']);

        return ['rows' => $rows, 'total' => $tot];
    }

    /**
     * Every staff member, day by day. Cell codes: P present, T late, V approved leave, A absent, - nothing to show.
     * Week = all 7 days (off days shown); month = school days only.
     */
    public function staff(Carbon $from, Carbon $to, Carbon $now, string $mode): array
    {
        $days = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $school = $this->schedule->isSchoolDay($d);
            if ($mode === 'week' || $school) {
                $days[] = ['date' => $d->copy(), 'key' => $d->toDateString(), 'school' => $school];
            }
        }

        $records = TeacherAttendance::whereDate('date', '>=', $from->toDateString())->whereDate('date', '<=', $to->toDateString())
            ->get()->groupBy('teacher_id')->map(fn ($g) => $g->keyBy(fn ($r) => $r->date->toDateString()));
        $leaves = DB::table('leave_requests')->where('status', 'approved')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get()->groupBy('teacher_id')->map(fn ($g) => $g->keyBy(fn ($l) => substr((string) $l->date, 0, 10)));

        $rows = [];
        $dayTot = [];
        foreach (Teacher::orderBy('name')->get() as $t) {
            $joined = $t->created_at ? Carbon::parse($t->created_at)->startOfDay() : null;
            $cells = [];
            $c = ['present' => 0, 'late' => 0, 'leave' => 0, 'absent' => 0, 'late_minutes' => 0];
            foreach ($days as $day) {
                $d = $day['date'];
                $r = $records->get($t->id)?->get($day['key']);
                $code = '-';
                $late = 0;
                if ($r && $r->check_in_at) {
                    $late = (int) $r->late_minutes > 0 ? (int) $r->late_minutes : $this->schedule->lateMinutes($r->check_in_at->copy());
                    $code = $late > 0 ? 'T' : 'P';
                    $c['present']++;
                    if ($late > 0) {
                        $c['late']++;
                        $c['late_minutes'] += $late;
                    }
                } elseif (! $day['school']) {
                    $code = 'O';
                } elseif ($leaves->get($t->id)?->has($day['key'])) {
                    $code = 'V';
                    $c['leave']++;
                } elseif ($d->gt($now->copy()->endOfDay()) || ($joined && $d->lt($joined))) {
                    $code = '-';
                } elseif ($d->isSameDay($now) && $now->lt($now->copy()->setTimeFromTimeString($this->schedule->timesFor($d)['out_start']))) {
                    $code = '-';
                } else {
                    $code = 'A';
                    $c['absent']++;
                }
                $cells[] = ['code' => $code, 'late' => $late];
                $dayTot[$day['key']][$code] = ($dayTot[$day['key']][$code] ?? 0) + 1;
            }
            $rows[] = ['teacher' => $t, 'cells' => $cells] + $c + ['pct' => $this->pct($c['present'], $c['absent'])];
        }

        $sum = ['present' => array_sum(array_column($rows, 'present')), 'late' => array_sum(array_column($rows, 'late')),
            'leave' => array_sum(array_column($rows, 'leave')), 'absent' => array_sum(array_column($rows, 'absent'))];
        $sum['pct'] = $this->pct($sum['present'], $sum['absent']);

        return ['days' => $days, 'rows' => $rows, 'sum' => $sum, 'dayTotals' => $dayTot, 'staff' => count($rows)];
    }

    /** Quick numbers for the dashboard card. Never throws: the dashboard must always open. */
    public function today(Carbon $now): ?array
    {
        try {
            $date = $now->toDateString();
            $records = AttendanceRecord::whereDate('date', $date)->get();
            $marks = $records->isEmpty() ? collect() : AttendanceMark::whereIn('attendance_record_id', $records->pluck('id'))->get(['status'])->countBy('status');
            $markedClasses = $records->pluck('class_name')->unique()->values();
            $strength = $markedClasses->isEmpty() ? 0 : Student::where('passed_out', false)->whereIn('class', $markedClasses)->count();
            $p = (int) $marks->get('P', 0);
            $l = (int) $marks->get('L', 0);
            $totalClasses = Student::where('passed_out', false)->distinct()->count('class');

            $staff = $this->staff($now->copy()->startOfDay(), $now->copy()->startOfDay(), $now, 'week');
            $codes = $staff['dayTotals'][$date] ?? [];

            return [
                'date' => $now,
                'school_day' => $this->schedule->isSchoolDay($now),
                'students' => ['present' => $p, 'leave' => $l, 'absent' => max(0, $strength - $p - $l), 'strength' => $strength,
                    'classes_marked' => $markedClasses->count(), 'classes_total' => $totalClasses],
                'staff' => ['present' => ($codes['P'] ?? 0) + ($codes['T'] ?? 0), 'late' => $codes['T'] ?? 0, 'leave' => $codes['V'] ?? 0,
                    'absent' => $codes['A'] ?? 0, 'total' => $staff['staff']],
            ];
        } catch (\Throwable $e) {
            \Log::warning('Attendance overview (today) failed: '.$e->getMessage());

            return null;
        }
    }

    private function pct(int $p, int $a): ?int
    {
        return ($p + $a) > 0 ? (int) round($p * 100 / ($p + $a)) : null;
    }
}
