<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'date', 'text', 'type'];

    protected function casts(): array
    {
        return ['date' => 'datetime'];
    }
}
