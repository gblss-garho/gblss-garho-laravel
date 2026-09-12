<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaving_certificates', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('student_id', 32)->nullable(); // link back if the student record still exists
            $table->string('gr_number')->nullable();
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->date('dob')->nullable();
            $table->string('dob_words')->nullable();
            $table->string('caste')->nullable();
            $table->string('religion')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('admission_date')->nullable();
            $table->string('admitted_class')->nullable();
            $table->string('passed_class')->nullable();
            $table->string('year')->nullable(); // academic year e.g. 2024-2025
            $table->string('progress')->nullable();
            $table->string('conduct')->nullable();
            $table->string('dues')->nullable();
            $table->string('reason')->nullable();
            $table->string('remarks')->nullable();
            $table->string('last_school')->nullable();
            $table->date('leaving_date')->nullable();
            $table->date('issue_date')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaving_certificates');
    }
};
