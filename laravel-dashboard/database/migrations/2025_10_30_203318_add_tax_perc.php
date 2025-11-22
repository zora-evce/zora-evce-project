<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tariff', function (Blueprint $table) {
            $table->string('tax_rate', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tariff', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
        });
    }
};
