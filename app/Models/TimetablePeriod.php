<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetablePeriod extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'period';
    public $incrementing = false;
    protected $fillable = ['period', 'starts_at', 'ends_at'];
}
