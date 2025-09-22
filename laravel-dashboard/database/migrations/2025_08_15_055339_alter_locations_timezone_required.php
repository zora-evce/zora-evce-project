<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('locations')) {
            return; // no-op
        }
        // If/when locations exists, make timezone NOT NULL in that create migration instead
    }

    public function down(): void {
        // no-op
    }
};
