<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminLeaveController extends Controller
{
    public function index()
    {
        $items = DB::table('leave_requests')->orderByRaw("case status when 'pending' then 0 else 1 end")->orderByDesc('date')->limit(100)->get();
        return view('admin.leave', compact('items'));
    }

    public function update(Request $r, string $id)
    {
        $d = $r->validate(['status' => 'required|in:approved,rejected,pending']);
        DB::table('leave_requests')->where('id', $id)->update(['status' => $d['status'], 'updated_at' => now()]);
        return redirect()->route('admin.leave.index')->with('ok', 'Status badal gaya.');
    }
}
