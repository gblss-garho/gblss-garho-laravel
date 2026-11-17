<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Manual status change (Active / LC Issued / Left / Passed out), kept in sync in students + gr_register. */
class StudentStatusService
{
    public const STATUSES = [
        'active' => 'Active',
        'lc' => 'LC Issued',
        'left' => 'Left / Removed',
        'passed' => 'Passed out',
    ];

    public const LABELS = ['lc' => 'LC Issued', 'left' => 'Left', 'passed' => 'Passed out'];

    public static function rules(): array
    {
        return [
            'status_choice' => ['required', 'in:active,lc,left,passed'],
            'removal_date' => ['nullable', 'date'],
            'removal_class' => ['nullable', 'string', 'max:20'],
            'removal_cause' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public static function choiceFor(?Student $s, ?object $gr = null): string
    {
        if ($s && ! $s->passed_out) {
            return 'active';
        }
        $label = mb_strtolower((string) ($s->status ?? $gr->status ?? ''));
        if (str_contains($label, 'lc')) {
            return 'lc';
        }
        if (str_contains($label, 'pass')) {
            return 'passed';
        }

        return ($s || $gr) ? 'left' : 'active';
    }

    /** @param array{status_choice:string,removal_date?:?string,removal_class?:?string,removal_cause?:?string,remarks?:?string} $d */
    public function apply(int $regNo, ?Student $student, array $d): void
    {
        $choice = $d['status_choice'];
        $active = $choice === 'active';
        $label = $active ? null : self::LABELS[$choice];
        $date = $active ? null : ($d['removal_date'] ?? now()->toDateString());

        DB::transaction(function () use ($student, $regNo, $d, $active, $label, $date) {
            if ($student) {
                $student->update([
                    'passed_out' => $active ? 0 : 1,
                    'status' => $label,
                    'pass_out_year' => $active ? null : (int) substr((string) $date, 0, 4),
                ]);
            }
            if (! Schema::hasTable('gr_register') || $regNo <= 0) {
                return;
            }
            $row = [
                'removal_date' => $date,
                'removal_class' => $active ? null : ($d['removal_class'] ?? $student?->class),
                'removal_cause' => $active ? null : ($d['removal_cause'] ?? null),
                'remarks' => $d['remarks'] ?? null,
                'status' => $label,
                'updated_at' => now(),
            ];
            if (DB::table('gr_register')->where('reg_no', $regNo)->exists()) {
                DB::table('gr_register')->where('reg_no', $regNo)->update($row);
            } elseif ($student) {
                DB::table('gr_register')->insert($row + [
                    'reg_no' => $regNo, 'name' => $student->name, 'father_name' => $student->father_name,
                    'dob' => $student->dob, 'address' => $student->address,
                    'admission_date' => $student->admission_date, 'created_at' => now(),
                ]);
            }
        });
    }
}
