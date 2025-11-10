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
        // $commandId akan diisi oleh return value dari transaksi
        $commandId = DB::transaction(function () use ($stationCode, $connectorNumber, $commandName, $payload) {

            // 1. Dapatkan ID Stasiun/Konektor
            [$stationId, $connectorId] = $this->resolveStationConnector($stationCode, $connectorNumber);

            // 2. Catat perintah ke DB
            // Gunakan variabel LOKAL, bukan dari scope luar
            $newCommandId = DB::table('remote_commands')->insertGetId([
                'station_id'   => $stationId,
                'connector_id' => $connectorId,
                'command'      => $commandName,
                'payload'      => json_encode($payload),
                'status'       => 'pending',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // 3. Siapkan payload untuk Redis
            $redisPayload = [
                'command'       => $commandName,
                'cp_id'         => $stationCode,
                'payload'       => $payload,
                'command_db_id' => $newCommandId, // Gunakan ID baru
            ];

            // 4. Publish ke Redis
            Redis::publish(self::REDIS_CHANNEL, json_encode($redisPayload));

            // 5. Update status ke 'sent'
            DB::table('remote_commands')->where('id', $newCommandId)->update([
                'status'     => 'sent',
                'updated_at' => now(),
            ]);

            // 6. Kembalikan ID dari closure ini
            return $newCommandId;

        }); // Transaksi di-commit, dan $commandId sekarang berisi return value

        // 7. Pemeriksaan keamanan untuk memuaskan tool analisis statis
        // Jika transaksi gagal, $commandId bisa jadi null
        if (!is_int($commandId)) {
            Log::error('Gagal membuat command ID dalam transaksi', [
                'station_code' => $stationCode,
                'command' => $commandName
            ]);
            throw new \RuntimeException('Gagal memproses perintah dalam transaksi.');
        }

        return $commandId;
    }

    // =====================================================================
    // HELPER DIBAWAH INI DIAMBIL DARI 'RemoteCommandController' LAMA ANDA
    // =====================================================================

    private function validated(Request $r, array $rules): array
    {
        return validator($r->all(), $rules)->validate();
    }

    private function resolveStationConnector(string $stationCode, ?int $connectorNumber): array
    {
        $station = DB::table('stations')->where('code', $stationCode)->first();
        if (! $station) {
            // Anda bisa membuat stasiun baru di sini jika perlu, atau error
            abort(404, 'Stasiun tidak ditemukan: ' . $stationCode);
        }
        $connectorId = null;
        if (! is_null($connectorNumber)) {
            $connector = DB::table('connectors')
                ->where('station_id', $station->id)
                ->where('connector_number', $connectorNumber)
                ->first();
            if (! $connector) {
                abort(404, 'Konektor tidak ditemukan');
            }
            $connectorId = $connector->id;
        }
        return [$station->id, $connectorId];
    }
}
