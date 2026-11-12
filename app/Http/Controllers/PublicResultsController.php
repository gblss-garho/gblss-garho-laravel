<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicResultsController extends PublicLookupController
{
    public function show()
    {
        return view('pages.results', ['student' => null, 'exams' => collect(), 'error' => null]);
    }

    public function check(Request $request)
    {
        $error = null;
        $student = $this->lookupStudent($request, $error);

        return view('pages.results', [
            'student' => $student,
            'exams' => $student ? $this->publishedExams($student) : collect(),
            'error' => $error,
        ]);
    }
}
