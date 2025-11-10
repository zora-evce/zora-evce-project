<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controller ini bertanggung jawab untuk MENGIRIM perintah
 * ke gateway Python OCPP melalui Redis Pub/Sub.
 */
class OcppCommandController extends Controller
{
    private const REDIS_CHANNEL = 'ocpp:commands';

    public function remoteStart(Request $request)
    {
        // 1. Validasi input dari frontend/admin panel Anda
        $validator = Validator::make($request->all(), [
            'station_code' => ['required', 'string', 'max:100'],
            'id_tag'       => ['required', 'string', 'max:255'],
            // Anda bisa tambahkan validasi lain di sini
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $stationCode = $validated['station_code'];
        $idTag = $validated['id_tag'];

        // 2. Siapkan payload perintah
        // Strukturnya harus sama dengan yang diharapkan oleh subscriber Python
        $commandPayload = [
            'command' => 'RemoteStartTransaction', // Nama perintah
            'cp_id'   => $stationCode,           // Target ChargePoint
            'payload' => [                     // Data spesifik perintah
                'idTag' => $idTag
            ]
        ];

        // 3. Publish ke Redis
        try {
            Redis::publish(self::REDIS_CHANNEL, json_encode($commandPayload));

            Log::info('OCPP Command Published: RemoteStartTransaction', [
                'station_code' => $stationCode,
                'id_tag' => $idTag
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Perintah RemoteStart telah dikirim'
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal publish perintah RemoteStart ke Redis', [
                'error' => $e->getMessage(),
                'station_code' => $stationCode
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke server Redis'
            ], 500);
        }
    }

    public function remoteStop(Request $request)
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'station_code'   => ['required', 'string', 'max:100'],
            'transaction_id' => ['required', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $stationCode = $validated['station_code'];
        // Pastikan ini adalah integer, sesuai harapan skrip Python
        $transactionId = (int)$validated['transaction_id'];

        // 2. Siapkan payload perintah
        $commandPayload = [
            'command' => 'RemoteStopTransaction',
            'cp_id'   => $stationCode,
            'payload' => [
                'transactionId' => $transactionId
            ]
        ];

        // 3. Publish ke Redis
        try {
            Redis::publish(self::REDIS_CHANNEL, json_encode($commandPayload));

            Log::info('OCPP Command Published: RemoteStopTransaction', [
                'station_code' => $stationCode,
                'transaction_id' => $transactionId
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Perintah RemoteStop telah dikirim'
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal publish perintah RemoteStop ke Redis', [
                'error' => $e->getMessage(),
                'station_code' => $stationCode
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke server Redis'
            ], 500);
        }
    }
}
