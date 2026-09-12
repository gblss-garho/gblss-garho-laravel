<?php

namespace App\Http\Controllers;

use App\Models\TeacherAttendance;
use App\Services\SchoolScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherAttendanceController extends Controller
{
    public function __construct(protected SchoolScheduleService $schedule)
    {
    }

    /** Shows the check-in/check-out screen with current window status. */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;
        $now = Carbon::now();
        $today = $now->toDateString();

        $record = $teacher
            ? TeacherAttendance::where('teacher_id', $teacher->id)->where('date', $today)->first()
            : null;

        return view('teacher.attendance', [
            'teacher' => $teacher,
            'record' => $record,
            'isSchoolDay' => $this->schedule->isSchoolDay($now),
            'canCheckIn' => $this->schedule->isWithinCheckInWindow($now),
            'canCheckOut' => $this->schedule->isWithinCheckOutWindow($now),
        ]);
    }

    public function checkIn(Request $request)
    {
        $teacher = $request->user()->teacher;
        if (! $teacher) {
            return back()->withErrors(['attendance' => 'Aapke account se koi teacher record linked nahi hai.']);
        }

        $now = Carbon::now();

        if (! $this->schedule->isWithinCheckInWindow($now)) {
            return back()->withErrors(['attendance' => 'Check-in sirf 8:00–8:30 AM ke darmiyan school ke dinon mein ho sakta hai.']);
        }

        $record = TeacherAttendance::firstOrNew([
            'teacher_id' => $teacher->id,
            'date' => $now->toDateString(),
        ]);

        if ($record->exists && $record->check_in_at) {
            return back()->withErrors(['attendance' => 'Aap aaj already check-in kar chuke hain.']);
        }

        $record->check_in_at = $now;
        $record->check_in_method = $request->input('method', 'manual'); // Phase 4b sets this to face_recognition
        $record->save();

        return back()->with('status', 'Check-in ho gaya: ' . $now->format('h:i A'));
    }

    public function checkOut(Request $request)
    {
        $teacher = $request->user()->teacher;
        if (! $teacher) {
            return back()->withErrors(['attendance' => 'Aapke account se koi teacher record linked nahi hai.']);
        }

        $now = Carbon::now();

        if (! $this->schedule->isWithinCheckOutWindow($now)) {
            return back()->withErrors(['attendance' => 'Check-out sirf school khatam hone se pehle aakhri 15 minutes mein ho sakta hai.']);
        }

        $record = TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('date', $now->toDateString())
            ->first();

        if (! $record || ! $record->check_in_at) {
            return back()->withErrors(['attendance' => 'Aap ne aaj check-in nahi kiya, is liye check-out nahi ho sakta.']);
        }

        if ($record->check_out_at) {
            return back()->withErrors(['attendance' => 'Aap aaj already check-out kar chuke hain.']);
        }

        $record->check_out_at = $now;
        $record->check_out_method = $request->input('method', 'manual');
        $record->save();

        return back()->with('status', 'Check-out ho gaya: ' . $now->format('h:i A'));
    }
}
