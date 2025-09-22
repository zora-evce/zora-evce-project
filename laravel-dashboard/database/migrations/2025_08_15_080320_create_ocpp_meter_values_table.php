<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ocpp_meter_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('station_id')->constrained('stations')->cascadeOnDelete();
            $t->foreignId('connector_id')->constrained('connectors')->cascadeOnDelete();
            $t->foreignId('session_id')->constrained('charging_sessions')->cascadeOnDelete();

            $t->timestampTz('event_time')->nullable();
            $t->jsonb('meter_value_json')->nullable();

            // parsed convenience fields (nullable)
            $t->decimal('energy_kwh', 10, 4)->nullable();
            $t->decimal('power_kw', 10, 4)->nullable();
            $t->decimal('voltage', 10, 4)->nullable();
            $t->decimal('current', 10, 4)->nullable();

            $t->timestampsTz();
            $t->softDeletes();

            $t->index(['station_id','connector_id','session_id']);
            $t->index(['event_time']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('ocpp_meter_values');
    }
};
