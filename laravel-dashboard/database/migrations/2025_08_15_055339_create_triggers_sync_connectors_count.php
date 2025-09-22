<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared("
        -- function: refresh connectors_count on stations
        CREATE OR REPLACE FUNCTION refresh_connectors_count() RETURNS TRIGGER AS $$
        BEGIN
            UPDATE stations s
            SET connectors_count = (SELECT COUNT(*) FROM connectors c WHERE c.station_id = s.id)
            WHERE s.id = COALESCE(NEW.station_id, OLD.station_id);
            RETURN NEW;
        END; $$ LANGUAGE plpgsql;

        -- after INSERT on connectors
        DROP TRIGGER IF EXISTS connectors_count_after_ins ON connectors;
        CREATE TRIGGER connectors_count_after_ins
        AFTER INSERT ON connectors
        FOR EACH ROW EXECUTE FUNCTION refresh_connectors_count();

        -- after DELETE on connectors
        DROP TRIGGER IF EXISTS connectors_count_after_del ON connectors;
        CREATE TRIGGER connectors_count_after_del
        AFTER DELETE ON connectors
        FOR EACH ROW EXECUTE FUNCTION refresh_connectors_count();

        -- after station_id change on connectors
        DROP TRIGGER IF EXISTS connectors_count_after_upd ON connectors;
        CREATE TRIGGER connectors_count_after_upd
        AFTER UPDATE OF station_id ON connectors
        FOR EACH ROW EXECUTE FUNCTION refresh_connectors_count();
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS connectors_count_after_ins ON connectors;
            DROP TRIGGER IF EXISTS connectors_count_after_del ON connectors;
            DROP TRIGGER IF EXISTS connectors_count_after_upd ON connectors;
            DROP FUNCTION IF EXISTS refresh_connectors_count();
        ");
    }
};
