<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class PublicInfoController extends Controller
{
    public function about()
    {
        return view('pages.about', ['info' => DB::table('school_info')->where('id', 1)->first()]);
    }

    public function staff()
    {
        // Only public-safe columns: never cnic, dob, email, pin or face data.
        $teachers = DB::table('teachers')
            ->select('name', 'designation', 'subject', 'qualification', 'experience', 'photo_url', 'intro_line')
            ->orderBy('name')->get();

        return view('pages.staff', ['teachers' => $teachers, 'info' => DB::table('school_info')->where('id', 1)->first()]);
    }

    public function toppers()
    {
        $keys = ['english', 'math', 'gscience', 'sst', 'islamiat', 'sindhi', 'urdu', 'computer', 'drawing', 'arabic', 'agriculture'];
        $year = DB::table('student_results')->max('academic_year');

        $rows = DB::table('student_results as sr')
            ->join('students as s', 's.id', '=', 'sr.student_id')
            ->join('result_publish_status as p', function ($j) {
                $j->on('p.class_name', '=', 'sr.class')->on('p.exam_type', '=', 'sr.exam_type');
            })
            ->where('p.status', 'published')->where('sr.exam_type', 'annual')->where('sr.academic_year', $year)
            ->whereIn('sr.subject', $keys)->whereNotNull('sr.score')
            ->groupBy('sr.class', 's.id', 's.name', 's.photo_url')
            ->selectRaw('sr.class as cls, s.name, s.photo_url, sum(sr.score) as total, count(*) as subjects')
            ->get();

        $byClass = collect(['VI', 'VII', 'VIII'])->mapWithKeys(fn ($c) => [
            $c => $rows->where('cls', $c)->sortByDesc('total')->take(3)->values(),
        ]);

        return view('pages.toppers', [
            'byClass' => $byClass, 'year' => $year,
            'info' => DB::table('school_info')->where('id', 1)->first(),
        ]);
    }
}
