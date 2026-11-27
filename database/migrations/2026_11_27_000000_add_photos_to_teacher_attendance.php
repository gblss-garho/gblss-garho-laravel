<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Path of the small photo taken by the camera at check-in / check-out (private storage, admin only). */
    public function up(): void
    {
        Schema::table('teacher_attendance', function (Blueprint $t) {
            $t->string('check_in_photo', 150)->nullable();
            $t->string('check_out_photo', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_attendance', function (Blueprint $t) {
            $t->dropColumn(['check_in_photo', 'check_out_photo']);
        });
    }
};
