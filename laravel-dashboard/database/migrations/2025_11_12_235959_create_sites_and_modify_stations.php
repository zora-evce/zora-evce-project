<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Lokasi fisik (site)
        Schema::create('sites', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('address')->nullable();
            $t->timestampsTz();
        });

        // 2) Tambahkan relasi & auth key per charger
        Schema::table('stations', function (Blueprint $t) {
            if (!Schema::hasColumn('stations', 'site_id')) {
                $t->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            }
            if (!Schema::hasColumn('stations', 'auth_key')) {
                $t->string('auth_key', 128)->nullable()->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stations', function (Blueprint $t) {
            if (Schema::hasColumn('stations', 'site_id')) {
                $t->dropForeign(['site_id']);
                $t->dropColumn('site_id');
            }
            if (Schema::hasColumn('stations', 'auth_key')) {
                $t->dropUnique(['auth_key']);
                $t->dropColumn('auth_key');
            }
        });

        Schema::dropIfExists('sites');
    }
};
