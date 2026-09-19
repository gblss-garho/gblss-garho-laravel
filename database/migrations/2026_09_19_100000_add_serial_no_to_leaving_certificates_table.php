<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaving_certificates', function (Blueprint $table) {
            $table->unsignedInteger('serial_no')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('leaving_certificates', function (Blueprint $table) {
            $table->dropUnique(['serial_no']);
            $table->dropColumn('serial_no');
        });
    }
};
