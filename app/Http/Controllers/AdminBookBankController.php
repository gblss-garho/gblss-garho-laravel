<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminBookBankController extends Controller
{
    /** Column order of the printed Book Bank register. */
    public const SUBJECTS = ['English', 'General Science', 'Mathematics', 'P/SST', 'Sindhi', 'Islamiat', 'Arabic', 'Urdu'];

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

        // Matrix: student_id => subject => 'D' (distributed) | 'C' (collected)
        $subjects = collect(self::SUBJECTS)->merge($classCatalog->pluck('subject'))->unique(fn ($x) => mb_strtolower($x))->values()->all();
        $titleToSubject = $classCatalog->pluck('subject', 'book_title');
        $matrix = [];
        foreach (DB::table('bookbank_issues')->get()->filter(fn ($i) => $this->norm($i->class) === $n) as $i) {
            $sub = $titleToSubject[$i->book_title] ?? null;
            if (! $sub || ! $i->student_id) {
                continue;
            }
            $state = $i->status === 'Returned' ? 'C' : 'D';
            if (($matrix[$i->student_id][$sub] ?? '') !== 'C') {
                $matrix[$i->student_id][$sub] = $state;
            }
        }
        $stock = [
            'total' => (int) $classCatalog->sum(fn ($b) => $b->available_qty + $b->issued_qty),
            'issued' => (int) $classCatalog->sum('issued_qty'),
            'remaining' => (int) $classCatalog->sum('available_qty'),
        ];
        $allStock = [
            'total' => (int) $catalog->sum(fn ($b) => $b->available_qty + $b->issued_qty),
            'issued' => (int) $catalog->sum('issued_qty'),
            'remaining' => (int) $catalog->sum('available_qty'),
        ];

        return view('admin.bookbank', [
            'view' => $request->query('view') === 'catalog' ? 'catalog' : 'register',
            'subjects' => $subjects, 'matrix' => $matrix, 'stock' => $stock, 'allStock' => $allStock,
            'catalogSubjects' => $classCatalog->pluck('subject')->map(fn ($x) => mb_strtolower($x))->all(),
            'isAdmin' => $this->isAdmin(), 'classes' => $classes, 'class' => $class, 'st' => $st, 'q' => $q,
            'catalog' => $this->isAdmin() ? $catalog : $classCatalog, 'classCatalog' => $classCatalog,
            'students' => $class === '' ? collect() : Student::where('passed_out', false)->orderByRaw('CAST(gr_number AS UNSIGNED)')->orderBy('gr_number')->get()->filter(fn ($s) => $this->norm($s->class) === $n)->values(),
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

    /** "+ Issue / Update Record": set distributed / collected per subject for one student. */
    public function matrixSave(Request $r)
    {
        $d = $r->validate([
            'student_id' => 'required|string', 'class' => 'nullable|string',
            'dist' => 'nullable|array', 'dist.*' => 'string', 'coll' => 'nullable|array', 'coll.*' => 'string',
        ]);
        $student = Student::where('id', $d['student_id'])->where('passed_out', false)->first();
        if (! $student || ! $this->canUseClass($student->class)) {
            return back()->withErrors(['student_id' => 'Student nahi mila ya aapki class ka nahi.']);
        }
        $n = $this->norm($student->class);
        $coll = array_map('mb_strtolower', $d['coll'] ?? []);
        $dist = array_unique(array_merge(array_map('mb_strtolower', $d['dist'] ?? []), $coll)); // collected implies distributed
        $errors = [];

        DB::transaction(function () use ($student, $n, $coll, $dist, &$errors) {
            $catalog = DB::table('bookbank_catalog')->get()->filter(fn ($b) => $this->norm($b->class) === $n);
            foreach ($catalog as $b) {
                $key = mb_strtolower($b->subject);
                $b = DB::table('bookbank_catalog')->where('id', $b->id)->first();
                $row = DB::table('bookbank_issues')->where('student_id', $student->id)->where('book_title', $b->book_title)->orderByDesc('created_at')->first();
                $wantD = in_array($key, $dist, true);
                $wantC = in_array($key, $coll, true);

                if (! $wantD) {
                    if ($row) {
                        DB::table('bookbank_issues')->where('student_id', $student->id)->where('book_title', $b->book_title)->delete();
                        if ($row->status === 'Issued') {
                            DB::table('bookbank_catalog')->where('id', $b->id)->update(['available_qty' => $b->available_qty + 1, 'issued_qty' => max(0, $b->issued_qty - 1), 'updated_at' => now()]);
                        }
                    }
                    continue;
                }
                if (! $row) {
                    if ($b->available_qty < 1) {
                        $errors[] = $b->subject.' ki copy maujood nahi.';
                        continue;
                    }
                    DB::table('bookbank_issues')->insert([
                        'id' => Str::random(8), 'student_id' => $student->id, 'student_name' => $student->name, 'class' => $student->class,
                        'book_title' => $b->book_title, 'issue_date' => now()->toDateString(), 'condition' => 'Good',
                        'status' => $wantC ? 'Returned' : 'Issued', 'return_date' => $wantC ? now()->toDateString() : null,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    if (! $wantC) {
                        DB::table('bookbank_catalog')->where('id', $b->id)->update(['available_qty' => $b->available_qty - 1, 'issued_qty' => $b->issued_qty + 1, 'updated_at' => now()]);
                    }
                } elseif ($wantC && $row->status === 'Issued') {
                    DB::table('bookbank_issues')->where('id', $row->id)->update(['status' => 'Returned', 'return_date' => now()->toDateString(), 'updated_at' => now()]);
                    DB::table('bookbank_catalog')->where('id', $b->id)->update(['available_qty' => $b->available_qty + 1, 'issued_qty' => max(0, $b->issued_qty - 1), 'updated_at' => now()]);
                } elseif (! $wantC && $row->status === 'Returned') {
                    if ($b->available_qty < 1) {
                        $errors[] = $b->subject.' ki copy maujood nahi (dobara issue nahi ho saki).';
                        continue;
                    }
                    DB::table('bookbank_issues')->where('id', $row->id)->update(['status' => 'Issued', 'return_date' => null, 'updated_at' => now()]);
                    DB::table('bookbank_catalog')->where('id', $b->id)->update(['available_qty' => $b->available_qty - 1, 'issued_qty' => $b->issued_qty + 1, 'updated_at' => now()]);
                }
            }
        });

        $r->merge(['class' => $student->class]);
        $resp = $this->back($student->name.' ka record update ho gaya.');

        return $errors ? $resp->withErrors($errors) : $resp;
    }
}
