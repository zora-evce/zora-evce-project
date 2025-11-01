<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('connectors', function (Blueprint $table) {
            $table->string('idTag', 100)->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('connectors', function (Blueprint $table) {
            $table->dropColumn('idTag');
        });
    }
};
