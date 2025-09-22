<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('session_id')->constrained('charging_sessions')->cascadeOnDelete();
            $t->string('order_id')->index();
            $t->string('transaction_id')->index();
            $t->string('payment_type', 50);
            $t->decimal('amount', 10, 4);
            $t->string('payment_status', 30);
            $t->timestampTz('transaction_time')->nullable();
            $t->jsonb('raw_payload')->nullable();
            $t->softDeletes();
            $t->timestampsTz();
            $t->unique(['order_id','transaction_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('payments');
    }
};
