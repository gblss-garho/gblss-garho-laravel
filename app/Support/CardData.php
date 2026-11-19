<?php

namespace App\Support;

use App\Models\SchoolInfo;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\QrCodeService;
use App\Services\SchoolScheduleService;
use Carbon\Carbon;

/** Shared data for the on-page ID / admit card previews. */
class CardData
{
    public static function school(): array
    {
        $info = SchoolInfo::first();

        return [
            'name' => $info?->name ?: 'Government Boys Lower Secondary School Garho',
            'code' => $info?->semis_code ?: '404030082',
            'address' => $info?->address ?: 'Taluka Ketibunder, District Thatta, Sindh, Pakistan',
            'phone' => $info?->phone, 'email' => $info?->email, 'motto' => $info?->motto,
            'exam_title' => $info?->current_exam_title, 'exam_year' => $info?->current_exam_year,
        ];
    }

    public static function studentId(Student $s): string
    {
        $gr = str_pad(trim((string) $s->gr_number), 3, '0', STR_PAD_LEFT);
        $year = $s->admission_date ? Carbon::parse($s->admission_date)->year : now()->year;

        return 'GBLSS-GARHO-'.$year.'-'.$gr;
    }

    public static function staffId(Teacher $t): string
    {
        $year = $t->entry_in_service ? Carbon::parse($t->entry_in_service)->year : now()->year;

        return 'GBLSS-GARHO-STAFF-'.$year.'-'.strtoupper($t->pid ?: substr($t->id, -6));
    }

    public static function qr($model): string
    {
        return app(QrCodeService::class)->dataUri($model->ensureQrCode(), 300, 1);
    }

    public static function session(): string
    {
        return app(SchoolScheduleService::class)->academicYearFor(now());
    }
}
