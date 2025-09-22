<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
	    if (!Schema::hasTable('webhook_logs')) {
	        Schema::create('webhook_logs', function (Blueprint $t) {
	            $t->id();
	            $t->string('type', 50); // midtrans.payment, ocpp.start, ocpp.stop, etc.
	            $t->unsignedBigInteger('related_id')->nullable(); // session_id or payment id
	            $t->jsonb('payload')->nullable();
	            $t->integer('status_code')->nullable();
	            $t->timestampTz('received_at')->useCurrent();
	            $t->jsonb('response')->nullable();
	            $t->softDeletes();
	            $t->timestampsTz();
	            $t->index(['type', 'related_id']);
	        });
	    }
	}
	    public function down(): void
	{
	    Schema::dropIfExists('webhook_logs');
	}

};
