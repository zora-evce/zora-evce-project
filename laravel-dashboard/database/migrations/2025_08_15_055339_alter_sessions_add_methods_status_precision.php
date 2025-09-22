<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // We use charging_sessions (domain) not Laravel's sessions (HTTP)
        if (!Schema::hasTable('charging_sessions')) {
            return; // no-op if table isn’t present
        }

        Schema::table('charging_sessions', function (Blueprint $t) {
            if (!Schema::hasColumn('charging_sessions','start_method')) {
                $t->string('start_method', 20)->nullable()->after('created_at'); // webhook, manual
            }
            if (!Schema::hasColumn('charging_sessions','end_method')) {
                $t->string('end_method', 20)->nullable()->after('start_method'); // webhook, manual, auto
            }
            // Precision is already 10,4 from create; no change() calls (avoid DBAL requirement)
        });

        // Add check constraints (idempotent add)
        try {
            DB::statement("ALTER TABLE charging_sessions
                ADD CONSTRAINT charging_sessions_status_chk
                CHECK (status IN ('ongoing','stopped','failed'))");
        } catch (\Throwable $e) { /* ignore if exists */ }

        try {
            DB::statement("ALTER TABLE charging_sessions
                ADD CONSTRAINT charging_sessions_start_method_chk
                CHECK (start_method IS NULL OR start_method IN ('webhook','manual'))");
        } catch (\Throwable $e) { /* ignore if exists */ }

        try {
            DB::statement("ALTER TABLE charging_sessions
                ADD CONSTRAINT charging_sessions_end_method_chk
                CHECK (end_method IS NULL OR end_method IN ('webhook','manual','auto'))");
        } catch (\Throwable $e) { /* ignore if exists */ }
    }

    public function down(): void {
        if (!Schema::hasTable('charging_sessions')) return;

        // Drop constraints if present
        try { DB::statement("ALTER TABLE charging_sessions DROP CONSTRAINT IF EXISTS charging_sessions_end_method_chk"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE charging_sessions DROP CONSTRAINT IF EXISTS charging_sessions_start_method_chk"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE charging_sessions DROP CONSTRAINT IF EXISTS charging_sessions_status_chk"); } catch (\Throwable $e) {}

        Schema::table('charging_sessions', function (Blueprint $t) {
            if (Schema::hasColumn('charging_sessions','end_method'))   { $t->dropColumn('end_method'); }
            if (Schema::hasColumn('charging_sessions','start_method')) { $t->dropColumn('start_method'); }
        });
    }
};
