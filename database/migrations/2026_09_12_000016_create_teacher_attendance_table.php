<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_attendance', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_id', 32);
            $table->date('date');
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->string('check_in_method')->nullable();  // e.g. "face_recognition"
            $table->string('check_out_method')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
            $table->unique(['teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance');
    }
};
