<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolInfo extends Model
{
    protected $table = 'school_info';

    protected $fillable = [
        'name', 'urdu_name', 'email', 'phone', 'motto', 'shifts', 'address',
        'emis_code', 'about_text', 'school_lat', 'school_lng', 'map_location',
        'deo_name', 'deo_message', 'deo_photo_url',
        'teo_name', 'teo_message', 'teo_photo_url',
        'current_exam_year',
    ];

    /** Always work with the single settings row. */
    public static function current(): self
    {
        return static::first() ?? new self();
    }
}
