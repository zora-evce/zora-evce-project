<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('ocpp_meter_values')) {
            return; // no-op
        }
        // We created with decimal(10,4) already; nothing to alter
    }

    public function down(): void {
        // no-op
    }
};
