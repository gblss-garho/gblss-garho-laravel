<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('id', 32)->primary(); // preserves original Supabase id
            $table->string('gr_number')->nullable()->index();
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->date('dob')->nullable();
            $table->string('class');
            $table->string('section')->nullable();
            $table->string('address')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('photo_url')->nullable();
            $table->date('admission_date')->nullable();
            $table->boolean('passed_out')->default(false);
            $table->string('pass_out_year')->nullable();
            $table->string('status')->nullable(); // e.g. "LC Issued"
            $table->string('results_class_override')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
