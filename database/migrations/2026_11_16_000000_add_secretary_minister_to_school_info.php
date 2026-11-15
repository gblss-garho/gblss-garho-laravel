<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_info', function (Blueprint $t) {
            foreach (['secretary', 'minister'] as $k) {
                $t->string($k.'_name')->nullable();
                $t->text($k.'_message')->nullable();
                $t->string($k.'_photo_url')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('school_info', function (Blueprint $t) {
            foreach (['secretary', 'minister'] as $k) {
                $t->dropColumn([$k.'_name', $k.'_message', $k.'_photo_url']);
            }
        });
    }
};
