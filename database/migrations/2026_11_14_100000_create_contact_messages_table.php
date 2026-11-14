<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $t) {
            $t->string('id', 16)->primary();
            $t->string('name');
            $t->string('phone');
            $t->string('email')->nullable();
            $t->string('subject');
            $t->text('message');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
