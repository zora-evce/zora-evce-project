<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
	{
	    if (!Schema::hasTable('ocpp_start_transactions')) {
	        Schema::create('ocpp_start_transactions', function (Blueprint $t) {
	            $t->id();
	            // IMPORTANT: reference charging_sessions, not sessions
	            $t->foreignId('session_id')->constrained('charging_sessions')->cascadeOnDelete();

	            $t->unsignedBigInteger('station_id')->index();
	            $t->unsignedBigInteger('connector_id')->index();

	            $t->string('id_tag')->nullable();
	            $t->bigInteger('meter_start')->nullable();
	            $t->decimal('meter_start_kwh', 10, 4)->nullable();
	            $t->timestampTz('timestamp')->nullable();
	            $t->jsonb('raw')->nullable();

	            $t->softDeletes();
	            $t->timestampsTz();
	        });
	    }
	}
    public function down(): void {
        Schema::dropIfExists('ocpp_start_transactions');
    }
};
