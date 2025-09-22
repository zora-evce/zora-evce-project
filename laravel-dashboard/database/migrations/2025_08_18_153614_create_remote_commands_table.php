<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('remote_commands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('station_id')->constrained('stations')->cascadeOnDelete();
            $table->foreignId('connector_id')->nullable()->constrained('connectors')->nullOnDelete();
            $table->string('command', 40); // RemoteStartTransaction | RemoteStopTransaction
            $table->jsonb('payload')->nullable();
            $table->string('status', 20)->default('pending'); // pending|sent|ack|error|cancelled
            $table->timestamps();

            $table->index(['station_id', 'status'], 'remote_commands_station_status_idx');
        });
    }

    public function down(): void {
        Schema::dropIfExists('remote_commands');
    }
};
