<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Downloads centre: uploaded letters/notifications + quick links to every printable record. */
class DownloadsController extends Controller
{
    public const CATEGORIES = ['Letter', 'Notification', 'Form', 'Certificate', 'Circular', 'Other'];

    public function index(Request $request)
    {
        $user = $request->user();
        $staff = $user && in_array($user->role, ['admin', 'teacher'], true);
        $cat = (string) $request->query('cat', '');
        $q = trim((string) $request->query('q', ''));

        $docs = DB::table('downloads')
            ->when(! $staff, fn ($x) => $x->where('audience', 'public'))
            ->when(in_array($cat, self::CATEGORIES, true), fn ($x) => $x->where('category', $cat))
            ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%")))
            ->orderByDesc('created_at')->get();

        $classes = [];
        if ($user && $user->role === 'admin') {
            $classes = Student::where('passed_out', false)->distinct()->orderBy('class')->pluck('class')->all();
        }

        return view('downloads', [
            'docs' => $docs, 'staff' => $staff, 'isAdmin' => $user && $user->role === 'admin',
            'isTeacher' => $user && $user->role === 'teacher', 'cat' => $cat, 'q' => $q,
            'categories' => self::CATEGORIES, 'classes' => $classes,
        ]);
    }

    public function file(Request $request, string $id)
    {
        $doc = DB::table('downloads')->where('id', $id)->first();
        abort_unless($doc, 404);
        $user = $request->user();
        if ($doc->audience !== 'public') {
            abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);
        }
        abort_unless(Storage::disk('local')->exists($doc->file_path), 404);

        return Storage::disk('local')->download($doc->file_path, $doc->file_name);
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'in:'.implode(',', self::CATEGORIES)],
            'audience' => ['required', 'in:public,staff'],
            'description' => ['nullable', 'string', 'max:500'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,txt'],
        ]);
        $f = $request->file('file');
        $ext = strtolower($f->getClientOriginalExtension() ?: $f->extension());
        $path = $f->storeAs('downloads', Str::random(24).'.'.$ext, 'local');

        DB::table('downloads')->insert([
            'id' => Str::random(10), 'title' => $d['title'], 'category' => $d['category'], 'audience' => $d['audience'],
            'description' => $d['description'] ?? null, 'file_path' => $path,
            'file_name' => Str::limit(Str::slug($d['title']), 80, '').'.'.$ext, 'mime' => $f->getClientMimeType(),
            'size' => $f->getSize(), 'uploaded_by' => $request->user()->email,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return redirect(route('downloads.index', [], false))->with('ok', 'File upload ho gayi.');
    }

    public function destroy(string $id)
    {
        $doc = DB::table('downloads')->where('id', $id)->first();
        abort_unless($doc, 404);
        Storage::disk('local')->delete($doc->file_path);
        DB::table('downloads')->where('id', $id)->delete();

        return redirect(route('downloads.index', [], false))->with('ok', 'File hata di gayi.');
    }
}
