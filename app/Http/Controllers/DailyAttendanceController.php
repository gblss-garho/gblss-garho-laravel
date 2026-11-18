<?php

namespace App\Http\Controllers;

use App\Models\AttendanceMark;
use App\Models\AttendanceRecord;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Hand-marked daily attendance (Present / Absent / Leave) for teachers (own classes) and admin. */
class DailyAttendanceController extends Controller
{
    private function isAdmin(Request $r): bool
    {
        return $r->user()->role === 'admin';
    }

    private function classes(Request $r): array
    {
        if ($this->isAdmin($r)) {
            return Student::where('passed_out', false)->distinct()->orderBy('class')->pluck('class')->filter()->values()->all();
        }

        return array_values($r->user()->teacher?->assigned_classes_list ?? []);
    }

    private function date(Request $r): string
    {
        $d = (string) $r->input('date', '');
        $d = preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : Carbon::now()->toDateString();

        return $d > Carbon::now()->toDateString() ? Carbon::now()->toDateString() : $d;
    }

    private function totals(string $date, string $class, $students): array
    {
        $record = AttendanceRecord::whereDate('date', $date)->where('class_name', $class)->first();
        $marks = $record ? AttendanceMark::where('attendance_record_id', $record->id)->pluck('status', 'student_id') : collect();
        $ids = $students->pluck('id');
        $c = ['P' => 0, 'A' => 0, 'L' => 0];
        foreach ($ids as $id) {
            if (isset($marks[$id])) {
                $c[$marks[$id]]++;
            }
        }
        $total = $ids->count();
        $marked = $c['P'] + $c['A'] + $c['L'];

        return [
            'marks' => $marks, 'total' => $total, 'present' => $c['P'], 'absent' => $c['A'], 'leave' => $c['L'],
            'unmarked' => $total - $marked, 'percent' => $total > 0 ? (int) round($c['P'] / $total * 100) : 0,
        ];
    }

    public function index(Request $request)
    {
        $classes = $this->classes($request);
        $class = (string) $request->query('class', $classes[0] ?? '');
        if ($class !== '' && ! in_array($class, $classes, true)) {
            $class = $classes[0] ?? '';
        }
        $date = $this->date($request);
        $students = $class === '' ? collect() : Student::where('passed_out', false)->where('class', $class)->orderByRaw('CAST(gr_number AS UNSIGNED)')->orderBy('name')->get();
        $isAdmin = $this->isAdmin($request);

        return view('attendance.daily', [
            'classes' => $classes, 'class' => $class, 'date' => $date, 'students' => $students,
            't' => $this->totals($date, $class, $students), 'isAdmin' => $isAdmin,
            'base' => route($isAdmin ? 'admin.daily-attendance' : 'teacher.daily-attendance', [], false),
            'saveUrl' => route($isAdmin ? 'admin.daily-attendance.mark' : 'teacher.daily-attendance.mark', [], false),
            'scanUrl' => $isAdmin ? null : route('teacher.qr.scan', [], false),
            'back' => route($isAdmin ? 'admin.dashboard' : 'teacher.dashboard', [], false),
        ]);
    }

    /** Set (or clear) one student's status, or everyone's present. */
    public function mark(Request $request): JsonResponse
    {
        $d = $request->validate([
            'class' => 'required|string', 'date' => 'required|date_format:Y-m-d',
            'student_id' => 'nullable|string', 'status' => 'required_without:marks|nullable|in:P,A,L,clear,all_present',
            'marks' => 'nullable|array', 'marks.*' => 'in:P,A,L,clear',
        ]);
        if (! in_array($d['class'], $this->classes($request), true)) {
            return response()->json(['message' => 'Yeh class aapki nahi hai.'], 403);
        }
        $date = $this->date(new Request(['date' => $d['date']]));
        if ($date !== $d['date']) {
            return response()->json(['message' => 'Aane wale din ki attendance nahi lag sakti.'], 422);
        }

        $record = $this->record($date, $d['class']);
        $students = Student::where('passed_out', false)->where('class', $d['class'])->get();

        if (! empty($d['marks'])) {
            // Save button: apply every staged change in one go.
            $valid = $students->pluck('id')->flip();
            foreach ($d['marks'] as $sid => $st) {
                if (! isset($valid[$sid])) {
                    continue;
                }
                $keys = ['attendance_record_id' => $record->id, 'student_id' => $sid];
                if ($st === 'clear') {
                    AttendanceMark::where($keys)->delete();
                } else {
                    AttendanceMark::updateOrCreate($keys, ['status' => $st, 'marked_via' => 'manual']);
                }
            }
        } elseif (($d['status'] ?? null) === 'all_present') {
            foreach ($students as $s) {
                $m = AttendanceMark::where(['attendance_record_id' => $record->id, 'student_id' => $s->id])->first();
                if (! $m) {
                    AttendanceMark::create(['attendance_record_id' => $record->id, 'student_id' => $s->id, 'status' => 'P', 'marked_via' => 'manual']);
                } elseif ($m->status === 'A') {
                    $m->update(['status' => 'P', 'marked_via' => 'manual']);
                }
            }
        } else {
            $student = $students->firstWhere('id', $d['student_id'] ?? null);
            if (! $student) {
                return response()->json(['message' => 'Student nahi mila.'], 404);
            }
            $keys = ['attendance_record_id' => $record->id, 'student_id' => $student->id];
            if ($d['status'] === 'clear') {
                AttendanceMark::where($keys)->delete();
            } else {
                AttendanceMark::updateOrCreate($keys, ['status' => $d['status'], 'marked_via' => 'manual']);
            }
        }

        return response()->json(['ok' => true] + $this->totals($date, $d['class'], $students));
    }

    private function record(string $date, string $class): AttendanceRecord
    {
        $find = fn () => AttendanceRecord::whereDate('date', $date)->where('class_name', $class)->first();

        if ($r = $find()) {
            return $r;
        }
        try {
            return AttendanceRecord::create(['id' => str_replace('-', '', (string) Str::uuid()), 'date' => $date, 'class_name' => $class]);
        } catch (QueryException $e) {
            return $find() ?? throw $e;
        }
    }
}
