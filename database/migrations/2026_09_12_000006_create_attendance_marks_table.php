<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_marks', function (Blueprint $table) {
            $table->id();
            $table->string('attendance_record_id', 32);
            $table->string('student_id', 32);
            $table->enum('status', ['P', 'A', 'L']); // Present, Absent, Leave
            $table->timestamps();

            $table->foreign('attendance_record_id')->references('id')->on('attendance_records')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->unique(['attendance_record_id', 'student_id'], 'attendance_mark_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_marks');
    }
};
