<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ocpp_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('station_id')->nullable()->constrained('stations')->cascadeOnDelete();
            $table->foreignId('connector_id')->nullable()->constrained('connectors')->nullOnDelete();
            $table->string('name', 80);   // e.g. GroundFailure, ConnectorLockFailure
            $table->string('level', 10);  // info|warn|error
            $table->jsonb('detail')->nullable();
            $table->timestampTz('event_time')->useCurrent();
            $table->timestamps();

            $table->index(['station_id', 'event_time'], 'ocpp_events_station_time_idx');
        });
    }

    public function down(): void {
        Schema::dropIfExists('ocpp_events');
    }
};
