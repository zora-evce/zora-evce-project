<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rfid_card_connectors', function (Blueprint $table) {
            $table->id();
            // id_tag sama seperti di rfid_cards
            $table->string('id_tag');
            // station_code = kode stasiun OCPP (cp_id = ZoraS1 / ZoraN1 / dsb)
            $table->string('station_code');
            // connector (misal 1, 2, 3)
            $table->unsignedInteger('connector');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // kombinasi unik: 1 kartu hanya boleh sekali per station+connector
            $table->unique(['id_tag', 'station_code', 'connector'], 'uniq_card_station_connector');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_card_connectors');
    }
};
