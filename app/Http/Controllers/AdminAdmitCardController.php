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
        return view('admin.admit-cards.index', compact('students', 'q'));
    }

    public function pdf(Student $student)
    {
        $school = $this->schoolHeader();
        return Pdf::loadView('admin.admit-cards.student', compact('student', 'school'))
            ->setPaper('a4')
            ->stream('admit-card-'.($student->gr_number ?: $student->id).'.pdf');
    }

    private function schoolHeader(): array
    {
        $info = SchoolInfo::first();
        $name = $info?->name ?: 'Government Boys Lower Secondary School Garho';
        $address = $info?->address ?: 'Taluka Ketibunder, District Thatta, Sindh, Pakistan';
        return ['name' => $name, 'address' => $address, 'exam_title' => null, 'exam_year' => $info?->current_exam_year];
    }
}
