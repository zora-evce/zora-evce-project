<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stations', function (Blueprint $t) {
            $t->id(); // bigint
            $t->string('code')->unique();            // e.g., Zora1 (ChargePointIdentity)
            $t->string('name')->nullable();
            $t->string('brand')->nullable();

            // OCPP-facing operational flags
            $t->string('status', 20)->default('available');           // available, charging, faulted, ...
            $t->string('connectivity_status', 10)->default('offline'); // online/offline
            $t->timestampTz('last_heartbeat_at')->nullable();

            // convenience (kept in sync later via trigger when connectors table exists)
            $t->unsignedInteger('connectors_count')->default(0)->comment('Derived from connectors');

            $t->timestampsTz();
            $t->softDeletes();
        });

        // DB-level validation
        DB::statement("ALTER TABLE stations
            ADD CONSTRAINT stations_status_chk
            CHECK (status IN ('available','charging','faulted','preparing','suspended_ev','suspended_evse','finishing','reserved','unavailable'))");
        DB::statement("ALTER TABLE stations
            ADD CONSTRAINT stations_connectivity_status_chk
            CHECK (connectivity_status IN ('online','offline'))");
    }

    public function down(): void {
        DB::statement("ALTER TABLE stations DROP CONSTRAINT IF EXISTS stations_connectivity_status_chk");
        DB::statement("ALTER TABLE stations DROP CONSTRAINT IF EXISTS stations_status_chk");
        Schema::dropIfExists('stations');
    }
};
