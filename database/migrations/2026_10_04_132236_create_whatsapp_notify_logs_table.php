<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_notify_logs', function (Blueprint $table) {
            $table->id();$table->string('student_id', 32);
            $table->date('date');
            $table->string('kind', 60);
            $table->unique(['student_id', 'date', 'kind']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notify_logs');
    }
};
