<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite has no length limit; MySQL needs a bigger column for base64 photos.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }
        Schema::table('students', fn (Blueprint $t) => $t->mediumText('photo_url')->nullable()->change());
        Schema::table('teachers', fn (Blueprint $t) => $t->mediumText('photo_url')->nullable()->change());
    }

    public function down(): void
    {
    }
};
