<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transactionId', 100);
            $table->string('name', 150)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->integer('station_id')->nullable();
            $table->integer('connector_id')->nullable();
            $table->string('tariff_code', 100)->nullable();
            $table->string('executed_price', 100)->nullable();
            $table->string('midtrans_order_id', 100)->nullable();
            $table->integer('email_status');
            $table->integer('wa_status');
            $table->integer('payment_status');
            $table->integer('manual_stop');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('stop_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
