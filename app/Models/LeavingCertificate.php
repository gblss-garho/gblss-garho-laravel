<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeavingCertificate extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'student_id', 'gr_number', 'name', 'father_name', 'dob', 'dob_words',
        'caste', 'religion', 'place_of_birth', 'admission_date', 'admitted_class',
        'passed_class', 'year', 'progress', 'conduct', 'dues', 'reason', 'remarks',
        'last_school', 'leaving_date', 'issue_date',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'admission_date' => 'date',
            'leaving_date' => 'date',
            'issue_date' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
