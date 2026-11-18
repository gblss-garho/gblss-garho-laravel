<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminBookBankController extends Controller
{
    private function isAdmin(): bool
    {
        return auth()->user()->role === 'admin';
    }

    private function norm(?string $c): string
    {
        return preg_replace('/^class|\s+/i', '', mb_strtolower((string) $c));
    }

    /** Classes the signed-in user may see: admin = all, teacher = assigned. */
    private function classes(): array
    {
        if ($this->isAdmin()) {
            $all = Student::where('passed_out', false)->distinct()->pluck('class')->merge(DB::table('bookbank_catalog')->distinct()->pluck('class'));
        } else {
            $all = collect(auth()->user()->teacher?->assigned_classes_list ?? []);
        }

        return $all->filter()->unique(fn ($c) => $this->norm($c))->sort()->values()->all();
    }

    private function canUseClass(?string $class): bool
    {
        $n = $this->norm($class);

        return $this->isAdmin() || in_array($n, array_map(fn ($c) => $this->norm($c), $this->classes()), true);
    }

    private function back(string $msg)
    {
        $name = $this->isAdmin() ? 'admin.bookbank.index' : 'teacher.bookbank.index';

        return redirect(route($name, ['class' => request('class')], false))->with('ok', $msg);
    }

    public function index(Request $request)
    {
        $classes = $this->classes();
        $class = (string) $request->query('class', $classes[0] ?? '');
        if ($class !== '' && ! $this->canUseClass($class)) {
            $class = $classes[0] ?? '';
        }
        $n = $this->norm($class);
        $st = in_array($request->query('st'), ['Issued', 'Returned'], true) ? $request->query('st') : 'all';
        $q = trim((string) $request->query('q', ''));

        $catalog = DB::table('bookbank_catalog')->orderBy('class')->orderBy('subject')->get();
        $classCatalog = $catalog->filter(fn ($b) => $this->norm($b->class) === $n)->values();

        $issues = DB::table('bookbank_issues')->orderByDesc('issue_date')->get()
            ->filter(fn ($i) => $this->norm($i->class) === $n)
            ->when($st !== 'all', fn ($c) => $c->where('status', $st))
            ->when($q !== '', fn ($c) => $c->filter(fn ($i) => str_contains(mb_strtolower($i->student_name.' '.$i->book_title), mb_strtolower($q))))
            ->values();

        $grById = Student::whereIn('id', $issues->pluck('student_id')->filter()->all())->pluck('gr_number', 'id');

        return view('admin.bookbank', [
            'isAdmin' => $this->isAdmin(), 'classes' => $classes, 'class' => $class, 'st' => $st, 'q' => $q,
            'catalog' => $this->isAdmin() ? $catalog : $classCatalog, 'classCatalog' => $classCatalog,
            'students' => $class === '' ? collect() : Student::where('passed_out', false)->orderBy('name')->get()->filter(fn ($s) => $this->norm($s->class) === $n)->values(),
            'issues' => $issues, 'grById' => $grById,
            'openCount' => $issues->where('status', 'Issued')->count(),
            'base' => route($this->isAdmin() ? 'admin.bookbank.index' : 'teacher.bookbank.index', [], false),
            'prefix' => $this->isAdmin() ? 'admin' : 'teacher',
            'back' => route($this->isAdmin() ? 'admin.dashboard' : 'teacher.dashboard', [], false),
        ]);
    }

    public function catalogAdd(Request $r)
    {
        $d = $r->validate([
            'class' => 'required|string|max:20', 'subject' => 'required|string|max:100',
            'book_title' => 'required|string|max:200', 'available_qty' => 'required|integer|min:0',
        ]);
        DB::table('bookbank_catalog')->insert($d + ['id' => Str::random(8), 'issued_qty' => 0, 'created_at' => now(), 'updated_at' => now()]);
        return $this->back('Kitaab add ho gayi.');
    }

    public function catalogQty(Request $r, string $id)
    {
        $d = $r->validate(['available_qty' => 'required|integer|min:0']);
        DB::table('bookbank_catalog')->where('id', $id)->update(['available_qty' => $d['available_qty'], 'updated_at' => now()]);
        return $this->back('Tadaad badal gayi.');
    }

    public function catalogDelete(string $id)
    {
        DB::table('bookbank_catalog')->where('id', $id)->delete();
        return $this->back('Kitaab hata di gayi.');
    }

    public function issue(Request $r)
    {
        $d = $r->validate(['catalog_id' => 'required|string', 'student_id' => 'nullable|string', 'gr_number' => 'nullable|string']);
        $book = DB::table('bookbank_catalog')->where('id', $d['catalog_id'])->first();
        $student = ! empty($d['student_id'])
            ? Student::where('id', $d['student_id'])->where('passed_out', false)->first()
            : Student::where('gr_number', trim((string) ($d['gr_number'] ?? '')))->where('passed_out', false)->first();
        if (! $book || ! $student) {
            return back()->withErrors(['gr_number' => 'Kitaab ya student nahi mila.']);
        }
        if (! $this->canUseClass($student->class)) {
            return back()->withErrors(['gr_number' => $student->name.' aapki class ka student nahi hai.']);
        }
        if ($book->available_qty < 1) {
            return back()->withErrors(['gr_number' => 'Is kitaab ki copy maujood nahi.']);
        }
        DB::transaction(function () use ($book, $student) {
            DB::table('bookbank_issues')->insert([
                'id' => Str::random(8), 'student_id' => $student->id, 'student_name' => $student->name,
                'class' => $student->class, 'book_title' => $book->book_title,
                'issue_date' => now()->toDateString(), 'condition' => 'Good', 'status' => 'Issued',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('bookbank_catalog')->where('id', $book->id)->update([
                'available_qty' => $book->available_qty - 1, 'issued_qty' => $book->issued_qty + 1, 'updated_at' => now(),
            ]);
        });
        return $this->back('Kitaab issue ho gayi: '.$student->name);
    }

    public function giveBack(Request $r, string $id)
    {
        $d = $r->validate(['condition' => 'nullable|string|max:50']);
        $row = DB::table('bookbank_issues')->where('id', $id)->where('status', 'Issued')->first();
        abort_unless($row && $this->canUseClass($row->class), 404);
        DB::transaction(function () use ($row, $d) {
            DB::table('bookbank_issues')->where('id', $row->id)->update([
                'status' => 'Returned', 'return_date' => now()->toDateString(),
                'condition' => $d['condition'] ?: 'Good', 'updated_at' => now(),
            ]);
            $book = DB::table('bookbank_catalog')->where('book_title', $row->book_title)->first();
            if ($book) {
                DB::table('bookbank_catalog')->where('id', $book->id)->update([
                    'available_qty' => $book->available_qty + 1, 'issued_qty' => max(0, $book->issued_qty - 1), 'updated_at' => now(),
                ]);
            }
        });
        return $this->back('Kitaab wapas le li gayi.');
    }
}
