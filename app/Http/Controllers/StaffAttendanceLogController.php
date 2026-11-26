<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Services\GeoFenceService;
use App\Services\SchoolScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Admin: the exact check-in / check-out time of every teacher, where the phone was, and refused attempts. */
class StaffAttendanceLogController extends Controller
{
    private const MAX_DAYS = 62;

    public function __construct(protected SchoolScheduleService $schedule, protected GeoFenceService $geo)
    {
    }

    public function index(Request $request)
    {
        $now = Carbon::now();
        $from = $this->day($request->query('from'), $now)->startOfDay();
        $to = $this->day($request->query('to'), $from)->startOfDay();
        if ($to->lt($from)) {
            $to = $from->copy();
        }
        if ($from->diffInDays($to) > self::MAX_DAYS) {
            $from = $to->copy()->subDays(self::MAX_DAYS);
        }
        $teacherId = (string) $request->query('teacher', '');
        $single = $from->isSameDay($to) && $teacherId === '';
        $radius = (int) ($this->geo->config()['radius'] ?? 200);

        $teachers = Teacher::orderBy('name')->get(['id', 'name', 'designation']);
        $recs = TeacherAttendance::whereDate('date', '>=', $from->toDateString())->whereDate('date', '<=', $to->toDateString())
            ->when($teacherId !== '', fn ($q) => $q->where('teacher_id', $teacherId))
            ->get();
        $byKey = $recs->keyBy(fn ($r) => $r->teacher_id.'|'.$r->date->toDateString());

        $rows = [];
        if ($single) {
            // every teacher for that day, including the ones with no record
            $leaves = DB::table('leave_requests')->where('status', 'approved')
                ->whereDate('date', $from->toDateString())->pluck('teacher_id')->all();
            foreach ($teachers as $t) {
                $r = $byKey->get($t->id.'|'.$from->toDateString());
                $rows[] = $this->row($t, $from, $r, $radius, $now, in_array($t->id, $leaves, true));
            }
        } else {
            $names = $teachers->keyBy('id');
            foreach ($recs->sortByDesc(fn ($r) => $r->date->toDateString().'|'.($r->check_in_at?->format('H:i:s'))) as $r) {
                if ($t = $names->get($r->teacher_id)) {
                    $rows[] = $this->row($t, $r->date, $r, $radius, $now, false);
                }
            }
        }

        $attempts = DB::table('teacher_liveness_failures')
            ->whereBetween('occurred_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($teacherId !== '', fn ($q) => $q->where('teacher_id', $teacherId))
            ->orderByDesc('occurred_at')->limit(200)->get();
        $names = $teachers->pluck('name', 'id');

        return view('admin.staff-attendance', [
            'from' => $from, 'to' => $to, 'single' => $single, 'teacherId' => $teacherId, 'teachers' => $teachers,
            'rows' => $rows, 'radius' => $radius, 'geoSet' => $this->geo->config() !== null,
            'attempts' => $attempts->map(fn ($a) => [
                'at' => Carbon::parse($a->occurred_at),
                'name' => $names[$a->teacher_id] ?? $a->teacher_id,
                'what' => ($a->failure_type === 'check_out' ? 'Time Out' : ($a->failure_type === 'check_in' ? 'Time In' : 'Face')).': '.self::reasonLabel($a->reason),
            ]),
        ]);
    }

    private function day(?string $raw, Carbon $fallback): Carbon
    {
        if ($raw && preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            try {
                return Carbon::parse($raw);
            } catch (\Throwable $e) {
            }
        }

        return $fallback->copy();
    }

    private function row($t, Carbon $date, ?TeacherAttendance $r, int $radius, Carbon $now, bool $onLeave): array
    {
        $row = [
            'teacher' => $t, 'date' => $date, 'in' => null, 'out' => null, 'hours' => null, 'late' => 0,
            'in_dist' => null, 'out_dist' => null, 'in_acc' => null, 'status' => 'absent', 'flags' => [],
        ];
        if ($r && $r->check_in_at) {
            $row['in'] = $r->check_in_at->format('h:i A');
            $row['out'] = $r->check_out_at?->format('h:i A');
            $row['late'] = (int) $r->late_minutes;
            $row['in_dist'] = $r->check_in_dist;
            $row['out_dist'] = $r->check_out_dist;
            $row['in_acc'] = $r->check_in_acc;
            $row['status'] = $row['late'] > 0 ? 'late' : 'present';
            if ($r->check_out_at) {
                $m = $r->check_in_at->diffInMinutes($r->check_out_at);
                $row['hours'] = intdiv($m, 60).'h '.($m % 60).'m';
            } elseif (! $date->isSameDay($now)) {
                $row['flags'][] = 'Time Out nahi hua';
            }
            if ($r->check_in_dist === null) {
                $row['flags'][] = 'Location record nahi (purani hazri)';
            } elseif ($radius > 0 && $r->check_in_dist > $radius * 0.8) {
                $row['flags'][] = 'School ki hadd ke bilkul qareeb se';
            }
            if ($r->check_out_at && $r->check_in_ip && $r->check_out_ip && $r->check_in_ip !== $r->check_out_ip) {
                $row['flags'][] = 'In/Out ka IP alag';
            }
            if ($r->check_out_at && $r->check_in_device && $r->check_out_device && $r->check_in_device !== $r->check_out_device) {
                $row['flags'][] = 'In/Out ka phone/browser alag';
            }
        } elseif (! $this->schedule->isSchoolDay($date)) {
            $row['status'] = 'off';
        } elseif ($onLeave) {
            $row['status'] = 'leave';
        } elseif ($date->isSameDay($now) && $now->lt($now->copy()->setTimeFromTimeString($this->schedule->timesFor($date)['out_start']))) {
            $row['status'] = 'pending';
        }

        return $row;
    }

    public static function reasonLabel(string $reason): string
    {
        return match (true) {
            str_starts_with($reason, 'geo_outside_') => 'School se bahar se koshish ('.preg_replace('/\D/', '', $reason).' m door)',
            $reason === 'geo_missing' => 'Phone ki location nahi mili',
            $reason === 'geo_weak_gps' => 'GPS kamzor / ghalat',
            $reason === 'geo_school_not_set' => 'School ki location Settings mein set nahi',
            $reason === 'face_mismatch' => 'Chehra match nahi hua',
            str_starts_with($reason, 'liveness') => 'Liveness (palak/sir) pass nahi hua',
            default => $reason,
        };
    }
}
