<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSecurityController extends Controller
{
    public function index(Request $r)
    {
        $hours = max(1, min(720, (int) $r->query('hours', 72)));
        $base = fn () => DB::table('teacher_liveness_failures as f')->leftJoin('teachers as t', 't.id', '=', 'f.teacher_id');

        return view('admin.security', [
            'hours' => $hours,
            'summary' => $base()->where('f.occurred_at', '>=', now()->subHours($hours))
                ->groupBy('f.teacher_id', 't.name', 'f.reason')
                ->selectRaw('t.name as teacher_name, f.reason, count(*) as cnt, max(f.occurred_at) as last_at')
                ->orderByDesc('cnt')->get(),
            'flags' => $base()->where('f.occurred_at', '>=', now()->subHours(24))
                ->groupBy('f.teacher_id', 't.name')->havingRaw('count(*) >= 3')
                ->selectRaw('t.name as teacher_name, count(*) as cnt, max(f.occurred_at) as last_at')
                ->orderByDesc('cnt')->get(),
            'recent' => $base()->orderByDesc('f.occurred_at')->limit(30)
                ->select('t.name as teacher_name', 'f.reason', 'f.failure_type', 'f.occurred_at')->get(),
        ]);
    }
}
