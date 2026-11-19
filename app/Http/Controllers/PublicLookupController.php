<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicLookupController extends Controller
{
    public function show()
    {
        return view('pages.lookup', ['student' => null, 'exams' => [], 'error' => null]);
    }

    protected function parseDob(string $raw): ?string
    {
        $raw = trim($raw);
        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'M d, Y'] as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $raw);
                if ($d && $d->format($fmt) === $raw) {
                    return $d->toDateString();
                }
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    /** GR + date of birth check with the 15-minute attempt limit. Sets $error and returns null on failure. */
    protected function lookupStudent(Request $request, ?string &$error): ?Student
    {
        $data = $request->validate([
            'gr_number' => ['required', 'string', 'max:50'],
            'dob' => ['required', 'string', 'max:30'],
        ]);
        $gr = trim($data['gr_number']);
        $dob = $this->parseDob($data['dob']);

        if (! $dob) {
            $error = 'تاريخ پيدائش صحيح فارميٽ ۾ لکو (مثال 2013-05-21).';

            return null;
        }

        $tries = DB::table('lookup_attempts')->where('scope', 'student')->where('identifier', $gr)
            ->where('created_at', '>', now()->subMinutes(15)->toDateTimeString())->count();
        if ($tries >= 8) {
            $error = 'Bohat zyada koshishein ho gayi hain. 15 minute baad dobara try karein.';

            return null;
        }
        DB::table('lookup_attempts')->insert([
            'scope' => 'student', 'identifier' => $gr, 'created_at' => now()->toDateTimeString(),
        ]);

        $student = Student::where('gr_number', $gr)->whereDate('dob', $dob)->first();
        if (! $student) {
            $error = 'GR نمبر ۽ تاريخ پيدائش نه ملي.';
        }

        return $student;
    }

    /** Published results of a student, grouped by exam type. */
    protected function publishedExams(Student $student)
    {
        $published = DB::table('result_publish_status')
            ->where('class_name', $student->results_class)->where('status', 'published')->pluck('exam_type');

        return StudentResult::where('student_id', $student->id)->whereIn('exam_type', $published)
            ->orderBy('subject')->get()->groupBy('exam_type');
    }

    public function check(Request $request)
    {
        $error = null;
        $student = $this->lookupStudent($request, $error);
        if (! $student) {
            return view('pages.lookup', ['student' => null, 'exams' => [], 'error' => $error]);
        }

        return $this->renderStudent($student, $request->input('dob'));
    }

    /** The parent's page for one verified student. */
    protected function renderStudent(Student $student, ?string $dob, ?string $ok = null, ?string $leaveError = null)
    {
        $exams = $this->publishedExams($student);
        $homework = \App\Models\Homework::where('class_name', $student->class)->where('status', 'published')->orderByDesc('date')->limit(120)->get();

        return view('pages.lookup', [
            'student' => $student, 'exams' => $exams, 'homework' => $homework, 'error' => null,
            'dob' => $dob, 'ok' => $ok, 'leaveError' => $leaveError,
            'att' => $this->attendanceSummary($student), 'progress' => $this->progress($exams),
            'leaves' => DB::table('student_leave_applications')->where('student_id', $student->id)->orderByDesc('leave_date')->limit(10)->get(),
        ] + $this->extras($student));
    }

    /** Days present (P or L) out of the days this class had attendance taken. */
    private function attendanceSummary(Student $student): array
    {
        $days = DB::table('attendance_records')->where('class_name', $student->class)->count();
        $present = DB::table('attendance_marks')->join('attendance_records', 'attendance_records.id', '=', 'attendance_marks.attendance_record_id')
            ->where('attendance_marks.student_id', $student->id)->where('attendance_records.class_name', $student->class)
            ->whereIn('attendance_marks.status', ['P', 'L'])->count();

        return ['days' => $days, 'present' => $present, 'percent' => $days > 0 ? (int) round($present / $days * 100) : null];
    }

    /** Overall % over all published results (each subject out of 100). */
    private function progress($exams): array
    {
        $rows = collect($exams)->flatten(1);
        $max = $rows->filter(fn ($r) => $r->score !== null)->count() * 100;
        $got = (int) $rows->sum('score');

        return ['got' => $got, 'max' => $max, 'percent' => $max > 0 ? (int) round($got / $max * 100) : null];
    }

    protected function extras($student): array
    {
        $marks = DB::table('attendance_marks')
            ->join('attendance_records', 'attendance_records.id', '=', 'attendance_marks.attendance_record_id')
            ->where('attendance_marks.student_id', $student->id)
            ->where('attendance_records.date', 'like', now()->format('Y-m') . '%')
            ->selectRaw('attendance_marks.status as s, count(*) as c')
            ->groupBy('attendance_marks.status')->pluck('c', 's');
        $notices = DB::table('notices')->orderByDesc('date')->limit(5)->get();
        $events = DB::table('calendar_events')->whereDate('date', '>=', now()->toDateString())->orderBy('date')->limit(5)->get();

        return ['attMonth' => $marks, 'notices' => $notices, 'events' => $events];
    }
}
