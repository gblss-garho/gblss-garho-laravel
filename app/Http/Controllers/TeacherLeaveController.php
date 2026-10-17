<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherLeaveController extends Controller
{
    public function index()
    {
        $t = auth()->user()->teacher;
        $items = $t ? DB::table('leave_requests')->where('teacher_id', $t->id)->orderByDesc('date')->limit(30)->get() : collect();
        return view('teacher.leave', compact('items', 't'));
    }

    public function store(Request $r)
    {
        $t = auth()->user()->teacher;
        abort_unless($t, 403);
        $d = $r->validate(['date' => 'required|date|after_or_equal:today', 'reason' => 'required|string|max:1000']);
        DB::table('leave_requests')->insert([
            'id' => Str::random(12), 'teacher_id' => $t->id, 'teacher_name' => $t->name,
            'date' => $d['date'], 'reason' => $d['reason'], 'submitted_at' => now(),
            'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
        return redirect()->route('teacher.leave.index')->with('ok', 'Darkhwast bhej di gayi.');
    }
}
