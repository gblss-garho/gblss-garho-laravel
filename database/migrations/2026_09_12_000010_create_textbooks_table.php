<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('textbooks', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('class_name');
            $table->string('subject')->default('All');
            $table->string('book_name');
            $table->string('book_url');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('textbooks');
    }
};
