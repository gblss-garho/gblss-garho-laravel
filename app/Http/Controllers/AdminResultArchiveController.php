<?php

namespace App\Http\Controllers;

use App\Models\StudentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Year-end helper: download a CSV copy of one exam's marks, then (after typing RESET) clear them. */
class AdminResultArchiveController extends Controller
{
    public function download(Request $request)
    {
        $data = $request->validate(['year' => ['required', 'string', 'max:20'], 'exam' => ['required', 'string', 'max:60']]);
        $rows = StudentResult::with('student')->where('academic_year', $data['year'])->where('exam_type', $data['exam'])
            ->orderBy('class')->orderBy('student_id')->orderBy('subject')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['GR', 'Name', 'Class', 'Exam', 'Year', 'Subject', 'Score']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->student->gr_number ?? '', $r->student->name ?? '', $r->class, $r->exam_type, $r->academic_year, $r->subject, $r->score]);
            }
            fclose($out);
        }, 'marks-'.preg_replace('/[^A-Za-z0-9_-]+/', '_', $data['exam'].'-'.$data['year']).'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'year' => ['required', 'string', 'max:20'], 'exam' => ['required', 'string', 'max:60'],
            'confirm' => ['required', 'in:RESET'],
        ], ['confirm.in' => 'Reset ke liye box mein RESET likhein (capital letters).']);

        $n = DB::transaction(function () use ($data) {
            $count = StudentResult::where('academic_year', $data['year'])->where('exam_type', $data['exam'])->delete();
            DB::table('result_publish_status')->where('exam_type', $data['exam'])->delete();

            return $count;
        });

        return redirect()->route('admin.results.index', ['year' => $data['year'], 'exam' => $data['exam']])
            ->with('ok', "{$n} marks entries hata di gayin ({$data['exam']} {$data['year']}). Pehle download ki hui CSV mehfooz rakhein.");
    }
}
