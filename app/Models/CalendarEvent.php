<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'date', 'type', 'title'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
