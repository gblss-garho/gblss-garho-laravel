<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_info', function (Blueprint $t) {
            $t->string('friday_time_out_window_start')->nullable()->default('12:15');
            $t->string('friday_time_out_window_end')->nullable()->default('12:30');
        });
    }

    public function down(): void
    {
    }
};
