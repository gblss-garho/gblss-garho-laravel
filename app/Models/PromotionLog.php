<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionLog extends Model
{
    protected $fillable = [
        'promotion_batch_id', 'student_id',
        'previous_class', 'new_class',
        'previous_passed_out', 'new_passed_out',
        'previous_pass_out_year', 'new_pass_out_year',
        'previous_status', 'new_status',
        'overall_percentage', 'outcome', 'undone_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_passed_out' => 'boolean',
            'new_passed_out' => 'boolean',
            'undone_at' => 'datetime',
        ];
    }

    public function batch()
    {
        return $this->belongsTo(PromotionBatch::class, 'promotion_batch_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
