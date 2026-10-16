<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminContentController extends Controller
{
    public function index()
    {
        $notices = DB::table('notices')->orderByDesc('date')->limit(50)->get();
        $events = DB::table('calendar_events')->orderByDesc('date')->limit(100)->get();

        return view('admin.content.index', compact('notices', 'events'));
    }

    public function addNotice(Request $request)
    {
        $d = $request->validate([
            'text' => ['required', 'string', 'max:2000'],
            'type' => ['nullable', 'string', 'max:30'],
            'date' => ['nullable', 'date'],
        ]);
        $now = now()->toDateTimeString();
        DB::table('notices')->insert([
            'id' => Str::random(8),
            'date' => ! empty($d['date']) ? $d['date'].' 00:00:00' : $now,
            'text' => $d['text'],
            'type' => $d['type'] ?: 'info',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return redirect()->route('admin.content.index')->with('ok', 'Notice add ho gaya.');
    }

    public function deleteNotice(string $id)
    {
        DB::table('notices')->where('id', $id)->delete();

        return redirect()->route('admin.content.index')->with('ok', 'Notice delete ho gaya.');
    }

    public function addEvent(Request $request)
    {
        $d = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'date' => ['required', 'date'],
            'type' => ['nullable', 'string', 'max:30'],
        ]);
        $now = now()->toDateTimeString();
        DB::table('calendar_events')->insert([
            'id' => Str::random(8),
            'date' => $d['date'],
            'title' => $d['title'],
            'type' => $d['type'] ?: 'event',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return redirect()->route('admin.content.index')->with('ok', 'Event add ho gaya.');
    }

    public function deleteEvent(string $id)
    {
        DB::table('calendar_events')->where('id', $id)->delete();

        return redirect()->route('admin.content.index')->with('ok', 'Event delete ho gaya.');
    }
}
