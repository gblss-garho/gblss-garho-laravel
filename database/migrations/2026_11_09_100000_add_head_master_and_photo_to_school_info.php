<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $cols = [
            'school_photo_url' => 'string',
            'principal_name' => 'string',
            'principal_photo_url' => 'string',
            'principal_message' => 'text',
        ];
        foreach ($cols as $name => $type) {
            if (! Schema::hasColumn('school_info', $name)) {
                Schema::table('school_info', function (Blueprint $t) use ($name, $type) {
                    $t->{$type}($name)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
    }
};
