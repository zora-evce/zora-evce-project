<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariff', function (Blueprint $table) {
            $table->id('tariff_id');
            $table->string('tariff_code', 50)->unique();
            $table->string('tariff_name', 100);
            $table->string('tariff_type', 100);
            $table->string('tariff_value', 100);
            $table->string('tariff_price', 100);
            $table->string('active', 100);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariff');
    }
};

