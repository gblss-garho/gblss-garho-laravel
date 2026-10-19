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
}
