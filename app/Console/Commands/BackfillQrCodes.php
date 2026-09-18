<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Console\Command;

class BackfillQrCodes extends Command
{
    protected $signature = 'qr:backfill';
    protected $description = 'Assign a unique qr_code to any teacher/student records missing one';

    public function handle(): int
    {
        $teacherCount = 0;
        Teacher::whereNull('qr_code')->orWhere('qr_code', '')->each(function (Teacher $teacher) use (&$teacherCount) {
            $teacher->qr_code = Teacher::generateUniqueQrCode();
            $teacher->save();
            $teacherCount++;
        });

        $studentCount = 0;
        Student::whereNull('qr_code')->orWhere('qr_code', '')->each(function (Student $student) use (&$studentCount) {
            $student->qr_code = Student::generateUniqueQrCode();
            $student->save();
            $studentCount++;
        });

        $this->info("Backfilled qr_code for {$teacherCount} teacher(s) and {$studentCount} student(s).");

        return self::SUCCESS;
    }
}
