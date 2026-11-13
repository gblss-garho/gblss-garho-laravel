<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminGrRegisterController extends Controller
{
    private const PER_PAGE = 50;

    public function import(Request $request)
    {
        $request->validate(['csv' => ['required', 'file', 'max:2048']]);

        $tmp = $request->file('csv')->getRealPath();
        $code = \Illuminate\Support\Facades\Artisan::call('register:import', ['--csv' => $tmp]);
        $out = trim(\Illuminate\Support\Facades\Artisan::output());

        return redirect()->route('admin.gr-register', [], false)->with('ok', ($code === 0 ? 'Import mukammal. ' : 'Import fail: ').$out);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $f = in_array($request->query('f'), ['active', 'left'], true) ? $request->query('f') : 'all';

        $reg = Schema::hasTable('gr_register') ? DB::table('gr_register')->get()->keyBy('reg_no') : collect();
        $stu = Student::all()->keyBy(fn ($s) => (int) $s->gr_number);

        $rows = collect();
        foreach ($reg as $n => $x) {
            $s = $stu->get($n);
            $rows->put($n, (object) [
                'reg' => $n, 'name' => $s->name ?? $x->name, 'father' => $s->father_name ?? $x->father_name,
                'surname' => $x->surname, 'dob' => $s->dob ?? $x->dob, 'address' => $s->address ?? $x->address,
                'adm_date' => $s->admission_date ?? $x->admission_date, 'adm_class' => $x->admission_class,
                'rem_date' => $x->removal_date, 'rem_class' => $x->removal_class, 'cause' => $x->removal_cause,
                'remarks' => $x->remarks, 'status' => $x->status,
                'current' => ($s && ! $s->passed_out) ? $s->class.($s->section ? '-'.$s->section : '') : null,
                'active' => (bool) ($s && ! $s->passed_out),
            ]);
        }
        foreach ($stu as $n => $s) {
            if ($n > 0 && ! $rows->has($n)) {
                $rows->put($n, (object) [
                    'reg' => $n, 'name' => $s->name, 'father' => $s->father_name, 'surname' => null, 'dob' => $s->dob,
                    'address' => $s->address, 'adm_date' => $s->admission_date, 'adm_class' => null, 'rem_date' => null,
                    'rem_class' => null, 'cause' => null, 'remarks' => null, 'status' => $s->status,
                    'current' => $s->passed_out ? null : $s->class.($s->section ? '-'.$s->section : ''),
                    'active' => ! $s->passed_out,
                ]);
            }
        }
        $rows = $rows->sortKeys();

        $total = $rows->count();
        $activeCount = $rows->where('active', true)->count();
        $last = $rows->keys()->last() ?? 0;

        $rows = $rows->filter(function ($r) use ($q, $f) {
            if ($f === 'active' && ! $r->active) {
                return false;
            }
            if ($f === 'left' && $r->active) {
                return false;
            }
            return $q === '' || str_contains(mb_strtolower($r->name.' '.$r->father.' '.$r->reg), mb_strtolower($q));
        })->values();

        $pages = max(1, (int) ceil($rows->count() / self::PER_PAGE));
        $page = min($pages, max(1, (int) $request->query('page', 1)));

        return view('admin.gr-register', [
            'rows' => $rows->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE),
            'q' => $q, 'f' => $f, 'page' => $page, 'pages' => $pages, 'shown' => $rows->count(),
            'total' => $total, 'activeCount' => $activeCount, 'last' => $last,
            'empty' => $reg->isEmpty(),
        ]);
    }
}
