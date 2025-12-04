<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {

            if (!Schema::hasColumn('webhook_logs', 'station_code')) {
                $table->string('station_code')->nullable()->after('type')->index();
            }

            if (!Schema::hasColumn('webhook_logs', 'connector')) {
                $table->integer('connector')->nullable()->after('station_code')->index();
            }

            if (!Schema::hasColumn('webhook_logs', 'related_id')) {
                $table->bigInteger('related_id')->nullable()->after('status')->index();
            }

            if (!Schema::hasColumn('webhook_logs', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('response')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropColumn(['station_code', 'connector', 'related_id', 'received_at']);
        });
    }
};
