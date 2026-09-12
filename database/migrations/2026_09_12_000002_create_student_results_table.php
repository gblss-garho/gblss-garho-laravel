<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_results', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 32);
            $table->string('academic_year')->nullable(); // e.g. "2025-2026", null = current
            $table->string('class')->nullable(); // class at time of this result set
            $table->string('exam_type'); // annual | midterm | monthly | classtest
            $table->string('subject');
            $table->unsignedTinyInteger('score')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->unique(['student_id', 'academic_year', 'exam_type', 'subject'], 'student_result_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_results');
    }
};
