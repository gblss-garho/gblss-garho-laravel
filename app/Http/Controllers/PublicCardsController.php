<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Support\CardData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Public ID Cards page: student (GR + DOB) and staff (CNIC / PID) lookup with an on-page preview. */
class PublicCardsController extends PublicLookupController
{
    public function show()
    {
        return view('pages.id-cards', ['error' => null, 'terror' => null, 'card' => null]);
    }

    /** Student: search, then show the card on the page. */
    public function download(Request $request)
    {
        $error = null;
        $student = $this->lookupStudent($request, $error);
        if (! $student) {
            return view('pages.id-cards', ['error' => $error, 'terror' => null, 'card' => null]);
        }

        return view('pages.id-cards', [
            'error' => null, 'terror' => null,
            'card' => ['type' => 'student', 'person' => $student, 'idText' => CardData::studentId($student), 'qr' => CardData::qr($student),
                'pdfUrl' => route('public.id-cards.pdf', [], false), 'fields' => ['gr_number' => $student->gr_number, 'dob' => $request->input('dob')]],
        ]);
    }

    public function pdf(Request $request)
    {
        $error = null;
        $student = $this->lookupStudent($request, $error);
        if (! $student) {
            return view('pages.id-cards', ['error' => $error, 'terror' => null, 'card' => null]);
        }

        return app(AdminIdCardController::class)->pdf($student);
    }

    /** Staff: find by PID or CNIC (dashes/spaces ignored). */
    private function lookupTeacher(Request $request, ?string &$error): ?Teacher
    {
        $key = preg_replace('/[\s-]+/', '', trim((string) $request->validate(['cnic_pid' => ['required', 'string', 'max:30']])['cnic_pid']));

        $tries = DB::table('lookup_attempts')->where('scope', 'teacher')->where('identifier', $key)
            ->where('created_at', '>', now()->subMinutes(15)->toDateTimeString())->count();
        if ($tries >= 8) {
            $error = 'Bohat zyada koshishein ho gayi hain. 15 minute baad dobara try karein.';

            return null;
        }
        DB::table('lookup_attempts')->insert(['scope' => 'teacher', 'identifier' => $key, 'created_at' => now()->toDateTimeString()]);

        $teacher = Teacher::all()->first(fn ($t) => $key !== '' && (
            preg_replace('/[\s-]+/', '', (string) $t->pid) === $key || preg_replace('/[\s-]+/', '', (string) $t->getRawOriginal('cnic')) === $key
        ));
        if (! $teacher) {
            $error = 'Is CNIC / PID ka koi staff record nahi mila.';
        }

        return $teacher;
    }

    public function teacher(Request $request)
    {
        $error = null;
        $teacher = $this->lookupTeacher($request, $error);
        if (! $teacher) {
            return view('pages.id-cards', ['error' => null, 'terror' => $error, 'card' => null]);
        }
        $card = $teacher->replicate();          // preview copy: never print the CNIC on a public page
        $card->id = $teacher->id; $card->cnic = null; $card->photo_url = $teacher->photo_url;

        return view('pages.id-cards', [
            'error' => null, 'terror' => null,
            'card' => ['type' => 'teacher', 'person' => $card, 'idText' => CardData::staffId($teacher), 'qr' => CardData::qr($teacher),
                'pdfUrl' => route('public.id-cards.teacher.pdf', [], false), 'fields' => ['cnic_pid' => $request->input('cnic_pid')]],
        ]);
    }

    public function teacherPdf(Request $request)
    {
        $error = null;
        $teacher = $this->lookupTeacher($request, $error);
        if (! $teacher) {
            return view('pages.id-cards', ['error' => null, 'terror' => $error, 'card' => null]);
        }

        return app(AdminStaffIdCardController::class)->pdf($teacher);
    }
}
