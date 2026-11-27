<?php

namespace App\Console\Commands;

use App\Models\TeacherAttendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneAttendancePhotos extends Command
{
    protected $signature = 'attendance:prune-photos {--days=90 : delete photos older than this many days}';

    protected $description = 'Delete old staff attendance photos';

    public function handle(): int
    {
        $cutoff = now()->subDays(max(7, (int) $this->option('days')))->toDateString();
        $n = 0;
        TeacherAttendance::whereDate('date', '<', $cutoff)
            ->where(fn ($q) => $q->whereNotNull('check_in_photo')->orWhereNotNull('check_out_photo'))
            ->each(function (TeacherAttendance $r) use (&$n) {
                foreach (['check_in_photo', 'check_out_photo'] as $col) {
                    if ($r->{$col}) {
                        Storage::disk('local')->delete($r->{$col});
                        $r->{$col} = null;
                        $n++;
                    }
                }
                $r->save();
            });
        $this->info("Deleted {$n} photo(s) older than {$cutoff}.");

        return self::SUCCESS;
    }
}
