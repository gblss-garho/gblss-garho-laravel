<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminStudentController extends Controller
{
    private function rules(?Student $student = null): array
    {
        return [
            'gr_number' => ['required', 'string', 'max:50', Rule::unique('students', 'gr_number')->ignore($student?->id, 'id')],
            'name' => ['required', 'string', 'max:150'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'dob' => ['nullable', 'date'],
            'class' => ['required', 'string', 'max:50'],
            'section' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'admission_date' => ['nullable', 'date'],
        ];
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $class = (string) $request->query('class', '');
        $classes = Student::where('passed_out', 0)->distinct()->orderBy('class')->pluck('class');
        $students = Student::where('passed_out', 0)
            ->when($q !== '', fn ($x) => $x->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('gr_number', 'like', "%{$q}%")->orWhere('father_name', 'like', "%{$q}%");
            }))
            ->when($class !== '', fn ($x) => $x->where('class', $class))
            ->orderBy('class')->orderByRaw('CAST(gr_number AS UNSIGNED)')->orderBy('gr_number')
            ->simplePaginate(30)->withQueryString();

        return view('admin.students.index', compact('students', 'q', 'class', 'classes'));
    }

    /** Next free GR number: one above the highest in the students table or the GR register. */
    private function nextGr(): int
    {
        $max = (int) DB::table('students')->max(DB::raw('CAST(gr_number AS UNSIGNED)'));
        if (Schema::hasTable('gr_register')) {
            $max = max($max, (int) DB::table('gr_register')->max('reg_no'));
        }

        return $max + 1;
    }

    public function create(Request $request)
    {
        $gr = (int) $request->query('gr', 0);

        return view('admin.students.form', ['student' => null, 'nextGr' => $gr > 0 ? $gr : $this->nextGr()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $reg = ctype_digit(trim($data['gr_number'])) ? (int) $data['gr_number'] : null;
        $hasReg = Schema::hasTable('gr_register');
        if ($reg && $hasReg && DB::table('gr_register')->where('reg_no', $reg)->exists()) {
            return back()->withInput()->withErrors(['gr_number' => "GR {$reg} General Register me pehle se maujood hai (purana/left student). Naya GR number likhein."]);
        }
        $data['id'] = str_replace('-', '', (string) Str::uuid());   // students.id is varchar(32)
        $data['passed_out'] = 0;
        DB::transaction(function () use ($data, $reg, $hasReg) {
            Student::create($data);
            if ($reg && $hasReg) {
                DB::table('gr_register')->insert([
                    'reg_no' => $reg, 'name' => $data['name'], 'father_name' => $data['father_name'] ?? null,
                    'dob' => $data['dob'] ?? null, 'address' => $data['address'] ?? null,
                    'admission_date' => $data['admission_date'] ?? now()->toDateString(), 'admission_class' => $data['class'],
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.gr-register', ['q' => $data['gr_number']])->with('ok', 'Naya student add ho gaya aur GR register me darj ho gaya (GR '.$data['gr_number'].').');
    }

    public function edit(Student $student)
    {
        return view('admin.students.form', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate($this->rules($student) + \App\Services\StudentStatusService::rules());
        $status = \Illuminate\Support\Arr::only($data, array_keys(\App\Services\StudentStatusService::rules()));
        $student->update(\Illuminate\Support\Arr::except($data, array_keys($status)));
        app(\App\Services\StudentStatusService::class)->apply((int) $student->gr_number, $student->fresh(), $status);

        return redirect()->route('admin.students.index')->with('ok', 'Student update ho gaya.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('ok', 'Student delete ho gaya.');
    }
}
