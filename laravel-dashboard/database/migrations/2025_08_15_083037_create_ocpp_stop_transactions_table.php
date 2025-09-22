<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ocpp_stop_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('session_id')->constrained('charging_sessions')->cascadeOnDelete();
            $t->foreignId('station_id')->constrained('stations')->cascadeOnDelete();
            $t->foreignId('connector_id')->constrained('connectors')->cascadeOnDelete();

            $t->timestampTz('event_time')->nullable();
            $t->string('reason', 40)->nullable(); // Local, Remote, DeAuthorized, EVDisconnected, HardReset, etc.
            $t->bigInteger('meter_stop')->nullable();         // raw vendor meter
            $t->decimal('meter_stop_kwh', 10, 4)->nullable(); // normalized kWh
            $t->decimal('total_energy_kwh', 10, 4)->nullable();
            $t->decimal('total_cost', 10, 4)->nullable();

            $t->jsonb('raw')->nullable(); // full OCPP payload
            $t->timestampsTz();
            $t->softDeletes();

            $t->index(['station_id','connector_id','session_id']);
            $t->index(['event_time']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('ocpp_stop_transactions');
    }
};
