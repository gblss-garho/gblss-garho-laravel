<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Single-row settings table for the whole school.
        Schema::create('school_info', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('urdu_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('motto')->nullable();
            $table->string('shifts')->nullable();
            $table->string('address')->nullable();
            $table->string('emis_code')->nullable();
            $table->text('about_text')->nullable();
            $table->string('school_lat')->nullable();
            $table->string('school_lng')->nullable();
            $table->string('map_location')->nullable();
            $table->string('deo_name')->nullable();
            $table->text('deo_message')->nullable();
            $table->string('deo_photo_url')->nullable();
            $table->string('teo_name')->nullable();
            $table->text('teo_message')->nullable();
            $table->string('teo_photo_url')->nullable();
            $table->string('current_exam_year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_info');
    }
};
