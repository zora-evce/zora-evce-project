<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('email_summaries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('session_id')->constrained('charging_sessions')->cascadeOnDelete();
            $t->string('recipient');
            $t->timestampTz('sent_at')->nullable();
            $t->string('status', 20)->default('pending'); // pending, sent, failed
            $t->text('error')->nullable();
            $t->softDeletes();
            $t->timestampsTz();
        });
    }
    public function down(): void {
        Schema::dropIfExists('email_summaries');
    }
};
