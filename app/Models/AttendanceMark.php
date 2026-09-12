<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceMark extends Model
{
    protected $fillable = ['attendance_record_id', 'student_id', 'status'];

    public function record()
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_record_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
