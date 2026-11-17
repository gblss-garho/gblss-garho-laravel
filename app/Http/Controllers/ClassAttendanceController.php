<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\ClassAttendanceReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** Weekly / monthly student attendance record, for teachers (own classes) and admin (all classes). */
class ClassAttendanceController extends Controller
{
    public function index(Request $request, ClassAttendanceReport $report)
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';
        $teacher = $isAdmin ? null : $user->teacher;

        $classes = $isAdmin
            ? Student::where('passed_out', false)->distinct()->orderBy('class')->pluck('class')->all()
            : ($teacher ? array_values($teacher->assigned_classes_list) : []);

        $class = (string) $request->query('class', $classes[0] ?? '');
        $mode = $request->query('mode') === 'week' ? 'week' : 'month';
        $q = trim((string) $request->query('q', ''));

        $month = (string) $request->query('month', '');
        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) ? $month : Carbon::now()->format('Y-m');
        $anchor = (string) $request->query('week', '');
        $anchor = preg_match('/^\d{4}-\d{2}-\d{2}$/', $anchor) ? Carbon::parse($anchor) : Carbon::now();

        if ($mode === 'week') {
            $from = $anchor->copy()->startOfWeek(Carbon::MONDAY);
            $to = $from->copy()->addDays(6);
        } else {
            $from = Carbon::parse($month.'-01')->startOfMonth();
            $to = $from->copy()->endOfMonth();
        }

        $dayDate = (string) $request->query('date', '');
        $dayDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dayDate) ? $dayDate : null;
        $dayStatus = in_array($request->query('st'), ['present', 'absent', 'leave'], true) ? $request->query('st') : 'all';

        $data = null;
        if ($class !== '' && in_array($class, $classes, true)) {
            $data = $report->build($class, $from, $to, $mode, $q, $dayDate, $dayStatus);
        }

        return view('attendance.class-report', [
            'isAdmin' => $isAdmin, 'teacher' => $teacher, 'classes' => $classes, 'class' => $class,
            'mode' => $mode, 'month' => $month, 'from' => $from, 'to' => $to, 'q' => $q,
            'dayDate' => $dayDate, 'dayStatus' => $dayStatus, 'data' => $data,
            'base' => $isAdmin ? route('admin.class-attendance', [], false) : route('teacher.class-attendance', [], false),
            'back' => $isAdmin ? route('admin.dashboard', [], false) : route('teacher.dashboard', [], false),
            'prev' => $mode === 'week' ? $from->copy()->subWeek()->toDateString() : $from->copy()->subMonth()->format('Y-m'),
            'next' => $mode === 'week' ? $from->copy()->addWeek()->toDateString() : $from->copy()->addMonth()->format('Y-m'),
        ]);
    }
}
