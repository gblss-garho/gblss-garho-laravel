<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'name', 'rating', 'message'];
}
