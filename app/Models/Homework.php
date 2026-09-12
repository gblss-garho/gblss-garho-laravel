<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    protected $table = 'homework';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'date', 'text', 'status', 'subject', 'class_name', 'teacher_name',
        'published_at', 'attachment_path', 'attachment_name', 'attachment_type',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'published_at' => 'datetime',
        ];
    }
}
