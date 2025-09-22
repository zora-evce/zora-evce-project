<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RemoteCommandController extends Controller
{
    /**
     * POST /api/ocpp/commands
     * Body:
     *  - station_code (string, required)
     *  - connector (int, optional)
     *  - command: "RemoteStartTransaction" | "RemoteStopTransaction"
     *  - payload: object (e.g. {"idTag":"CARD123"})
     */
    public function enqueue(Request $r)
    {
        $data = $this->validated($r, [
            'station_code' => ['required','string','max:100'],
            'connector'    => ['nullable','integer','min:0'],
            'command'      => ['required','string','in:RemoteStartTransaction,RemoteStopTransaction'],
            'payload'      => ['nullable','array'],
        ]);

        [$stationId, $connectorId] = $this->resolveStationConnector($data['station_code'], $data['connector'] ?? null);

        $id = DB::table('remote_commands')->insertGetId([
            'station_id'   => $stationId,
            'connector_id' => $connectorId,
            'command'      => $data['command'],
            'payload'      => isset($data['payload']) ? json_encode($data['payload']) : null,
            'status'       => 'pending',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return response()->json(['ok' => true, 'command_id' => $id]);
    }

    /**
     * GET /api/ocpp/commands/poll?station_code=Zora1&connector=1
     * Returns at most 1 pending command and marks as "sent".
     */
    public function poll(Request $r)
    {
        $data = $this->validated($r, [
            'station_code' => ['required','string','max:100'],
            'connector'    => ['nullable','integer','min:0'],
        ]);

        [$stationId, $connectorId] = $this->resolveStationConnector($data['station_code'], $data['connector'] ?? null);

        $cmd = DB::table('remote_commands')
            ->where('station_id', $stationId)
            ->when($connectorId, fn($q) => $q->where('connector_id', $connectorId))
            ->where('status', 'pending')
            ->orderBy('id')
            ->first();

        if (! $cmd) {
            return response()->json(['ok' => true, 'command' => null]);
        }

        DB::table('remote_commands')->where('id', $cmd->id)->update([
            'status'     => 'sent',
            'updated_at' => now(),
        ]);

        return response()->json([
            'ok'      => true,
            'command' => [
                'id'          => $cmd->id,
                'command'     => $cmd->command,
                'payload'     => $cmd->payload ? json_decode($cmd->payload, true) : null,
                'station_id'  => $cmd->station_id,
                'connector_id'=> $cmd->connector_id,
            ],
        ]);
    }

    /**
     * Optional: device can acknowledge result
     * POST /api/ocpp/commands/ack
     * Body: { "id": <command_id>, "status": "ack"|"error", "detail": {...} }
     */
    public function ack(Request $r)
    {
        $p = $this->validated($r, [
            'id'     => ['required','integer','min:1'],
            'status' => ['required','string','in:ack,error,cancelled'],
            'detail' => ['nullable','array'],
        ]);

        $updated = DB::table('remote_commands')->where('id', $p['id'])->update([
            'status'     => $p['status'],
            'updated_at' => now(),
        ]);

        if (isset($p['detail'])) {
            DB::table('ocpp_events')->insert([
                'station_id'  => null,
                'connector_id'=> null,
                'name'        => 'RemoteCommandAck',
                'level'       => $p['status'] === 'ack' ? 'info' : 'error',
                'detail'      => json_encode(['command_id' => $p['id'], 'detail' => $p['detail']]),
                'event_time'  => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return response()->json(['ok' => (bool)$updated]);
    }

    private function validated(Request $r, array $rules): array
    {
        return validator($r->all(), $rules)->validate();
    }

    private function resolveStationConnector(string $stationCode, ?int $connectorNumber): array
    {
        $station = DB::table('stations')->where('code', $stationCode)->first();
        if (! $station) {
            abort(404, 'station not found');
        }
        $connectorId = null;
        if (! is_null($connectorNumber)) {
            $connector = DB::table('connectors')
                ->where('station_id', $station->id)
                ->where('connector_number', $connectorNumber)
                ->first();
            if (! $connector) {
                abort(404, 'connector not found');
            }
            $connectorId = $connector->id;
        }
        return [$station->id, $connectorId];
    }
}
