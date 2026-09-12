<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('cnic')->nullable();
            $table->string('pid')->nullable();
            $table->date('dob')->nullable();
            $table->string('subject')->nullable();
            $table->string('designation')->nullable();
            $table->string('qualification')->nullable();
            $table->string('experience')->nullable();
            $table->date('entry_in_service')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('auth_email')->nullable();
            $table->string('portal_pin')->nullable();
            $table->text('intro_line')->nullable();
            $table->string('syllabus_plan')->nullable();
            $table->text('progress_notes')->nullable();
            $table->string('school_name')->nullable();
            $table->string('assigned_class')->nullable(); // primary/legacy single-class field
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
