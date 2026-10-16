<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_publish_status', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('class_name');
            $table->string('exam_type');
            $table->string('status')->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->string('published_by')->nullable();
            $table->timestamps();
            $table->unique(['class_name', 'exam_type']);
        });

        Schema::create('lookup_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('scope');
            $table->string('identifier');
            $table->dateTime('created_at')->useCurrent();
            $table->index(['scope', 'identifier', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lookup_attempts');
        Schema::dropIfExists('result_publish_status');
    }
};
