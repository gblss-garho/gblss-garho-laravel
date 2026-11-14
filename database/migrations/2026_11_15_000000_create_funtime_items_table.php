<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funtime_items', function (Blueprint $t) {
            $t->string('id', 16)->primary();
            $t->string('type', 20);
            $t->string('title');
            $t->string('image_url', 500)->nullable();
            $t->string('media_url', 500)->nullable();
            $t->text('content')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funtime_items');
    }
};
