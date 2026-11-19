<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Parents' Leave / Absence Application (verified with GR + date of birth). */
class PublicLeaveController extends PublicLookupController
{
    public const PURPOSES = ['Sick Leave', 'Family Function', 'Travel', 'Other'];

    public function store(Request $request)
    {
        $error = null;
        $student = $this->lookupStudent($request, $error);
        if (! $student) {
            return view('pages.lookup', ['student' => null, 'exams' => [], 'error' => $error]);
        }

        $v = validator($request->all(), [
            'leave_date' => ['required', 'date', 'after_or_equal:'.now()->subDays(14)->toDateString(), 'before_or_equal:'.now()->addDays(60)->toDateString()],
            'purpose' => ['required', 'in:'.implode(',', self::PURPOSES)],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [], ['leave_date' => 'Date', 'purpose' => 'Purpose', 'reason' => 'Reason', 'attachment' => 'Attachment']);
        if ($v->fails()) {
            return $this->renderStudent($student, $request->input('dob'), null, $v->errors()->first());
        }

        $id = Str::random(10);
        $path = $name = null;
        if ($file = $request->file('attachment')) {
            $name = mb_substr($file->getClientOriginalName(), 0, 120);
            $path = $file->storeAs('leave-applications', $id.'.'.strtolower($file->getClientOriginalExtension() ?: $file->extension()), 'local');
        }
        DB::table('student_leave_applications')->insert([
            'id' => $id, 'student_id' => $student->id, 'student_name' => $student->name, 'class_name' => $student->class,
            'leave_date' => $request->input('leave_date'), 'purpose' => $request->input('purpose'), 'reason' => trim($request->input('reason')),
            'attachment_path' => $path, 'attachment_name' => $name, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);

        \App\Services\HeadMasterNotifier::leaveApplied($student->name, (string) $student->class, (string) $request->input('leave_date'), (string) $request->input('purpose'));

        return $this->renderStudent($student, $request->input('dob'), 'Aap ki arzi school ko bhej di gayi hai. Shukriya.');
    }
}
