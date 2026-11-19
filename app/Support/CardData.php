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
    /** The school's Sindhi motto exactly as saved in Settings; PDFs show it from a pre-rendered image. */
    public const SINDHI_MOTTO = 'علم حاصل ڪريو چاھي اوھان کي چين وڃڻو پوي. (حديث)';

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

    public static function staffId(Teacher $t, bool $masked = false): string
    {
        $year = $t->entry_in_service ? Carbon::parse($t->entry_in_service)->year : now()->year;
        $key = strtoupper($t->pid ?: substr($t->id, -6));

        return 'GBLSS-GARHO-STAFF-'.$year.'-'.($masked ? self::mask($key) : $key);
    }

    /** Hides all but the last 3 characters (public pages must not reveal PID / CNIC / phone numbers). */
    public static function mask(?string $v): string
    {
        $v = trim((string) $v);
        if ($v === '') {
            return '—';
        }
        $n = mb_strlen($v);

        return $n <= 3 ? str_repeat('*', $n) : str_repeat('*', $n - 3).mb_substr($v, -3);
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
