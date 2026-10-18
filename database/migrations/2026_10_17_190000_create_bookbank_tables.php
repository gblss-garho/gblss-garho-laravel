<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookbank_catalog', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->string('class')->nullable();
            $t->string('subject')->nullable();
            $t->string('book_title')->nullable();
            $t->integer('available_qty')->default(0);
            $t->integer('issued_qty')->default(0);
            $t->timestamps();
        });

        Schema::create('bookbank_issues', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->string('student_id')->nullable();
            $t->string('student_name')->nullable();
            $t->string('class')->nullable();
            $t->string('book_title')->nullable();
            $t->string('issue_date')->nullable();
            $t->string('return_date')->nullable();
            $t->string('condition')->nullable();
            $t->string('status')->default('Issued');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookbank_issues');
        Schema::dropIfExists('bookbank_catalog');
    }
};
