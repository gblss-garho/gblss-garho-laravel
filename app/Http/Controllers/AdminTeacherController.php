<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminTeacherController extends Controller
{
    private const FIELDS = ['name', 'cnic', 'pid', 'dob', 'subject', 'designation', 'qualification', 'experience', 'entry_in_service'];

    private function rules(?Teacher $teacher = null): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'cnic' => ['nullable', 'string', 'max:30'],
            'pid' => ['nullable', 'string', 'max:30'],
            'dob' => ['nullable', 'date'],
            'subject' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'qualification' => ['nullable', 'string', 'max:150'],
            'experience' => ['nullable', 'string', 'max:100'],
            'entry_in_service' => ['nullable', 'date'],
            'auth_email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($teacher?->user_id)],
            'pin' => [$teacher ? 'nullable' : 'required', 'digits:4'],
            'classes' => ['nullable', 'string', 'max:200'],
        ];
    }

    private function saveClasses(string $teacherId, ?string $csv): void
    {
        DB::table('teacher_classes')->where('teacher_id', $teacherId)->delete();
        $now = now()->toDateTimeString();
        foreach (array_unique(array_filter(array_map('trim', explode(',', (string) $csv)))) as $c) {
            DB::table('teacher_classes')->insert([
                'teacher_id' => $teacherId, 'class_name' => $c, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function index()
    {
        $teachers = Teacher::with('classes')->orderBy('name')->get();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.form', ['teacher' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $now = now()->toDateTimeString();

        DB::transaction(function () use ($data, $now) {
            $userId = DB::table('users')->insertGetId([
                'name' => $data['name'], 'email' => strtolower($data['auth_email']),
                'password' => Hash::make($data['pin']), 'role' => 'teacher', 'is_active' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $teacher = Teacher::create(array_merge(
                ['id' => (string) Str::uuid(), 'user_id' => $userId, 'auth_email' => strtolower($data['auth_email'])],
                array_intersect_key($data, array_flip(self::FIELDS))
            ));
            $this->saveClasses($teacher->id, $data['classes'] ?? '');
        });

        return redirect()->route('admin.teachers.index')->with('ok', 'Teacher add ho gaya.');
    }

    public function edit(Teacher $teacher)
    {
        return view('admin.teachers.form', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $data = $request->validate($this->rules($teacher));
        $now = now()->toDateTimeString();

        DB::transaction(function () use ($data, $teacher, $request, $now) {
            $teacher->fill(array_intersect_key($data, array_flip(self::FIELDS)));
            $teacher->auth_email = strtolower($data['auth_email']);
            if ($request->boolean('reset_face')) {
                $teacher->face_descriptor = null;
            }
            $teacher->save();

            $userVals = ['name' => $data['name'], 'email' => strtolower($data['auth_email']), 'updated_at' => $now];
            if (! empty($data['pin'])) {
                $userVals['password'] = Hash::make($data['pin']);
            }
            DB::table('users')->where('id', $teacher->user_id)->update($userVals);
            $this->saveClasses($teacher->id, $data['classes'] ?? '');
        });

        return redirect()->route('admin.teachers.index')->with('ok', 'Teacher update ho gaya.');
    }

    public function destroy(Teacher $teacher)
    {
        $userId = $teacher->user_id;
        DB::transaction(function () use ($teacher, $userId) {
            $teacher->delete();
            if ($userId) {
                DB::table('users')->where('id', $userId)->where('role', 'teacher')->delete();
            }
        });

        return redirect()->route('admin.teachers.index')->with('ok', 'Teacher delete ho gaya.');
    }
}
