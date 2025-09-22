<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('tariffs')) {
            return; // no-op: table not present in this app
        }
        // If you add tariffs later, handle precision there
    }

    public function down(): void {
        // no-op
    }
};
