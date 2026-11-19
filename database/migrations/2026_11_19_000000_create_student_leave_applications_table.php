<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_leave_applications', function (Blueprint $table) {
            $table->string('id', 12)->primary();
            $table->string('student_id', 32)->index();
            $table->string('student_name');
            $table->string('class_name')->nullable();
            $table->date('leave_date');
            $table->string('purpose', 40);
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('status', 12)->default('pending'); // pending | approved | rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_leave_applications');
    }
};
