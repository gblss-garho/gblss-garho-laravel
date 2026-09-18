<?php

namespace App\Models;

use App\Models\Concerns\HasQrCode;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasQrCode;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'gr_number', 'name', 'father_name', 'dob', 'class', 'section',
        'address', 'guardian_phone', 'photo_url', 'admission_date',
        'passed_out', 'pass_out_year', 'status', 'results_class_override', 'qr_code',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'admission_date' => 'date',
            'passed_out' => 'boolean',
        ];
    }

    public function results()
    {
        return $this->hasMany(StudentResult::class);
    }

    public function attendanceMarks()
    {
        return $this->hasMany(AttendanceMark::class);
    }

    public function leavingCertificate()
    {
        return $this->hasOne(LeavingCertificate::class);
    }

    /** The class used for results (exam class), falling back to current class. */
    public function getResultsClassAttribute(): string
    {
        return $this->results_class_override ?: $this->class;
    }
}
