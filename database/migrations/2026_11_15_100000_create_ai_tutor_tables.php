<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_tutor_messages', function (Blueprint $t) {
            $t->id();
            $t->string('student_id', 64)->index();
            $t->string('role', 12);
            $t->text('content');
            $t->timestamp('created_at')->nullable()->index();
        });
        Schema::create('ai_tutor_usage', function (Blueprint $t) {
            $t->string('student_id', 64);
            $t->date('usage_date');
            $t->unsignedInteger('message_count')->default(0);
            $t->primary(['student_id', 'usage_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tutor_usage');
        Schema::dropIfExists('ai_tutor_messages');
    }
};
