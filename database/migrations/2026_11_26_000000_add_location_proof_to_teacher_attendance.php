<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** What the phone reported at check-in / check-out, so the admin can review it later. */
    public function up(): void
    {
        Schema::table('teacher_attendance', function (Blueprint $t) {
            foreach (['check_in', 'check_out'] as $p) {
                $t->decimal($p.'_lat', 10, 7)->nullable();
                $t->decimal($p.'_lng', 10, 7)->nullable();
                $t->unsignedSmallInteger($p.'_acc')->nullable();   // GPS accuracy, metres
                $t->unsignedInteger($p.'_dist')->nullable();       // metres from the school
                $t->string($p.'_ip', 45)->nullable();
                $t->string($p.'_device', 40)->nullable();          // short hash of the browser's user agent
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_attendance', function (Blueprint $t) {
            foreach (['check_in', 'check_out'] as $p) {
                $t->dropColumn([$p.'_lat', $p.'_lng', $p.'_acc', $p.'_dist', $p.'_ip', $p.'_device']);
            }
        });
    }
};
