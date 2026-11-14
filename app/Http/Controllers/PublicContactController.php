<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicContactController extends Controller
{
    public const REASONS = ['General inquiry', 'Admission question', 'Fee question', 'Complaint / concern', 'Other'];

    public function show()
    {
        return view('pages.contact', [
            'info' => DB::table('school_info')->where('id', 1)->first(),
            'reasons' => self::REASONS,
        ]);
    }

    public function store(Request $r)
    {
        // Honeypot: real people never fill the hidden "website" field.
        if ($r->filled('website')) {
            return redirect(route('public.contact', [], false))->with('ok', 'Thank you — your message has been sent. We will get back to you soon.');
        }

        $d = $r->validate([
            'name' => 'required|string|max:80',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:120',
            'subject' => 'required|in:'.implode(',', self::REASONS),
            'message' => 'required|string|max:2000',
        ]);

        DB::table('contact_messages')->insert([
            'id' => Str::random(10),
            'name' => trim($d['name']), 'phone' => trim($d['phone']), 'email' => $d['email'] ?? null,
            'subject' => $d['subject'], 'message' => trim($d['message']),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return redirect(route('public.contact', [], false))->with('ok', 'Thank you — your message has been sent. We will get back to you soon.');
    }

    public function calendar()
    {
        $events = DB::table('calendar_events')->orderBy('date')->get();
        $today = now('Asia/Karachi')->toDateString();

        return view('pages.calendar', [
            'upcoming' => $events->filter(fn ($e) => substr((string) $e->date, 0, 10) >= $today)->values(),
            'past' => $events->filter(fn ($e) => substr((string) $e->date, 0, 10) < $today)->reverse()->values(),
        ]);
    }
}
