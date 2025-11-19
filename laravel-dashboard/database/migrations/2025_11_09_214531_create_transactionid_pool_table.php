<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactionid_pool', function (Blueprint $table) {
            $table->id();
            $table->string("transactionId", 200);
            $table->integer("id_transaction");
            $table->integer("station_id");
            $table->integer("connector_id");
            $table->integer("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactionid_pool');
    }
};
