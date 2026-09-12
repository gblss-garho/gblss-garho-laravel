<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentResult extends Model
{
    protected $fillable = [
        'student_id', 'academic_year', 'class', 'exam_type', 'subject', 'score',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
