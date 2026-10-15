<?php

namespace App\Http\Controllers;

use App\Models\StudentResult;
use Illuminate\Http\Request;

class AdminResultController extends Controller
{
    public function index(Request $request)
    {
        $years = StudentResult::distinct()->orderByDesc('academic_year')->pluck('academic_year')->filter()->values();
        $exams = StudentResult::distinct()->orderBy('exam_type')->pluck('exam_type')->filter()->values();
        $classes = StudentResult::distinct()->orderBy('class')->pluck('class')->filter()->values();

        $year = (string) $request->query('year', $years->first());
        $exam = (string) $request->query('exam', $exams->first());
        $class = (string) $request->query('class', '');

        $rows = collect();
        $subjects = collect();
        if ($class !== '') {
            $results = StudentResult::with('student')
                ->where('academic_year', $year)->where('exam_type', $exam)->where('class', $class)->get();
            $subjects = $results->pluck('subject')->unique()->sort()->values();
            $rows = $results->groupBy('student_id')->map(fn ($g) => [
                'student' => $g->first()->student,
                'scores' => $g->pluck('score', 'subject'),
                'total' => $g->sum('score'),
            ])->sortBy(fn ($r) => $r['student']->name ?? '')->values();
        }

        return view('admin.results.index', compact('years', 'exams', 'classes', 'year', 'exam', 'class', 'rows', 'subjects'));
    }
}
