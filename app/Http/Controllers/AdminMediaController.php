<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminMediaController extends Controller
{
    public function index()
    {
        return view('admin.media', [
            'gallery' => DB::table('gallery')->orderByDesc('created_at')->get(),
            'textbooks' => DB::table('textbooks')->orderBy('class_name')->orderBy('subject')->get(),
            'feedback' => DB::table('feedback')->orderByDesc('created_at')->limit(100)->get(),
        ]);
    }

    public function galleryAdd(Request $r)
    {
        $d = $r->validate([
            'caption' => 'nullable|string|max:200',
            'image' => 'nullable|image|max:5120',
            'url' => 'nullable|url|max:1000',
        ]);
        if ($r->hasFile('image')) {
            $f = $r->file('image');
            $path = $f->storeAs('gallery', Str::random(10) . '.' . $f->getClientOriginalExtension(), 'public');
            $url = '/storage/' . $path;
        } elseif (! empty($d['url'])) {
            $url = $d['url'];
        } else {
            return back()->withErrors(['image' => 'Photo ya link dein.']);
        }
        DB::table('gallery')->insert([
            'id' => Str::random(10), 'url' => $url, 'caption' => $d['caption'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return redirect()->route('admin.media.index')->with('ok', 'Photo add ho gayi.');
    }

    public function galleryDelete(string $id)
    {
        $g = DB::table('gallery')->where('id', $id)->first();
        if ($g && str_starts_with($g->url, '/storage/')) {
            Storage::disk('public')->delete(substr($g->url, 9));
        }
        DB::table('gallery')->where('id', $id)->delete();
        return redirect()->route('admin.media.index')->with('ok', 'Photo hata di gayi.');
    }

    public function textbookAdd(Request $r)
    {
        $d = $r->validate([
            'class_name' => 'required|string|max:20', 'subject' => 'required|string|max:100',
            'book_name' => 'required|string|max:200', 'book_url' => 'required|url|max:1000',
        ]);
        DB::table('textbooks')->insert($d + ['id' => Str::random(10), 'created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('admin.media.index')->with('ok', 'Kitaab add ho gayi.');
    }

    public function textbookDelete(string $id)
    {
        DB::table('textbooks')->where('id', $id)->delete();
        return redirect()->route('admin.media.index')->with('ok', 'Kitaab hata di gayi.');
    }

    public function feedbackDelete(string $id)
    {
        DB::table('feedback')->where('id', $id)->delete();
        return redirect()->route('admin.media.index')->with('ok', 'Feedback hata diya gaya.');
    }
}
