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

        return view('admin.id-cards.index', compact('students', 'q'));
    }

    public function pdf(Student $student)
    {
        $school = $this->schoolHeader();

        return Pdf::loadView('admin.id-cards.student', compact('student', 'school'))
            ->setPaper('a4')
            ->stream('id-card-'.($student->gr_number ?: $student->id).'.pdf');
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
        ];
    }
}
