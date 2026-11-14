<?php

namespace App\Http\Controllers;

use App\Models\LeavingCertificate;
use App\Models\Student;
use Illuminate\Http\Request;

/** Old-site public tabs: Admit Card, Leaving Certificate, Alumni. */
class PublicRecordsController extends PublicLookupController
{
    public function admitShow()
    {
        return view('pages.admit-card', ['error' => null]);
    }

    public function admitDownload(Request $request)
    {
        $error = null;
        $student = $this->lookupStudent($request, $error);
        $class = trim((string) $request->input('class', ''));
        if ($student && $class !== '' && $student->class !== $class) {
            $student = null;
            $error = 'GR نمبر ۽ تاريخ پيدائش نه ملي، يا هي ڪلاس ۾ ناهي.';
        }
        if (! $student) {
            return view('pages.admit-card', ['error' => $error]);
        }

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
        $results = null;
        if ($q !== '') {
            $results = mb_strlen($q) < 2 ? collect() : Student::query()
                ->where('passed_out', true)
                ->where(function ($w) use ($q) {
                    $w->where('gr_number', $q)->orWhere('name', 'like', "%{$q}%")->orWhere('father_name', 'like', "%{$q}%");
                })
                ->orderByDesc('pass_out_year')->orderBy('name')->limit(30)
                ->get(['id', 'gr_number', 'name', 'father_name', 'pass_out_year', 'photo_url']);
            $lc = LeavingCertificate::whereIn('gr_number', $results->pluck('gr_number'))->pluck('last_school', 'gr_number');
            $results->each(fn ($s) => $s->previous_school = $lc[$s->gr_number] ?? null);
        }

        return view('pages.alumni', ['q' => $q, 'results' => $results]);
    }
}
