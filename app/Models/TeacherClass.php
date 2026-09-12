<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClass extends Model
{
    protected $fillable = ['teacher_id', 'class_name'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
