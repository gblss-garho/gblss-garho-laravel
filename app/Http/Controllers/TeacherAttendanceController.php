<?php

namespace App\Http\Controllers;

use App\Models\TeacherAttendance;
use App\Services\FaceRecognitionService;
use App\Services\SchoolScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherAttendanceController extends Controller
{
    public function __construct(
        protected SchoolScheduleService $schedule,
        protected FaceRecognitionService $face,
        protected \App\Services\LivenessService $liveness,
        protected \App\Services\GeoFenceService $geo,
    ) {
    }

    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;
        $now = Carbon::now();
        $today = $now->toDateString();

        $record = $teacher
            ? TeacherAttendance::where('teacher_id', $teacher->id)->whereDate('date', $today)->first()
            : null;

        $isSchoolDay = $this->schedule->isSchoolDay($now);
        $times = $this->schedule->timesFor($now);
        $canCheckIn = $this->schedule->isWithinCheckInPeriod($now);
        $canCheckOut = $this->schedule->isWithinCheckOutWindow($now);
        $hasIn = (bool) ($record && $record->check_in_at);
        $hasOut = (bool) ($record && $record->check_out_at);
        $isEnrolled = $teacher && ! empty($teacher->face_descriptor);

        // What should the "today" card do right now?
        if (! $isSchoolDay) {
            $mode = 'off';
        } elseif (! $isEnrolled) {
            $mode = 'enroll';
        } elseif ($hasOut) {
            $mode = 'done';
        } elseif (! $hasIn && $canCheckIn) {
            $mode = 'checkin';
        } elseif ($hasIn && $canCheckOut) {
            $mode = 'checkout';
        } elseif ($hasIn) {
            $mode = 'waiting_out';
        } elseif ($now->lt($now->copy()->setTimeFromTimeString($times['in_start']))) {
            $mode = 'early';
        } else {
            $mode = 'missed';
        }

        $late = $hasIn ? $this->lateOf($record, $now) : ($mode === 'checkin' ? $this->schedule->lateMinutes($now) : 0);

        $dayBar = null;
        if ($isSchoolDay) {
            $from = $now->copy()->setTimeFromTimeString($times['in_start']);
            $to = $now->copy()->setTimeFromTimeString($times['out_end']);
            $total = max(1, $from->diffInMinutes($to));
            $pct = fn (Carbon $t) => max(0, min(100, round($from->diffInMinutes($t, false) / $total * 100, 1)));
            $dayBar = [
                'now' => $pct($now),
                'ontime' => $pct($from->copy()->setTimeFromTimeString($times['in_end'])),
                'in' => $hasIn ? $pct($record->check_in_at) : null,
                'out' => $hasOut ? $pct($record->check_out_at) : null,
                'from' => $from->format('g:i A'),
                'to' => $to->format('g:i A'),
            ];
        }

        $weekStart = $this->weekStartFrom($request->query('week'), $now);

        return view('teacher.attendance', [
            'teacher' => $teacher,
            'record' => $record,
            'mode' => $mode,
            'late' => $late,
            'dayBar' => $dayBar,
            'isSchoolDay' => $isSchoolDay,
            'canCheckIn' => $canCheckIn,
            'canCheckOut' => $canCheckOut,
            'isFaceEnrolled' => $isEnrolled,
            'checkInLabel' => $this->schedule->checkInWindowLabel($now),
            'checkOutLabel' => $this->schedule->checkOutWindowLabel($now),
            'week' => $teacher ? $this->buildWeek($teacher, $weekStart, $now) : null,
            'geo' => in_array($mode, ['checkin', 'checkout'], true) ? $this->geo->config() : null,
            'challenges' => in_array($mode, ['checkin', 'checkout'], true) ? $this->liveness->issue($request) : [],
            'weekStart' => $weekStart,
            'prevWeek' => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeek' => $weekStart->copy()->addWeek()->toDateString(),
            'isCurrentWeek' => $weekStart->toDateString() === $now->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
        ]);
    }

    private function weekStartFrom(?string $raw, Carbon $now): Carbon
    {
        $current = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        if (! $raw || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return $current;
        }
        try {
            $d = Carbon::parse($raw)->startOfWeek(Carbon::MONDAY)->startOfDay();
        } catch (\Throwable $e) {
            return $current;
        }

        // never into the future, never absurdly far back
        if ($d->gt($current)) {
            return $current;
        }
        if ($d->lt($current->copy()->subWeeks(52))) {
            return $current->copy()->subWeeks(52);
        }

        return $d;
    }

    private function lateOf(TeacherAttendance $r, Carbon $fallbackDay): int
    {
        if ((int) $r->late_minutes > 0) {
            return (int) $r->late_minutes;
        }

        // rows saved before the late_minutes column existed
        return $r->check_in_at ? $this->schedule->lateMinutes($r->check_in_at->copy()) : 0;
    }

    /** Monday..Sunday rows with a status for each day, plus totals. */
    private function buildWeek($teacher, Carbon $start, Carbon $now): array
    {
        $end = $start->copy()->addDays(6);
        $records = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->get()->keyBy(fn ($r) => $r->date->toDateString());

        $leaves = DB::table('leave_requests')
            ->where('teacher_id', $teacher->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()->groupBy(fn ($l) => substr((string) $l->date, 0, 10));

        $joined = $teacher->created_at ? Carbon::parse($teacher->created_at)->startOfDay() : null;
        $days = [];
        $sum = ['present' => 0, 'late' => 0, 'leave' => 0, 'absent' => 0, 'late_minutes' => 0];

        for ($i = 0; $i < 7; $i++) {
            $d = $start->copy()->addDays($i);
            $key = $d->toDateString();
            $row = [
                'date' => $d, 'status' => 'off', 'label' => 'Off', 'in' => null, 'out' => null,
                'late' => 0, 'hours' => null, 'note' => null, 'bar' => null,
            ];
            $r = $records->get($key);
            $dayLeaves = $leaves->get($key);
            $times = $this->schedule->timesFor($d);
            $isToday = $d->isSameDay($now);

            if ($r && $r->check_in_at) {
                $row['in'] = $r->check_in_at->format('h:i A');
                $row['out'] = $r->check_out_at ? $r->check_out_at->format('h:i A') : null;
                $row['late'] = $this->lateOf($r, $d);
                if ($r->check_out_at) {
                    $mins = $r->check_in_at->diffInMinutes($r->check_out_at);
                    $row['hours'] = intdiv($mins, 60) . 'h ' . ($mins % 60) . 'm';
                } elseif (! $isToday) {
                    $row['note'] = 'Check-out nahi hua';
                }
                $row['bar'] = $this->bar($r, $d, $times);
                if ($row['late'] > 0) {
                    $row['status'] = 'late'; $row['label'] = 'Late';
                    $sum['late']++; $sum['late_minutes'] += $row['late'];
                } else {
                    $row['status'] = 'present'; $row['label'] = 'Present';
                }
                $sum['present']++;
            } elseif (! $this->schedule->isSchoolDay($d)) {
                // stays "off"
            } elseif ($dayLeaves && $dayLeaves->contains('status', 'approved')) {
                $row['status'] = 'leave'; $row['label'] = 'Leave';
                $row['note'] = \Illuminate\Support\Str::limit((string) $dayLeaves->firstWhere('status', 'approved')->reason, 40);
                $sum['leave']++;
            } elseif ($d->gt($now->copy()->endOfDay())) {
                $row['status'] = 'upcoming'; $row['label'] = 'Aane wala';
            } elseif ($joined && $d->lt($joined)) {
                $row['status'] = 'na'; $row['label'] = '—';
            } elseif ($isToday && $now->lt($now->copy()->setTimeFromTimeString($times['out_start']))) {
                $row['status'] = 'pending'; $row['label'] = 'Abhi baqi';
            } else {
                $row['status'] = 'absent'; $row['label'] = 'Absent';
                if ($dayLeaves && $dayLeaves->contains('status', 'pending')) {
                    $row['note'] = 'Chhutti ki darkhwast pending';
                }
                $sum['absent']++;
            }

            $days[] = $row;
        }

        return ['days' => $days, 'sum' => $sum];
    }

    /** Where the present span sits inside the school day, as percentages (for the little timeline bar). */
    private function bar(TeacherAttendance $r, Carbon $d, array $times): ?array
    {
        $from = $d->copy()->setTimeFromTimeString($times['in_start']);
        $to = $d->copy()->setTimeFromTimeString($times['out_end']);
        $total = max(1, $from->diffInMinutes($to));
        $in = max(0, min($total, $from->diffInMinutes($r->check_in_at, false)));
        $outAt = $r->check_out_at ?: null;
        $left = round($in / $total * 100, 1);
        $width = $outAt ? max(2, round((max(0, min($total, $from->diffInMinutes($outAt, false))) - $in) / $total * 100, 1)) : 2;

        return ['left' => $left, 'width' => min($width, 100 - $left)];
    }

    public function checkIn(Request $request)
    {
        $teacher = $request->user()->teacher;
        if (! $teacher) {
            return back()->withErrors(['attendance' => 'Aapke account se koi teacher record linked nahi hai.']);
        }

        if (empty($teacher->face_descriptor)) {
            return redirect(route('teacher.face.enroll', [], false))
                ->withErrors(['attendance' => 'Pehle apna chehra enroll karen.']);
        }

        $now = Carbon::now();

        if (! $this->schedule->isWithinCheckInPeriod($now)) {
            return back()->withErrors(['attendance' => 'Check-in ka waqt khatam ho chuka hai ya aaj school off hai. Details ke liye Principal/Admin se rabta karen.']);
        }

        if ($geoErr = $this->geo->check($request)) {
            return back()->withErrors(['geo' => $geoErr]);
        }

        if ($err = $this->liveness->verify($request, $teacher, 'check_in')) {
            return back()->withErrors(['attendance' => $err]);
        }

        try {
            $descriptor = FaceEnrollController::decodeDescriptorOrFail($request->input('descriptor_json'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        if (! $this->face->isMatch($teacher->face_descriptor, $descriptor)) {
            $this->logFace($teacher, 'check_in'); return back()->withErrors(['attendance' => 'Chehra match nahi hua. Dobara try karen ya achi roshni mein camera use karen.']);
        }

        $record = TeacherAttendance::whereDate('date', $now->toDateString())->where('teacher_id', $teacher->id)->first() ?? new TeacherAttendance(['teacher_id' => $teacher->id, 'date' => $now->toDateString()]);

        if ($record->exists && $record->check_in_at) {
            return back()->withErrors(['attendance' => 'Aap aaj already check-in kar chuke hain.']);
        }

        $record->check_in_at = $now;
        $record->check_in_method = 'face_recognition';
        $record->late_minutes = $this->schedule->lateMinutes($now);
        $record->save();

        return back()->with('status', 'Check-in ho gaya: ' . $now->format('h:i A') . ($record->late_minutes > 0 ? ' (Late ' . $record->late_minutes . ' min)' : ' - waqt par'));
    }

    public function checkOut(Request $request)
    {
        $teacher = $request->user()->teacher;
        if (! $teacher) {
            return back()->withErrors(['attendance' => 'Aapke account se koi teacher record linked nahi hai.']);
        }

        if (empty($teacher->face_descriptor)) {
            return redirect(route('teacher.face.enroll', [], false))
                ->withErrors(['attendance' => 'Pehle apna chehra enroll karen.']);
        }

        $now = Carbon::now();

        if (! $this->schedule->isWithinCheckOutWindow($now)) {
            return back()->withErrors(['attendance' => 'Check-out sirf school khatam hone se pehle aakhri 15 minutes mein ho sakta hai.']);
        }

        if ($geoErr = $this->geo->check($request)) {
            return back()->withErrors(['geo' => $geoErr]);
        }

        if ($err = $this->liveness->verify($request, $teacher, 'check_out')) {
            return back()->withErrors(['attendance' => $err]);
        }

        try {
            $descriptor = FaceEnrollController::decodeDescriptorOrFail($request->input('descriptor_json'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        if (! $this->face->isMatch($teacher->face_descriptor, $descriptor)) {
            $this->logFace($teacher, 'check_out'); return back()->withErrors(['attendance' => 'Chehra match nahi hua. Dobara try karen ya achi roshni mein camera use karen.']);
        }

        $record = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        if (! $record || ! $record->check_in_at) {
            return back()->withErrors(['attendance' => 'Aap ne aaj check-in nahi kiya, is liye check-out nahi ho sakta.']);
        }

        if ($record->check_out_at) {
            return back()->withErrors(['attendance' => 'Aap aaj already check-out kar chuke hain.']);
        }

        $record->check_out_at = $now;
        $record->check_out_method = 'face_recognition';
        $record->save();

        return back()->with('status', 'Check-out ho gaya: ' . $now->format('h:i A'));
    }

    private function logFace($teacher, string $type): void
    {
        \Illuminate\Support\Facades\DB::table('teacher_liveness_failures')->insert([
            'teacher_id' => (string) $teacher->id,
            'reason' => 'face_mismatch',
            'failure_type' => $type,
            'occurred_at' => now(),
        ]);
    }
}
