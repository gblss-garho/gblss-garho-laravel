<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionBatch extends Model
{
    protected $fillable = ['academic_year', 'run_at', 'undone_at'];

    protected function casts(): array
    {
        return [
            'run_at' => 'datetime',
            'undone_at' => 'datetime',
        ];
    }

    public function logs()
    {
        return $this->hasMany(PromotionLog::class);
    }
}
