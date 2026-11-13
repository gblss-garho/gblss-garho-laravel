<?php

namespace App\Http\Controllers;

use App\Models\SchoolInfo;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminIdCardController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $students = Student::query()
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('gr_number', 'like', "%{$q}%");
            }))
            ->orderBy('name')
            ->limit(50)
            ->get();

        $classes = Student::query()->where('passed_out', false)->whereNotNull('class')->distinct()->orderBy('class')->pluck('class');

        return view('admin.id-cards.index', compact('students', 'q', 'classes'));
    }

    public function pdf(Student $student)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(120);
        $school = $this->schoolHeader();

        return Pdf::loadView('admin.id-cards.student', compact('student', 'school'))
            ->setPaper('a4')
            ->stream('id-card-'.($student->gr_number ?: $student->id).'.pdf')
            ->header('Cache-Control', 'no-store, max-age=0');
    }

    /** All ID cards of one class on one PDF ("Print All"). */
    public function classPdf(string $class)
    {
        @ini_set('memory_limit', '768M');
        @set_time_limit(180);

        $students = Student::query()
            ->where('passed_out', false)
            ->where('class', $class)
            ->orderByRaw('CAST(gr_number AS UNSIGNED)')
            ->get();
        abort_if($students->isEmpty(), 404);
        $school = $this->schoolHeader();

        return Pdf::loadView('admin.id-cards.class', compact('students', 'school', 'class'))
            ->setPaper('a4')
            ->stream('id-cards-class-'.$class.'.pdf')
            ->header('Cache-Control', 'no-store, max-age=0');
    }

    private function schoolHeader(): array
    {
        $info = SchoolInfo::first();

        return [
            'name' => $info?->name ?: 'Government Boys Lower Secondary School Garho',
            'code' => $info?->semis_code ?: '404030082',
            'address' => $info?->address ?: 'Taluka Ketibunder, District Thatta, Sindh, Pakistan',
            'phone' => $info?->phone,
            'motto' => $info?->motto,
            'email' => $info?->email,
        ];
    }
}
