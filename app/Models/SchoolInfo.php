<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolInfo extends Model
{
    protected $table = 'school_info';

    protected $fillable = [
        'total_classes', 'established_year', 'current_exam_title',
        'name', 'sindhi_name', 'email', 'phone', 'motto', 'shifts', 'address',
        'semis_code', 'about_text', 'school_lat', 'school_lng', 'map_location',
        'deo_name', 'deo_message', 'deo_photo_url',
        'teo_name', 'teo_message', 'teo_photo_url',
        'secretary_name', 'secretary_message', 'secretary_photo_url',
        'minister_name', 'minister_message', 'minister_photo_url',
        'current_exam_year', 'urdu_name', 'school_photo_url', 'about_text',
        'principal_name', 'principal_photo_url', 'principal_message',
    ];

    /** Always work with the single settings row. */
    public static function current(): self
    {
        return static::first() ?? new self();
    }
}
