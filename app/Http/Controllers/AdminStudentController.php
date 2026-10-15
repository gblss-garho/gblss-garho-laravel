<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
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
            ->orderBy('class')->orderBy('name')
            ->simplePaginate(30)->withQueryString();

        return view('admin.students.index', compact('students', 'q', 'class', 'classes'));
    }

    public function create()
    {
        return view('admin.students.form', ['student' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['id'] = (string) Str::uuid();
        $data['passed_out'] = 0;
        Student::create($data);

        return redirect()->route('admin.students.index')->with('ok', 'Student add ho gaya.');
    }

    public function edit(Student $student)
    {
        return view('admin.students.form', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $student->update($request->validate($this->rules($student)));

        return redirect()->route('admin.students.index')->with('ok', 'Student update ho gaya.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('ok', 'Student delete ho gaya.');
    }
}
