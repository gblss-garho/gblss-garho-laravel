<?php

namespace App\Console\Commands;

use App\Models\PromotionBatch;
use App\Models\Student;
use Illuminate\Console\Command;

class PromotionUndo extends Command
{
    protected $signature = 'promotion:undo {batch? : Promotion batch ID (defaults to the latest not-yet-undone batch)} {--student= : Only undo one student (by student id) within the batch, leaving the rest of the batch as-is}';
    protected $description = 'Undo a promotion batch (or a single student within it), restoring previous class/passed_out/pass_out_year/status';

    public function handle(): int
    {
        $batch = $this->argument('batch')
            ? PromotionBatch::find($this->argument('batch'))
            : PromotionBatch::whereNull('undone_at')->latest('run_at')->first();

        if (! $batch) {
            $this->error('No matching promotion batch found (or nothing left to undo).');
            return self::FAILURE;
        }

        $studentFilter = $this->option('student');

        $logs = $batch->logs()->whereNull('undone_at')
            ->when($studentFilter, fn ($q) => $q->where('student_id', $studentFilter))
            ->get();

        if ($logs->isEmpty()) {
            $this->warn('Nothing to undo for that batch/student (already undone or no matching log).');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($logs as $log) {
            $student = Student::find($log->student_id);
            if ($student) {
                $student->class = $log->previous_class;
                $student->passed_out = $log->previous_passed_out;
                $student->pass_out_year = $log->previous_pass_out_year;
                $student->status = $log->previous_status;
                $student->save();
            }

            $log->undone_at = now();
            $log->save();
            $count++;
        }

        if (! $studentFilter && $batch->logs()->whereNull('undone_at')->doesntExist()) {
            $batch->undone_at = now();
            $batch->save();
        }

        $this->info("Undone {$count} student log(s) for batch #{$batch->id} ({$batch->academic_year}).");

        return self::SUCCESS;
    }
}
