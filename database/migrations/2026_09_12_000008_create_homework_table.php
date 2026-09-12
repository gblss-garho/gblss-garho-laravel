<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->date('date');
            $table->text('text');
            $table->string('status')->default('published');
            $table->string('subject')->nullable();
            $table->string('class_name');
            $table->string('teacher_name')->nullable();
            $table->dateTime('published_at')->nullable();
            // attachments stored as files on disk (storage/app/public), not base64 in DB
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework');
    }
};
