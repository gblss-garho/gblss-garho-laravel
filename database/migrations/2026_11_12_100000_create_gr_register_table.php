<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gr_register', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('reg_no')->unique();
            $t->string('name');
            $t->string('father_name')->nullable();
            $t->string('religion', 40)->nullable();
            $t->string('surname', 80)->nullable();
            $t->date('dob')->nullable();
            $t->string('address')->nullable();
            $t->string('last_institution')->nullable();
            $t->date('admission_date')->nullable();
            $t->string('admission_class', 20)->nullable();
            $t->date('removal_date')->nullable();
            $t->string('removal_class', 20)->nullable();
            $t->string('removal_cause')->nullable();
            $t->string('remarks')->nullable();
            $t->string('status', 40)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gr_register');
    }
};
