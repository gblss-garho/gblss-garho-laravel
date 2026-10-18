<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_liveness_failures', function (Blueprint $t) {
            $t->id();
            $t->string('teacher_id')->index();
            $t->string('reason');
            $t->string('failure_type')->nullable();
            $t->string('session_id')->nullable();
            $t->timestamp('occurred_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_liveness_failures');
    }
};
