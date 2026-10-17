<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $cols = [
            'urdu_name' => null, 'established_year' => null, 'total_classes' => null,
            'current_exam_title' => null, 'attendance_radius' => '200',
            'time_in_window_start' => '08:00', 'time_in_window_end' => '08:30',
            'time_out_window_start' => '13:15', 'time_out_window_end' => '13:30',
            'weekly_off_days' => '0,6',
        ];
        foreach ($cols as $name => $default) {
            if (! Schema::hasColumn('school_info', $name)) {
                Schema::table('school_info', function (Blueprint $t) use ($name, $default) {
                    $c = $t->string($name)->nullable();
                    if ($default !== null) {
                        $c->default($default);
                    }
                });
            }
        }
    }

    public function down(): void
    {
    }
};
