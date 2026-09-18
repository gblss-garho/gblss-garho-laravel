<?php

namespace App\Http\Controllers;

use App\Models\AttendanceMark;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Services\SchoolScheduleService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeacherQrScanController extends Controller
{
    public function __construct(
        protected SchoolScheduleService $schedule,
    ) {
    }

    public function show(Request $request)
    {
        $teacher = $request->user()->teacher;

        return view('teacher.qr-scan', [
            'teacher' => $teacher,
            'canScan' => $teacher && $this->schedule->isWithinSchoolHours(Carbon::now()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacher;
        if (! $teacher) {
            return $this->error('Aapke account se koi teacher record linked nahi hai.', 403);
        }

        $now = Carbon::now();
        if (! $this->schedule->isWithinSchoolHours($now)) {
            return $this->error('QR attendance sirf school ke waqt mein ho sakti hai.', 422);
        }

        $code = strtoupper(trim((string) $request->input('qr_code')));
        if ($code === '' || strlen($code) > 32) {
            return $this->error('QR code sahi nahi hai.', 422);
        }

        $student = Student::where('qr_code', $code)->first();
        if (! $student) {
            return $this->error('Yeh QR code kisi student ka nahi hai.', 404);
        }
        if ($student->passed_out) {
            return $this->error($student->name.' passed out hai, attendance nahi lag sakti.', 422);
        }
        if (! in_array($student->class, $teacher->assigned_classes_list, true)) {
            return $this->error($student->name.' aapki class ka student nahi hai.', 403);
        }

        $record = $this->recordFor($now->toDateString(), $student->class);
        [$state, $message] = $this->markPresent($record, $student);

        return response()->json([
            'status' => $state,
            'message' => $message,
            'student' => [
                'name' => $student->name,
                'class' => $student->class,
                'section' => $student->section,
            ],
        ]);
    }

    /** Find or create the class's attendance record for the day. */
    private function recordFor(string $date, string $class): AttendanceRecord
    {
        $find = fn () => AttendanceRecord::whereDate('date', $date)
            ->where('class_name', $class)
            ->first();

        $record = $find();
        if ($record) {
            return $record;
        }

        try {
            return AttendanceRecord::create([
                'id' => str_replace('-', '', (string) Str::uuid()),
                'date' => $date,
                'class_name' => $class,
            ]);
        } catch (QueryException $e) {
            // Another scan created the same record at the same moment.
            return $find() ?? throw $e;
        }
    }

    /** @return array{0: string, 1: string} [state, message] */
    private function markPresent(AttendanceRecord $record, Student $student): array
    {
        $keys = ['attendance_record_id' => $record->id, 'student_id' => $student->id];
        $mark = AttendanceMark::where($keys)->first();

        if (! $mark) {
            try {
                AttendanceMark::create($keys + ['status' => 'P', 'marked_via' => 'qr_code']);

                return ['marked', $student->name.' ki attendance lag gayi.'];
            } catch (QueryException $e) {
                // A concurrent scan inserted the same mark; re-read it below.
                $mark = AttendanceMark::where($keys)->first();
                if (! $mark) {
                    throw $e;
                }
            }
        }

        if ($mark->status === 'P') {
            return ['already_present', $student->name.' pehle se present hai.'];
        }
        if ($mark->status === 'L') {
            return ['on_leave', $student->name.' leave par hai, attendance change nahi hui.'];
        }

        $mark->update(['status' => 'P', 'marked_via' => 'qr_code']);

        return ['marked', $student->name.' ki attendance lag gayi.'];
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message], $status);
    }
}
