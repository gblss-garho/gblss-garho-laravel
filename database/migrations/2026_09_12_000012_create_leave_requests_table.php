<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('teacher_id', 32)->nullable();
            $table->string('teacher_name')->nullable();
            $table->date('date');
            $table->text('reason');
            $table->dateTime('submitted_at')->nullable();
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
