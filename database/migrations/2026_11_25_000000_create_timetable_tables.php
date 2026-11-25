<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Who teaches which subject to which class.
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('class_name');
            $table->string('subject');
            $table->string('teacher_id', 32);
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
            $table->unique(['class_name', 'subject']);
        });

        // Period timings, one row per period (same for every class and day).
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->unsignedTinyInteger('period')->primary();
            $table->string('starts_at', 5)->nullable();
            $table->string('ends_at', 5)->nullable();
        });

        // One cell of a class timetable. day: 1 = Monday ... 5 = Friday.
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->string('class_name');
            $table->unsignedTinyInteger('day');
            $table->unsignedTinyInteger('period');
            $table->string('subject');
            $table->string('teacher_id', 32)->nullable();
            $table->timestamps();

            $table->unique(['class_name', 'day', 'period']);
            $table->index(['teacher_id', 'day', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
        Schema::dropIfExists('timetable_periods');
        Schema::dropIfExists('teaching_assignments');
    }
};
