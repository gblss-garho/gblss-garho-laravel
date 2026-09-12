<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'date', 'class_name'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function marks()
    {
        return $this->hasMany(AttendanceMark::class);
    }
}
