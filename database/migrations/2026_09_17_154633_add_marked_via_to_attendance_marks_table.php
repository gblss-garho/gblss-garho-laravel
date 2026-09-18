<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_marks', function (Blueprint $table) {
            $table->enum('marked_via', ['manual', 'qr_code'])
                  ->default('manual')
                  ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_marks', function (Blueprint $table) {
            $table->dropColumn('marked_via');
        });
    }
};
