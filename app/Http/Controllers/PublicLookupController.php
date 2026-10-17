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

    private function parseDob(string $raw): ?string
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

    public function check(Request $request)
    {
        $data = $request->validate([
            'gr_number' => ['required', 'string', 'max:50'],
            'dob' => ['required', 'string', 'max:30'],
        ]);
        $gr = trim($data['gr_number']);
        $dob = $this->parseDob($data['dob']);
        $fail = fn (string $m) => view('pages.lookup', ['student' => null, 'exams' => [], 'error' => $m]);

        if (! $dob) {
            return $fail('Date of birth sahi format mein likhein (jaise 2013-05-21).');
        }

        $tries = DB::table('lookup_attempts')->where('scope', 'student')->where('identifier', $gr)
            ->where('created_at', '>', now()->subMinutes(15)->toDateTimeString())->count();
        if ($tries >= 8) {
            return $fail('Bohat zyada koshishein ho gayi hain. 15 minute baad dobara try karein.');
        }
        DB::table('lookup_attempts')->insert([
            'scope' => 'student', 'identifier' => $gr, 'created_at' => now()->toDateTimeString(),
        ]);

        $student = Student::where('gr_number', $gr)->whereDate('dob', $dob)->first();
        if (! $student) {
            return $fail('GR number ya date of birth match nahi hui.');
        }

        $published = DB::table('result_publish_status')
            ->where('class_name', $student->results_class)->where('status', 'published')->pluck('exam_type');
        $exams = StudentResult::where('student_id', $student->id)->whereIn('exam_type', $published)
            ->orderBy('subject')->get()->groupBy('exam_type');

        return view('pages.lookup', ['student' => $student, 'exams' => $exams, 'homework' => \App\Models\Homework::where('class_name', $student->class)->where('status', 'published')->orderByDesc('date')->limit(15)->get(), 'error' => null]);
    }
}
