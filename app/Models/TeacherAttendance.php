<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    protected $table = 'teacher_attendance';

    protected $fillable = [
        'teacher_id', 'date', 'check_in_at', 'check_out_at',
        'check_in_method', 'check_out_method',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
