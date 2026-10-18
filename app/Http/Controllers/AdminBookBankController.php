<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminBookBankController extends Controller
{
    public function index()
    {
        return view('admin.bookbank', [
            'catalog' => DB::table('bookbank_catalog')->orderBy('class')->orderBy('subject')->get(),
            'open' => DB::table('bookbank_issues')->where('status', 'Issued')->orderByDesc('issue_date')->get(),
            'returned' => DB::table('bookbank_issues')->where('status', 'Returned')->orderByDesc('issue_date')->limit(30)->get(),
        ]);
    }

    public function catalogAdd(Request $r)
    {
        $d = $r->validate([
            'class' => 'required|string|max:20', 'subject' => 'required|string|max:100',
            'book_title' => 'required|string|max:200', 'available_qty' => 'required|integer|min:0',
        ]);
        DB::table('bookbank_catalog')->insert($d + ['id' => Str::random(8), 'issued_qty' => 0, 'created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('admin.bookbank.index')->with('ok', 'Kitaab add ho gayi.');
    }

    public function catalogQty(Request $r, string $id)
    {
        $d = $r->validate(['available_qty' => 'required|integer|min:0']);
        DB::table('bookbank_catalog')->where('id', $id)->update(['available_qty' => $d['available_qty'], 'updated_at' => now()]);
        return redirect()->route('admin.bookbank.index')->with('ok', 'Tadaad badal gayi.');
    }

    public function catalogDelete(string $id)
    {
        DB::table('bookbank_catalog')->where('id', $id)->delete();
        return redirect()->route('admin.bookbank.index')->with('ok', 'Kitaab hata di gayi.');
    }

    public function issue(Request $r)
    {
        $d = $r->validate(['catalog_id' => 'required|string', 'gr_number' => 'required|string']);
        $book = DB::table('bookbank_catalog')->where('id', $d['catalog_id'])->first();
        $student = Student::where('gr_number', trim($d['gr_number']))->where('passed_out', false)->first();
        if (! $book || ! $student) {
            return back()->withErrors(['gr_number' => 'Kitaab ya student (GR number) nahi mila.']);
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
        return redirect()->route('admin.bookbank.index')->with('ok', 'Kitaab issue ho gayi: ' . $student->name);
    }

    public function giveBack(Request $r, string $id)
    {
        $d = $r->validate(['condition' => 'nullable|string|max:50']);
        $row = DB::table('bookbank_issues')->where('id', $id)->where('status', 'Issued')->first();
        abort_unless($row, 404);
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
        return redirect()->route('admin.bookbank.index')->with('ok', 'Kitaab wapas le li gayi.');
    }
}
