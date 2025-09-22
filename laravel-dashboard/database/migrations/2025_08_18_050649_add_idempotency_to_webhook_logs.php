<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('webhook_logs', function (Blueprint $t) {
            if (!Schema::hasColumn('webhook_logs','idempotency_key')) {
                $t->string('idempotency_key',191)->nullable()->index();
            }
        });

        // Unique pair for dedupe; safe even if re-run
        try {
            Schema::table('webhook_logs', function (Blueprint $t) {
                $t->unique(['type','idempotency_key'], 'webhook_logs_type_idk_unique');
            });
        } catch (\Throwable $e) { /* already exists; ignore */ }
    }

    public function down(): void {
        Schema::table('webhook_logs', function (Blueprint $t) {
            // Drop the unique index first if it exists
            try {
                $t->dropUnique('webhook_logs_type_idk_unique');
            } catch (\Throwable $e) { /* ignore */ }

            if (Schema::hasColumn('webhook_logs','idempotency_key')) {
                $t->dropColumn('idempotency_key');
            }
        });
    }
};
