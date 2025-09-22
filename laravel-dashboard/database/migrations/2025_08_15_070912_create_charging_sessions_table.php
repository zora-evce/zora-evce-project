<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('charging_sessions', function (Blueprint $t) {
            $t->id(); // bigint PK
            $t->unsignedBigInteger('station_id')->nullable();
            $t->unsignedBigInteger('connector_id')->nullable();
            $t->string('status', 20)->default('ongoing'); // ongoing, stopped, failed
            $t->decimal('total_energy_kwh', 10, 4)->nullable();
            $t->decimal('energy_cost', 10, 4)->nullable();
            $t->decimal('additional_cost', 10, 4)->nullable();
            $t->decimal('total_cost', 10, 4)->nullable();
            $t->timestampsTz();
            $t->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('charging_sessions');
    }
};
