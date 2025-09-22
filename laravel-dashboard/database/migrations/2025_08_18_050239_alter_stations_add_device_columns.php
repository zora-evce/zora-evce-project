<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('stations', function (Blueprint $t) {
            if (!Schema::hasColumn('stations','vendor')) {
                $t->string('vendor',100)->nullable();
            }
            if (!Schema::hasColumn('stations','model')) {
                $t->string('model',100)->nullable();
            }
            if (!Schema::hasColumn('stations','firmware_version')) {
                $t->string('firmware_version',100)->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('stations', function (Blueprint $t) {
            if (Schema::hasColumn('stations','firmware_version')) {
                $t->dropColumn('firmware_version');
            }
            if (Schema::hasColumn('stations','model')) {
                $t->dropColumn('model');
            }
            if (Schema::hasColumn('stations','vendor')) {
                $t->dropColumn('vendor');
            }
        });
    }
};
