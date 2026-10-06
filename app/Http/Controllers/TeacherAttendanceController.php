<?php

namespace App\Http\Controllers;

use App\Models\TeacherAttendance;
use App\Services\FaceRecognitionService;
use App\Services\SchoolScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TeacherAttendanceController extends Controller
{
    public function __construct(
        protected SchoolScheduleService $schedule,
        protected FaceRecognitionService $face,
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

        return view('teacher.attendance', [
            'teacher' => $teacher,
            'record' => $record,
            'isSchoolDay' => $this->schedule->isSchoolDay($now),
            'canCheckIn' => $this->schedule->isWithinCheckInWindow($now),
            'canCheckOut' => $this->schedule->isWithinCheckOutWindow($now),
            'isFaceEnrolled' => $teacher && ! empty($teacher->face_descriptor),
        ]);
    }

    public function checkIn(Request $request)
    {
        $teacher = $request->user()->teacher;
        if (! $teacher) {
            return back()->withErrors(['attendance' => 'Aapke account se koi teacher record linked nahi hai.']);
        }

        if (empty($teacher->face_descriptor)) {
            return redirect()->route('teacher.face.enroll', [], false)
                ->withErrors(['attendance' => 'Pehle apna chehra enroll karen.']);
        }

        $now = Carbon::now();

        if (! $this->schedule->isWithinCheckInWindow($now)) {
            return back()->withErrors(['attendance' => 'Check-in sirf 8:00–8:30 AM ke darmiyan school ke dinon mein ho sakta hai.']);
        }

        try {
            $descriptor = FaceEnrollController::decodeDescriptorOrFail($request->input('descriptor_json'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        if (! $this->face->isMatch($teacher->face_descriptor, $descriptor)) {
            return back()->withErrors(['attendance' => 'Chehra match nahi hua. Dobara try karen ya achi roshni mein camera use karen.']);
        }

        $record = TeacherAttendance::whereDate('date', $now->toDateString())->where('teacher_id', $teacher->id)->first() ?? new TeacherAttendance(['teacher_id' => $teacher->id, 'date' => $now->toDateString()]);

        if ($record->exists && $record->check_in_at) {
            return back()->withErrors(['attendance' => 'Aap aaj already check-in kar chuke hain.']);
        }

        $record->check_in_at = $now;
        $record->check_in_method = 'face_recognition';
        $record->save();

        return back()->with('status', 'Check-in ho gaya: ' . $now->format('h:i A'));
    }

    public function checkOut(Request $request)
    {
        $teacher = $request->user()->teacher;
        if (! $teacher) {
            return back()->withErrors(['attendance' => 'Aapke account se koi teacher record linked nahi hai.']);
        }

        if (empty($teacher->face_descriptor)) {
            return redirect()->route('teacher.face.enroll', [], false)
                ->withErrors(['attendance' => 'Pehle apna chehra enroll karen.']);
        }

        $now = Carbon::now();

        if (! $this->schedule->isWithinCheckOutWindow($now)) {
            return back()->withErrors(['attendance' => 'Check-out sirf school khatam hone se pehle aakhri 15 minutes mein ho sakta hai.']);
        }

        try {
            $descriptor = FaceEnrollController::decodeDescriptorOrFail($request->input('descriptor_json'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        if (! $this->face->isMatch($teacher->face_descriptor, $descriptor)) {
            return back()->withErrors(['attendance' => 'Chehra match nahi hua. Dobara try karen ya achi roshni mein camera use karen.']);
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
}
