<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $t) {
            $t->string('id', 20)->primary();
            $t->string('title');
            $t->string('category', 40)->default('Other');
            $t->text('description')->nullable();
            $t->string('audience', 10)->default('staff'); // public | staff
            $t->string('file_path');
            $t->string('file_name');
            $t->string('mime', 120)->nullable();
            $t->unsignedBigInteger('size')->default(0);
            $t->string('uploaded_by')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
