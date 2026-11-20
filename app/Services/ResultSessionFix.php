<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentResult;
use Illuminate\Support\Facades\DB;

/**
 * Results imported after the new session started carry the *current* class of each student.
 * This moves them back to the session they were really sat in: academic_year -> $to and
 * class -> the class the student was in then (one class lower; passed-out students keep theirs).
 */
class ResultSessionFix
{
    public const ORDER = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];

    /** @return array{rows:int, students:int, moves:array<string,int>} moves: "VIII → VII" => students */
    public static function preview(string $from): array
    {
        $rows = StudentResult::where('academic_year', $from)->get(['student_id', 'class']);
        $students = Student::whereIn('id', $rows->pluck('student_id')->unique())->get()->keyBy('id');
        $moves = [];
        foreach ($rows->groupBy('student_id') as $sid => $g) {
            $s = $students->get($sid);
            $old = (string) ($g->first()->class ?? '');
            $new = self::newClass($s, $old);
            $key = $old.' → '.$new;
            $moves[$key] = ($moves[$key] ?? 0) + 1;
        }
        ksort($moves);

        return ['rows' => $rows->count(), 'students' => $rows->pluck('student_id')->unique()->count(), 'moves' => $moves];
    }

    /** Class the student sat the exam in; unchanged for passed-out students or when the stored class isn't their current one. */
    public static function newClass(?Student $s, string $stored): string
    {
        if (! $s || $s->passed_out || $stored !== (string) $s->class) {
            return $stored;
        }
        $i = array_search($stored, self::ORDER, true);

        return ($i !== false && $i > 0) ? self::ORDER[$i - 1] : $stored;
    }

    /** @return int number of result rows moved; throws if $to already holds results. */
    public static function apply(string $from, string $to): int
    {
        if ($from === $to) {
            throw new \InvalidArgumentException('Naya session purane se mukhtalif hona chahiye.');
        }
        if (StudentResult::where('academic_year', $to)->exists()) {
            throw new \InvalidArgumentException("{$to} mein pehle se results maujood hain; mix hone se bachane ke liye ruk gaya.");
        }

        return DB::transaction(function () use ($from, $to) {
            $students = Student::whereIn('id', StudentResult::where('academic_year', $from)->distinct()->pluck('student_id'))->get();
            foreach ($students as $s) {
                $new = self::newClass($s, (string) $s->class);
                if ($new !== (string) $s->class) {
                    StudentResult::where('student_id', $s->id)->where('academic_year', $from)->where('class', $s->class)->update(['class' => $new]);
                }
            }

            return StudentResult::where('academic_year', $from)->update(['academic_year' => $to]);
        });
    }
}
