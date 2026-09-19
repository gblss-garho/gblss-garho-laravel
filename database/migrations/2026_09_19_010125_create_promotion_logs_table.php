<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_batch_id')->constrained('promotion_batches')->cascadeOnDelete();
            $table->string('student_id', 32);
            $table->string('previous_class')->nullable();
            $table->string('new_class')->nullable();
            $table->boolean('previous_passed_out')->default(false);
            $table->boolean('new_passed_out')->default(false);
            $table->string('previous_pass_out_year')->nullable();
            $table->string('new_pass_out_year')->nullable();
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->unsignedTinyInteger('overall_percentage')->nullable();
            $table->string('outcome');
            $table->timestamp('undone_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_logs');
    }
};
