<?php

namespace App\Http\Controllers;

use App\Services\AttendanceOverview;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** Admin: staff + student attendance for any week or month. */
class AttendanceOverviewController extends Controller
{
    public function index(Request $request, AttendanceOverview $overview)
    {
        $now = Carbon::now();
        $mode = $request->query('mode') === 'week' ? 'week' : 'month';
        $tab = $request->query('tab') === 'staff' ? 'staff' : 'students';

        $month = (string) $request->query('month', '');
        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) ? $month : $now->format('Y-m');
        $anchor = (string) $request->query('week', '');
        try {
            $anchor = preg_match('/^\d{4}-\d{2}-\d{2}$/', $anchor) ? Carbon::parse($anchor) : $now->copy();
        } catch (\Throwable $e) {
            $anchor = $now->copy();
        }

        if ($mode === 'week') {
            $from = $anchor->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $to = $from->copy()->addDays(6);
        } else {
            $from = Carbon::parse($month.'-01')->startOfMonth();
            $to = $from->copy()->endOfMonth();
        }

        return view('attendance.overview', [
            'mode' => $mode, 'tab' => $tab, 'month' => $month, 'from' => $from, 'to' => $to, 'now' => $now,
            'students' => $tab === 'students' ? $overview->students($from, $to, $mode) : null,
            'staff' => $tab === 'staff' ? $overview->staff($from, $to, $now, $mode) : null,
            'prev' => $mode === 'week' ? $from->copy()->subWeek()->toDateString() : $from->copy()->subMonth()->format('Y-m'),
            'next' => $mode === 'week' ? $from->copy()->addWeek()->toDateString() : $from->copy()->addMonth()->format('Y-m'),
            'base' => route('admin.attendance-overview', [], false),
        ]);
    }
}
