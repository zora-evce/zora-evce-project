<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('connectors', function (Blueprint $t) {
            $t->id(); // bigint
            $t->foreignId('station_id')->constrained('stations')->cascadeOnDelete();
            $t->unsignedSmallInteger('connector_number')->default(1); // OCPP connectorId (1..N)
            $t->string('status', 20)->default('available');   // available, charging, faulted, ...
            $t->decimal('power_kw', 10, 4)->nullable();       // optional rated power
            $t->timestampTz('last_status_at')->nullable();

            $t->timestampsTz();
            $t->softDeletes();

            $t->unique(['station_id','connector_number']);
        });

        // DB checks
        DB::statement("ALTER TABLE connectors
            ADD CONSTRAINT connectors_status_chk
            CHECK (status IN ('available','charging','faulted','preparing','suspended_ev','suspended_evse','finishing','reserved','unavailable'))");
    }

    public function down(): void {
        DB::statement("ALTER TABLE connectors DROP CONSTRAINT IF EXISTS connectors_status_chk");
        Schema::dropIfExists('connectors');
    }
};
