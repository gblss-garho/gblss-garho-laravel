<?php

namespace App\Console\Commands;

use App\Models\PromotionBatch;
use App\Models\PromotionLog;
use App\Models\Student;
use App\Models\StudentResult;
use App\Services\SchoolScheduleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PromoteStudents extends Command
{
    protected $signature = 'promotion:run';
    protected $description = 'Automatically promote students to the next class based on their annual result (runs every 31 March)';

    private const PASS_PERCENTAGE = 33;

    private const CLASS_ORDER = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];

    public function handle(SchoolScheduleService $schedule): int
    {
        $academicYear = $schedule->academicYearFor(now());

        $batch = PromotionBatch::create([
            'academic_year' => $academicYear,
            'run_at' => now(),
        ]);

        $promoted = 0;
        $passedOut = 0;
        $skippedMissing = 0;
        $skippedFailed = 0;
        $skippedUnknownClass = 0;

        Student::where('passed_out', false)->each(function (Student $student) use (
            $batch, $academicYear,
            &$promoted, &$passedOut, &$skippedMissing, &$skippedFailed, &$skippedUnknownClass
        ) {
            $classIndex = array_search($student->class, self::CLASS_ORDER, true);

            if ($classIndex === false) {
                $this->logOutcome($batch, $student, 'skipped_unknown_class', null);
                $skippedUnknownClass++;
                return;
            }

            $results = StudentResult::where('student_id', $student->id)
                ->where('academic_year', $academicYear)
                ->where('exam_type', 'annual')
                ->get();

            if ($results->isEmpty()) {
                $this->logOutcome($batch, $student, 'skipped_missing_result', null);
                $skippedMissing++;
                return;
            }

            $overallPercentage = (int) round($results->avg('score'));
            $lowestSubject = (int) $results->min('score');
            $passed = $overallPercentage >= self::PASS_PERCENTAGE && $lowestSubject >= self::PASS_PERCENTAGE;

            if (! $passed) {
                $this->logOutcome($batch, $student, 'skipped_did_not_meet_criteria', $overallPercentage);
                $skippedFailed++;
                return;
            }

            $previousClass = $student->class;
            $previousPassedOut = $student->passed_out;
            $previousPassOutYear = $student->pass_out_year;
            $previousStatus = $student->status;

            $isLastClass = $classIndex === count(self::CLASS_ORDER) - 1;

            if ($isLastClass) {
                $student->passed_out = true;
                $student->pass_out_year = $academicYear;
                $student->status = 'LC Issued';
                $outcome = 'passed_out';
                $passedOut++;
            } else {
                $student->class = self::CLASS_ORDER[$classIndex + 1];
                $outcome = 'promoted';
                $promoted++;
            }

            $student->save();

            PromotionLog::create([
                'promotion_batch_id' => $batch->id,
                'student_id' => $student->id,
                'previous_class' => $previousClass,
                'new_class' => $student->class,
                'previous_passed_out' => $previousPassedOut,
                'new_passed_out' => $student->passed_out,
                'previous_pass_out_year' => $previousPassOutYear,
                'new_pass_out_year' => $student->pass_out_year,
                'previous_status' => $previousStatus,
                'new_status' => $student->status,
                'overall_percentage' => $overallPercentage,
                'outcome' => $outcome,
            ]);
        });

        $this->info("Promotion batch #{$batch->id} ({$academicYear}) complete: "
            . "{$promoted} promoted, {$passedOut} passed out, "
            . "{$skippedMissing} skipped (missing result), "
            . "{$skippedFailed} skipped (did not meet criteria), "
            . "{$skippedUnknownClass} skipped (unknown class).");

        return self::SUCCESS;
    }

    private function logOutcome(PromotionBatch $batch, Student $student, string $outcome, ?int $overallPercentage): void
    {
        PromotionLog::create([
            'promotion_batch_id' => $batch->id,
            'student_id' => $student->id,
            'previous_class' => $student->class,
            'new_class' => $student->class,
            'previous_passed_out' => $student->passed_out,
            'new_passed_out' => $student->passed_out,
            'previous_pass_out_year' => $student->pass_out_year,
            'new_pass_out_year' => $student->pass_out_year,
            'previous_status' => $student->status,
            'new_status' => $student->status,
            'overall_percentage' => $overallPercentage,
            'outcome' => $outcome,
        ]);
    }
}
