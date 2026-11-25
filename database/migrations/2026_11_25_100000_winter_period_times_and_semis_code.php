<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Winter timing 8:30-2:00: assembly 8:30-8:45, break 11:30-12:00 (after period 4).
    private const TIMES = [
        1 => ['08:45', '09:25'],
        2 => ['09:25', '10:05'],
        3 => ['10:05', '10:45'],
        4 => ['10:45', '11:30'],
        5 => ['12:00', '12:40'],
        6 => ['12:40', '13:20'],
        7 => ['13:20', '14:00'],
    ];

    public function up(): void
    {
        foreach (self::TIMES as $period => [$start, $end]) {
            $row = DB::table('timetable_periods')->where('period', $period)->first();
            if (! $row) {
                DB::table('timetable_periods')->insert(['period' => $period, 'starts_at' => $start, 'ends_at' => $end]);
            } elseif (empty($row->starts_at) && empty($row->ends_at)) {
                DB::table('timetable_periods')->where('period', $period)->update(['starts_at' => $start, 'ends_at' => $end]);
            }
        }

        DB::table('school_info')->update(['semis_code' => '404030082']);
    }

    public function down(): void
    {
    }
};
