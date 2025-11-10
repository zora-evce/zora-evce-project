<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * Controller ini bertanggung jawab untuk MENGIRIM perintah
 * ke gateway Python OCPP melalui Redis Pub/Sub.
 */
class OcppCommandController extends Controller
{
    private const REDIS_CHANNEL = 'ocpp:commands';

    public function remoteStart(Request $request)
    {
        // 1. Validasi input (menggunakan helper lokal)
        $validated = $this->validated($request, [
            'station_code' => ['required', 'string', 'max:100'],
            'id_tag'       => ['required', 'string', 'max:255'],
        ]);

        $stationCode = $validated['station_code'];
        $idTag = $validated['id_tag'];

        // 2. Siapkan payload
        $commandName = 'RemoteStartTransaction';
        $payload = ['idTag' => $idTag];

        try {
            // 3. Panggil helper baru untuk memproses dan mengirim
            $commandId = $this->enqueueAndPublish(
                $stationCode,
                null, // connectorId (opsional)
                $commandName,
                $payload
            );

            Log::info('OCPP Command Published: RemoteStartTransaction', [
                'station_code' => $stationCode,
                'id_tag' => $idTag,
                'command_id' => $commandId
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Perintah RemoteStart telah dikirim',
                'command_id' => $commandId // Kembalikan ID database
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal publish perintah RemoteStart', [
                'error' => $e->getMessage(),
                'station_code' => $stationCode
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function remoteStop(Request $request)
    {
        // 1. Validasi input
        $validated = $this->validated($request, [
            'station_code'   => ['required', 'string', 'max:100'],
            'transaction_id' => ['required', 'integer', 'min:0'],
        ]);

        $stationCode = $validated['station_code'];
        $transactionId = (int)$validated['transaction_id'];

        // 2. Siapkan payload
        $commandName = 'RemoteStopTransaction';
        $payload = ['transactionId' => $transactionId];

        try {
            // 3. Panggil helper baru untuk memproses dan mengirim
            $commandId = $this->enqueueAndPublish(
                $stationCode,
                null, // connectorId (opsional)
                $commandName,
                $payload
            );

            Log::info('OCPP Command Published: RemoteStopTransaction', [
                'station_code' => $stationCode,
                'transaction_id' => $transactionId,
                'command_id' => $commandId
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Perintah RemoteStop telah dikirim',
                'command_id' => $commandId
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal publish perintah RemoteStop', [
                'error' => $e->getMessage(),
                'station_code' => $stationCode
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper baru yang menggabungkan logika 'enqueue' LAMA dan 'publish' BARU
     */
    private function enqueueAndPublish(string $stationCode, ?int $connectorNumber, string $commandName, array $payload): int
    {
        // 1) Insert inside a transaction only
        $newCommandId = DB::transaction(function () use ($stationCode, $connectorNumber, $commandName, $payload) {
            [$stationId, $connectorId] = $this->resolveStationConnector($stationCode, $connectorNumber);

            return DB::table('remote_commands')->insertGetId([
                'station_id'   => $stationId,
                'connector_id' => $connectorId,
                'command'      => $commandName,
                'payload'      => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'status'       => 'pending',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        });

        if (!is_int($newCommandId)) {
            Log::error('Gagal membuat command ID dalam transaksi', [
                'station_code' => $stationCode,
                'command'      => $commandName,
            ]);
            throw new \RuntimeException('Gagal membuat command.');
        }

        // 2) Prepare Redis payload
        $redisPayload = [
            'command'       => $commandName,
            'cp_id'         => $stationCode,
            'payload'       => $payload,
            'command_db_id' => $newCommandId,
        ];

        // 3) Publish (outside transaction) and update status
        try {
            Redis::publish(self::REDIS_CHANNEL, json_encode($redisPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

            DB::table('remote_commands')
                ->where('id', $newCommandId)
                ->update([
                    'status'     => 'sent',
                    'updated_at' => now(),
                ]);

            Log::info('Redis publish OK', [
                'channel' => self::REDIS_CHANNEL,
                'id'      => $newCommandId,
                'cmd'     => $commandName,
                'cp_id'   => $stationCode,
            ]);
        } catch (\Throwable $e) {
            // Mark as failed (or keep 'pending' if you have a separate retry flow)
            DB::table('remote_commands')
                ->where('id', $newCommandId)
                ->update([
                    'status'     => 'failed',
                    'updated_at' => now(),
                ]);

            Log::error('Redis publish FAILED', [
                'channel' => self::REDIS_CHANNEL,
                'id'      => $newCommandId,
                'cmd'     => $commandName,
                'cp_id'   => $stationCode,
                'error'   => $e->getMessage(),
            ]);

            // Re-throw so the controller can return 500 if desired
            throw $e;
        }

        return $newCommandId;
    }

    private function validated(Request $r, array $rules): array
    {
        return validator($r->all(), $rules)->validate();
    }

    private function resolveStationConnector(string $stationCode, ?int $connectorNumber): array
    {
        $station = DB::table('stations')->where('code', $stationCode)->first();
        if (!$station) {
            abort(404, 'Stasiun tidak ditemukan: ' . $stationCode);
        }

        $connectorId = null;
        if (!is_null($connectorNumber)) {
            $connector = DB::table('connectors')
                ->where('station_id', $station->id)
                ->where('connector_number', $connectorNumber)
                ->first();

            if (!$connector) {
                abort(404, 'Konektor tidak ditemukan');
            }
            $connectorId = $connector->id;
        }

        return [$station->id, $connectorId];
    }
}
