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
            ])->sortBy(fn ($r) => (int) ($r['student']->gr_number ?? 0))->values();
        }

        // Subject averages for the selected class (numeric scores only).
        $subjectAvg = collect();
        if ($class !== '' && $rows->isNotEmpty()) {
            $subjectAvg = $subjects->mapWithKeys(function ($sub) use ($rows) {
                $vals = $rows->map(fn ($r) => $r['scores'][$sub] ?? null)->filter(fn ($v) => is_numeric($v))->map(fn ($v) => (float) $v);

                return [$sub => ['avg' => $vals->count() ? round($vals->avg(), 1) : 0, 'max' => $vals->max() ?? 0, 'n' => $vals->count()]];
            });
        }

        // Class-by-class summary for the chosen year + exam.
        $summary = StudentResult::where('academic_year', $year)->where('exam_type', $exam)->get()
            ->groupBy('class')->map(function ($g, $cls) {
                $totals = $g->groupBy('student_id')->map(fn ($x) => $x->filter(fn ($r) => is_numeric($r->score))->sum('score'));

                return [
                    'class' => $cls, 'students' => $totals->count(),
                    'avg_total' => $totals->count() ? round($totals->avg(), 1) : 0,
                    'top_total' => $totals->max() ?? 0,
                    'avg_subject' => ($n = $g->filter(fn ($r) => is_numeric($r->score))->count()) ? round($g->filter(fn ($r) => is_numeric($r->score))->avg('score'), 1) : 0,
                ];
            })->sortKeys()->values();

        // Rank by total (ties share a rank).
        $ranked = $rows->sortByDesc('total')->values();
        $rank = [];
        $prev = null;
        $pos = 0;
        foreach ($ranked as $i => $r) {
            if ($r['total'] !== $prev) {
                $pos = $i + 1;
                $prev = $r['total'];
            }
            $rank[$r['student']->id ?? $i] = $pos;
        }
        $tab = in_array($request->query('tab'), ['marks', 'subjects', 'classes'], true) ? $request->query('tab') : 'marks';

        return view('admin.results.index', compact('years', 'exams', 'classes', 'year', 'exam', 'class', 'rows', 'subjects', 'subjectAvg', 'summary', 'rank', 'tab'));
    }
}
