<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_info', function (Blueprint $table) {
            $table->renameColumn('emis_code', 'semis_code');
            $table->renameColumn('urdu_name', 'sindhi_name');
        });
    }

    public function down(): void
    {
        Schema::table('school_info', function (Blueprint $table) {
            $table->renameColumn('semis_code', 'emis_code');
            $table->renameColumn('sindhi_name', 'urdu_name');
        });
    }
};
