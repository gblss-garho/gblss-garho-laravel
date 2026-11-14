<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Old-site "E-Library" (FunTime): stories, poems, books, videos, songs. Public read, staff manage. */
class ElibraryController extends Controller
{
    public const TYPES = [
        'story' => ['Story', '📖', 'Read Now'],
        'poem' => ['Poem', '⭐', 'Read Now'],
        'book' => ['Book', '📚', 'Read Now'],
        'video' => ['Video', '🎬', 'Watch Now'],
        'song' => ['Song', '🎵', 'Listen Now'],
    ];

    public function index(Request $request)
    {
        $type = $request->query('type');
        $type = array_key_exists((string) $type, self::TYPES) ? $type : null;
        $counts = DB::table('funtime_items')->select('type', DB::raw('count(*) c'))->groupBy('type')->pluck('c', 'type');
        $items = $type ? DB::table('funtime_items')->where('type', $type)->orderByDesc('created_at')->get() : collect();
        $edit = null;
        if (auth()->check() && $request->query('edit')) {
            $edit = DB::table('funtime_items')->where('id', $request->query('edit'))->first();
        }

        return view('pages.elibrary', ['types' => self::TYPES, 'counts' => $counts, 'type' => $type, 'items' => $items, 'edit' => $edit]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => ['nullable', 'string', 'max:16'],
            'type' => ['required', 'in:'.implode(',', array_keys(self::TYPES))],
            'title' => ['required', 'string', 'max:200'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'media_url' => ['nullable', 'url', 'max:500'],
            'content' => ['nullable', 'string', 'max:20000'],
        ]);
        $id = $data['id'] ?? null;
        unset($data['id']);
        if ($id && DB::table('funtime_items')->where('id', $id)->exists()) {
            DB::table('funtime_items')->where('id', $id)->update($data + ['updated_at' => now()]);
        } else {
            DB::table('funtime_items')->insert($data + ['id' => Str::random(10), 'created_at' => now(), 'updated_at' => now()]);
        }

        return redirect(route('public.elibrary', ['type' => $data['type']], false))->with('ok', 'Item save ho gaya.');
    }

    public function destroy(string $id)
    {
        $item = DB::table('funtime_items')->where('id', $id)->first();
        DB::table('funtime_items')->where('id', $id)->delete();

        return redirect(route('public.elibrary', $item ? ['type' => $item->type] : [], false))->with('ok', 'Item delete ho gaya.');
    }

    /** Turn a YouTube watch/short URL into an embed URL (null for anything else). */
    public static function youtubeEmbed(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        return null;
    }
}
