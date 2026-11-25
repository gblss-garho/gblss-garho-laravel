<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\TimetableEntry;
use App\Models\TimetablePeriod;
use App\Services\TimetableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Teaching assignments + weekly timetable (admin), own timetable (teacher), class timetable (public). */
class TimetableController extends Controller
{
    private function classFrom(Request $r): string
    {
        return TimetableService::validClass((string) $r->query('class', $r->input('class_name', ''))) ?? TimetableService::classes()[0];
    }

    // ---------- admin: teaching assignments ----------

    public function assignments(Request $request)
    {
        $class = $this->classFrom($request);

        return view('timetable.assignments', [
            'class' => $class,
            'classes' => TimetableService::classes(),
            'rows' => TeachingAssignment::with('teacher:id,name')->where('class_name', $class)->orderBy('subject')->get(),
            'teachers' => Teacher::orderBy('name')->get(['id', 'name', 'subject']),
            'subjects' => ['English', 'Urdu', 'Sindhi', 'Mathematics', 'General Science', 'Islamiat', 'Social Studies', 'Computer', 'Drawing', 'General Knowledge', 'Physical Education'],
        ]);
    }

    public function assignmentStore(Request $request)
    {
        $data = $request->validate([
            'class_name' => ['required', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:60'],
            'teacher_id' => ['required', 'string', 'exists:teachers,id'],
        ]);
        $class = TimetableService::validClass($data['class_name']);
        abort_unless($class, 422, 'Class theek nahi.');
        $subject = trim(preg_replace('/\s+/u', ' ', $data['subject']));
        abort_if($subject === '', 422, 'Subject likhein.');

        $existing = TeachingAssignment::where('class_name', $class)->where('subject', $subject)->first();
        TeachingAssignment::updateOrCreate(['class_name' => $class, 'subject' => $subject], ['teacher_id' => $data['teacher_id']]);
        if ($existing && $existing->teacher_id !== $data['teacher_id']) {
            // Teacher changed: the timetable cells for this subject follow the new teacher.
            TimetableEntry::where('class_name', $class)->where('subject', $subject)->update(['teacher_id' => $data['teacher_id']]);
        }

        return redirect(route('admin.teaching.index', ['class' => $class], false))->with('ok', 'Teaching assignment save ho gaya.');
    }

    public function assignmentDestroy(int $id)
    {
        $a = TeachingAssignment::findOrFail($id);
        DB::transaction(function () use ($a) {
            TimetableEntry::where('class_name', $a->class_name)->where('subject', $a->subject)->delete();
            $a->delete();
        });

        return redirect(route('admin.teaching.index', ['class' => $a->class_name], false))->with('ok', 'Hata diya (timetable se bhi).');
    }

    // ---------- admin: timetable grid ----------

    public function edit(Request $request, TimetableService $tt)
    {
        $class = $this->classFrom($request);
        $assign = TeachingAssignment::with('teacher:id,name')->where('class_name', $class)->orderBy('subject')->get();

        return view('timetable.edit', [
            'class' => $class,
            'classes' => TimetableService::classes(),
            'grid' => $tt->classGrid($class),
            'periods' => $tt->periods(),
            'assign' => $assign,
        ]);
    }

    public function save(Request $request, TimetableService $tt)
    {
        $class = TimetableService::validClass((string) $request->input('class_name'));
        abort_unless($class, 422, 'Class theek nahi.');

        $assign = TeachingAssignment::with('teacher:id,name')->where('class_name', $class)->get()->keyBy('id');
        $names = $assign->mapWithKeys(fn ($a) => [$a->teacher_id => $a->teacher?->name])->all();

        $cells = [];
        $new = [];
        foreach (array_keys(TimetableService::DAYS) as $day) {
            for ($p = 1; $p <= TimetableService::PERIODS; $p++) {
                $id = (string) $request->input("cell.$day.$p", '');
                if ($id === '') {
                    continue;
                }
                $a = $assign->get((int) $id);
                if (! $a) {
                    return back()->withInput()->withErrors(['grid' => 'Ek khana is class ke Teaching Assignment mein nahi hai.']);
                }
                $new[] = ['day' => $day, 'period' => $p, 'subject' => $a->subject, 'teacher_id' => $a->teacher_id];
                $cells[$day][$p] = $a->teacher_id;
            }
        }

        $clashes = $tt->clashes($class, $cells, $names);
        if ($clashes) {
            return back()->withInput()->withErrors(['grid' => 'Save nahi hua. '.implode(' · ', $clashes)]);
        }

        $times = [];
        for ($p = 1; $p <= TimetableService::PERIODS; $p++) {
            foreach (['starts_at', 'ends_at'] as $k) {
                $v = trim((string) $request->input("times.$p.$k", ''));
                if ($v !== '' && ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v)) {
                    return back()->withInput()->withErrors(['grid' => "Period $p ka waqt theek nahi (HH:MM)."]);
                }
                $times[$p][$k] = $v === '' ? null : $v;
            }
        }

        DB::transaction(function () use ($class, $new, $times) {
            TimetableEntry::where('class_name', $class)->delete();
            foreach ($new as $n) {
                TimetableEntry::create($n + ['class_name' => $class]);
            }
            foreach ($times as $p => $t) {
                TimetablePeriod::updateOrCreate(['period' => $p], $t);
            }
        });

        return redirect(route('admin.timetable.edit', ['class' => $class], false))->with('ok', 'Timetable save ho gaya.');
    }

    // ---------- teacher: own week ----------

    public function mine(TimetableService $tt)
    {
        $t = Teacher::where('user_id', auth()->id())->first();

        return view('timetable.teacher', [
            'teacher' => $t,
            'grid' => $t ? $tt->teacherGrid($t->id) : null,
            'periods' => $tt->periods(),
        ]);
    }

    // ---------- public: any class ----------

    public function show(Request $request, TimetableService $tt)
    {
        $class = $this->classFrom($request);

        return view('timetable.public', [
            'class' => $class,
            'classes' => TimetableService::classes(),
            'grid' => $tt->classGrid($class),
            'periods' => $tt->periods(),
        ]);
    }
}
