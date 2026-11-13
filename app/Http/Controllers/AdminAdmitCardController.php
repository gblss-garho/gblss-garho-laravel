<?php

namespace App\Http\Controllers;

use App\Models\SchoolInfo;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAdmitCardController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $students = Student::query()
            ->where('passed_out', false)
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('gr_number', 'like', "%{$q}%");
            }))
            ->orderBy('class')->orderByRaw('CAST(gr_number AS UNSIGNED)')
            ->limit(50)->get();
        $classes = Student::query()->where('passed_out', false)->whereNotNull('class')->distinct()->orderBy('class')->pluck('class');

        return view('admin.admit-cards.index', compact('students', 'q', 'classes'));
    }

    public function pdf(Student $student)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(120);
        $school = $this->schoolHeader();
        return Pdf::loadView('admin.admit-cards.student', compact('student', 'school'))
            ->setPaper('a4')
            ->stream('admit-card-'.($student->gr_number ?: $student->id).'.pdf');
    }

    /** All admit cards of one class on one PDF (old site: "Print All"). */
    public function classPdf(string $class)
    {
        @ini_set('memory_limit', '768M');
        @set_time_limit(180);
        $school = $this->schoolHeader();
        $students = Student::query()
            ->where('passed_out', false)
            ->where('class', $class)
            ->orderByRaw('CAST(gr_number AS UNSIGNED)')
            ->get();
        abort_if($students->isEmpty(), 404);

        return Pdf::loadView('admin.admit-cards.class', compact('students', 'school', 'class'))
            ->setPaper('a4')
            ->stream('admit-cards-class-'.$class.'.pdf');
    }

    private function schoolHeader(): array
    {
        $info = SchoolInfo::first();
        $name = $info?->name ?: 'Government Boys Lower Secondary School Garho';
        $address = $info?->address ?: 'Taluka Ketibunder, District Thatta, Sindh, Pakistan';
        return ['name' => $name, 'address' => $address, 'exam_title' => $info?->current_exam_title, 'exam_year' => $info?->current_exam_year];
    }
}
