<?php

namespace App\Http\Controllers;

use App\Models\LeavingCertificate;
use App\Models\SchoolInfo;
use App\Models\Student;
use App\Services\DateInWords;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminLeavingCertificateController extends Controller
{
    public function index(): View
    {
        $certificates = LeavingCertificate::orderByDesc('serial_no')->get();

        $issuedStudentIds = LeavingCertificate::whereNotNull('student_id')->pluck('student_id');

        $eligible = Student::where('passed_out', true)
            ->whereNotIn('id', $issuedStudentIds)
            ->orderBy('name')
            ->get();

        return view('admin.leaving-certificates.index', compact('certificates', 'eligible'));
    }

    public function create(Student $student): View|RedirectResponse
    {
        if (LeavingCertificate::where('student_id', $student->id)->exists()) {
            return redirect()->route('admin.leaving-certificates.index')
                ->with('status', 'Is student ka leaving certificate pehle se bana hua hai.');
        }

        $defaults = [
            'gr_number' => $student->gr_number,
            'name' => $student->name,
            'father_name' => $student->father_name,
            'dob' => $student->dob?->format('Y-m-d'),
            'dob_words' => $student->dob ? (new DateInWords())->format($student->dob) : '',
            'admission_date' => $student->admission_date?->format('Y-m-d'),
            'passed_class' => $student->class,
            'year' => $student->pass_out_year,
            'leaving_date' => now()->toDateString(),
            'issue_date' => now()->toDateString(),
            'progress' => 'Good',
            'conduct' => 'Good',
            'dues' => 'Nil',
            'remarks' => 'L.C Issued',
            'reason' => $student->class ? 'Class ' . $student->class . ' Passed' : '',
        ];

        return view('admin.leaving-certificates.create', compact('student', 'defaults'));
    }

    public function store(Request $request, Student $student): RedirectResponse
    {
        if (LeavingCertificate::where('student_id', $student->id)->exists()) {
            return redirect()->route('admin.leaving-certificates.index')
                ->with('status', 'Is student ka leaving certificate pehle se bana hua hai.');
        }

        $text = ['nullable', 'string', 'max:255'];
        $date = ['nullable', 'date'];

        $data = $request->validate([
            'gr_number' => $text,
            'name' => ['required', 'string', 'max:255'],
            'father_name' => $text,
            'dob' => $date,
            'dob_words' => $text,
            'caste' => $text,
            'religion' => $text,
            'place_of_birth' => $text,
            'admission_date' => $date,
            'admitted_class' => $text,
            'passed_class' => $text,
            'year' => $text,
            'progress' => $text,
            'conduct' => $text,
            'dues' => $text,
            'reason' => $text,
            'remarks' => $text,
            'last_school' => $text,
            'leaving_date' => $date,
            'issue_date' => $date,
        ]);

        $certificate = DB::transaction(function () use ($data, $student) {
            return LeavingCertificate::create($data + [
                'id' => str_replace('-', '', (string) Str::uuid()),
                'serial_no' => ((int) LeavingCertificate::max('serial_no')) + 1,
                'student_id' => $student->id,
            ]);
        });

        return redirect()->route('admin.leaving-certificates.index')
            ->with('status', sprintf('Leaving certificate LC-%04d save ho gaya.', $certificate->serial_no));
    }

    public function pdf(LeavingCertificate $certificate)
    {
        $pdf = Pdf::loadView('admin.leaving-certificates.pdf', [
            'c' => $certificate,
            'school' => $this->schoolHeader(),
        ])->setPaper('a4');

        return $pdf->stream(sprintf('LC-%04d.pdf', $certificate->serial_no));
    }

    private function schoolHeader(): array
    {
        $info = SchoolInfo::first();

        return [
            'name' => $info?->name ?: 'Government Boys Lower Secondary School Garho',
            'code' => $info?->semis_code ?: '404030082',
            'address' => $info?->address ?: 'Taluka Ketibunder, District Thatta, Sindh, Pakistan',
        ];
    }
}
