<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Staff side of the parents' leave applications (admin: all classes, teacher: own classes). */
class StudentLeaveController extends Controller
{
    private function norm(?string $c): string
    {
        return preg_replace('/^class|\s+/i', '', mb_strtolower((string) $c));
    }

    private function allowed(Request $r, ?string $class): bool
    {
        if ($r->user()->role === 'admin') {
            return true;
        }
        $mine = array_map(fn ($c) => $this->norm($c), $r->user()->teacher?->assigned_classes_list ?? []);

        return in_array($this->norm($class), $mine, true);
    }

    public function index(Request $request)
    {
        $isAdmin = $request->user()->role === 'admin';
        $status = $request->query('status', 'pending');
        $q = DB::table('student_leave_applications')->orderByRaw("case status when 'pending' then 0 else 1 end")->orderByDesc('leave_date')->limit(200);
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $q->where('status', $status);
        }
        $items = $q->get()->filter(fn ($i) => $this->allowed($request, $i->class_name))->values();

        return view('staff.student-leave', [
            'items' => $items, 'status' => $status, 'isAdmin' => $isAdmin,
            'p' => $isAdmin ? 'admin' : 'teacher', 'back' => route($isAdmin ? 'admin.dashboard' : 'teacher.dashboard', [], false),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $d = $request->validate(['status' => 'required|in:approved,rejected,pending']);
        $row = DB::table('student_leave_applications')->where('id', $id)->first();
        abort_unless($row && $this->allowed($request, $row->class_name), 404);
        DB::table('student_leave_applications')->where('id', $id)->update(['status' => $d['status'], 'updated_at' => now()]);

        // An approved leave counts as "Leave" if attendance was already taken for that day.
        if ($d['status'] === 'approved') {
            $rec = DB::table('attendance_records')->where('class_name', $row->class_name)->whereDate('date', $row->leave_date)->value('id');
            if ($rec) {
                \App\Models\AttendanceMark::updateOrCreate(['attendance_record_id' => $rec, 'student_id' => $row->student_id], ['status' => 'L', 'marked_via' => 'leave']);
            }
        }

        return back()->with('ok', 'Status badal gaya.');
    }

    public function file(Request $request, string $id)
    {
        $row = DB::table('student_leave_applications')->where('id', $id)->first();
        abort_unless($row && $row->attachment_path && $this->allowed($request, $row->class_name), 404);
        abort_unless(Storage::disk('local')->exists($row->attachment_path), 404);

        return Storage::disk('local')->download($row->attachment_path, $row->attachment_name ?: basename($row->attachment_path));
    }
}
