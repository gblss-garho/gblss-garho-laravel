<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminResultHistoryController extends Controller
{
    public function index(Request $r)
    {
        $gr = trim((string) $r->query('gr', ''));
        $student = null;
        $history = collect();
        $notFound = false;

        if ($gr !== '') {
            $student = Student::where('gr_number', $gr)->first();
            if (! $student) {
                $notFound = true;
            } else {
                $history = DB::table('student_results')->where('student_id', $student->id)
                    ->orderByDesc('academic_year')->orderBy('class')->orderBy('exam_type')->orderBy('subject')
                    ->get()->groupBy(['academic_year', 'class', 'exam_type']);
            }
        }

        return view('admin.results-history', compact('gr', 'student', 'history', 'notFound'));
    }
}
