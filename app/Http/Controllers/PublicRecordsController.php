<?php

namespace App\Http\Controllers;

use App\Models\LeavingCertificate;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/** Old-site public tabs: Admit Card, Leaving Certificate, Alumni. */
class PublicRecordsController extends PublicLookupController
{
    public function admitShow()
    {
        return view('pages.admit-card', ['error' => null, 'card' => null]);
    }

    private function admitStudent(Request $request, ?string &$error): ?Student
    {
        $student = $this->lookupStudent($request, $error);
        if ($student && $student->passed_out) {
            $student = null;
            $error = 'Yeh student ab school mein enrolled nahi (passed out / LC issued), is liye admit card jari nahi ho sakta.';
        }
        $class = trim((string) $request->input('class', ''));
        if ($student && $class !== '' && $student->class !== $class) {
            $student = null;
            $error = 'GR نمبر ۽ تاريخ پيدائش نه ملي، يا هي ڪلاس ۾ ناهي.';
        }

        return $student;
    }

    /** Search, then show the slip on the page. */
    public function admitDownload(Request $request)
    {
        $error = null;
        $student = $this->admitStudent($request, $error);
        if (! $student) {
            return view('pages.admit-card', ['error' => $error, 'card' => null]);
        }

        return view('pages.admit-card', ['error' => null, 'card' => [
            'student' => $student, 'school' => \App\Support\CardData::school(),
            'pdfUrl' => URL::temporarySignedRoute('public.admit-card.pdf', now()->addMinutes(30), ['student' => $student->id], false),
        ]]);
    }

    /** Signed GET link (works with Android Chrome downloads); student already verified by GR + DOB. */
    public function admitPdf(Student $student)
    {
        return app(AdminAdmitCardController::class)->pdf($student);
    }

    public function lcShow()
    {
        return view('pages.leaving-certificate', ['error' => null]);
    }

    public function lcDownload(Request $request)
    {
        $error = null;
        $student = $this->lookupStudent($request, $error);
        $certificate = null;
        if ($student) {
            $year = trim((string) $request->input('year', ''));
            $certificate = LeavingCertificate::query()
                ->where(fn ($q) => $q->where('student_id', $student->id)->orWhere('gr_number', $student->gr_number))
                ->when($year !== '', fn ($q) => $q->where('year', $year))
                ->orderByDesc('issue_date')->first();
            if (! $certificate) {
                $error = 'اڃا تائين هن GR لاءِ Leaving Certificate جاري نه ٿيو آهي.';
            }
        }
        if (! $certificate) {
            return view('pages.leaving-certificate', ['error' => $error]);
        }

        return app(AdminLeavingCertificateController::class)->pdf($certificate);
    }

    public function alumni(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $year = trim((string) $request->query('year', ''));
        $years = Student::where('passed_out', true)->whereNotNull('pass_out_year')->where('pass_out_year', '!=', '')
            ->distinct()->orderByDesc('pass_out_year')->pluck('pass_out_year');
        $results = null;
        if ($q !== '' || $year !== '') {
            $results = ($q !== '' && mb_strlen($q) < 2) ? collect() : Student::query()
                ->where('passed_out', true)
                ->when($year !== '', fn ($w) => $w->where('pass_out_year', $year))
                ->when($q !== '', fn ($w) => $w->where(function ($x) use ($q) {
                    $x->where('gr_number', $q)->orWhere('name', 'like', "%{$q}%")->orWhere('father_name', 'like', "%{$q}%");
                }))
                ->orderByDesc('pass_out_year')->orderByRaw('CAST(gr_number AS UNSIGNED)')->limit(60)
                ->get(['id', 'gr_number', 'name', 'father_name', 'pass_out_year', 'photo_url']);
            $lc = LeavingCertificate::whereIn('gr_number', $results->pluck('gr_number'))->pluck('last_school', 'gr_number');
            $results->each(fn ($s) => $s->previous_school = $lc[$s->gr_number] ?? null);
        }

        return view('pages.alumni', ['q' => $q, 'year' => $year, 'years' => $years, 'results' => $results]);
    }
}
