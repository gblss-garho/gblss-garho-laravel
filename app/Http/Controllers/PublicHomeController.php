<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class PublicHomeController extends Controller
{
    public function index()
    {
        $classes = ['VI', 'VII', 'VIII'];
        $counts = DB::table('students')->where('passed_out', false)->whereIn('class', $classes)
            ->selectRaw('class, count(*) as c')->groupBy('class')->pluck('c', 'class');

        return view('pages.home', [
            'info' => DB::table('school_info')->where('id', 1)->first(),
            'students' => DB::table('students')->where('passed_out', false)->count(),
            'teachers' => DB::table('teachers')->count(),
            'classCounts' => collect($classes)->mapWithKeys(fn ($c) => [$c => (int) ($counts[$c] ?? 0)]),
            'notices' => DB::table('notices')->orderByDesc('date')->limit(4)->get(),
            'events' => DB::table('calendar_events')->whereDate('date', '>=', now()->toDateString())->orderBy('date')->limit(4)->get(),
            'photos' => DB::table('gallery')->orderByDesc('created_at')->limit(6)->get(),
        ]);
    }
}
