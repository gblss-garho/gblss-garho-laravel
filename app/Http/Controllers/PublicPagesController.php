<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicPagesController extends Controller
{
    public function gallery()
    {
        return view('pages.gallery', ['items' => DB::table('gallery')->orderByDesc('created_at')->get()]);
    }

    public function textbooks()
    {
        $items = DB::table('textbooks')->orderBy('class_name')->orderBy('subject')->get()->groupBy('class_name');
        return view('pages.textbooks', compact('items'));
    }

    public function feedbackForm()
    {
        return view('pages.feedback');
    }

    public function feedbackStore(Request $r)
    {
        $d = $r->validate([
            'name' => 'nullable|string|max:80',
            'rating' => 'nullable|integer|min:1|max:5',
            'message' => 'required|string|max:1000',
        ]);
        DB::table('feedback')->insert([
            'id' => Str::random(10), 'name' => $d['name'] ?: 'Anonymous', 'rating' => $d['rating'] ?? null,
            'message' => $d['message'], 'created_at' => now(), 'updated_at' => now(),
        ]);
        return redirect()->route('public.feedback')->with('ok', 'Shukriya! Aap ki feedback mil gayi.');
    }
}
