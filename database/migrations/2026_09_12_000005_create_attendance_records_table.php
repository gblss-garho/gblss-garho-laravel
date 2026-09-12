<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->date('date');
            $table->string('class_name');
            $table->timestamps();

            $table->unique(['date', 'class_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
