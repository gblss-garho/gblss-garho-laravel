<?php

namespace App\Http\Controllers;

use App\Models\SchoolInfo;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AdminStaffIdCardController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $teachers = Teacher::when($q, function ($query, $q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('pid', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return view('admin.staff-id-cards.index', compact('teachers', 'q'));
    }

    public function pdf(Teacher $teacher)
    {
        $school = $this->schoolHeader();

        $pdf = Pdf::loadView('admin.staff-id-cards.teacher', compact('teacher', 'school'))
            ->setPaper('a4');

        $name = $teacher->pid ?: $teacher->id;

        return $pdf->stream("staff-id-card-{$name}.pdf");
    }

    private function schoolHeader(): array
    {
        $info = SchoolInfo::first();

        return [
            'name' => $info?->name ?? 'Government Boys Lower Secondary School Garho',
            'code' => $info?->semis_code ?? '404030082',
            'address' => $info?->address ?? 'Garho, Sindh',
            'motto' => $info?->motto,
        ];
    }
}
