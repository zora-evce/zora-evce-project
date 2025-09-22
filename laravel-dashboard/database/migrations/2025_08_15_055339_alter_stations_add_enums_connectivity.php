<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('stations')) {
            return; // no-op
        }
        // Already applied in create_stations_table
    }

    public function down(): void {
        // no-op
    }
};
