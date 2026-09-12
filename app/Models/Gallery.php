<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $table = 'gallery';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'url', 'caption'];
}
