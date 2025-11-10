<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use App\Models\SessionToken;
use App\Models\Connector;
use App\Models\Station;
use App\Models\Tariff;
use App\Models\WebhookLog;

class HomeController extends Controller
{
    public function start($station_code, $connector_code)
    {
    //     $response = Http::withHeaders([
    //     'X-OCPP-Key' => 'ZORA_SUPER_SECRET',
    //     'Content-Type' => 'application/json',
    // ])->post('https://zora.apenable.com/api/ocpp/commands', [
    //     'station_code' => 'Zora1',
    //     'connector' => 1,
    //     'command' => 'RemoteStartTransaction',
    //     'payload' => [
    //         'idTag' => 'TEST123'
    //     ],
    // ]);

    // // Lihat hasil response
    // if ($response->successful()) {
    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $response->json()
    //     ]);
    // } else {
    //     return response()->json([
    //         'status' => 'error',
    //         'code' => $response->status(),
    //         'body' => $response->body()
    //     ]);
    // }

        $response = Http::withHeaders([
        'X-OCPP-Key' => 'ZORA_SUPER_SECRET',
        'Content-Type' => 'application/json',
    ])->post('https://zora.apenable.com/api/ocpp/commands', [
        'station_code' => 'Zora1',
        'connector' => 1,
        'command' => 'RemoteStopTransaction',
        'payload' => [
            'transactionId' => 3
        ]
    ]);

    return response()->json([
        'status' => $response->successful() ? 'success' : 'error',
        'code' => $response->status(),
        'body' => $response->json(),
    ]);
    die;
        $data = WebhookLog::where('payload->status', 'Available')->get();
        
        try {
            $station = Station::where('code', $station_code)->first();
            $connector = Connector::where('connector_code', $connector_code)->first();

            if (!$station || !$connector) {
                return response()->view('errors.404', [], 404);
            }

            // CHECK IF CONNECTOR ACTIVE
            if ($connector->status !== 'available') {
                return response()->view('errors.connector_inactive', [
                    'station_code' => $station_code,
                    'connector_code' => $connector_code,
                ], 409);
            }
            
            $payload = json_encode([
                'station_code' => $station_code,
                'connector_code' => $connector_code,
                'ts' => time(),
            ]);

            $token = Crypt::encryptString($payload);

            $session = new SessionToken;
            $session->token = $token;
            $session->station_code = $station_code;
            $session->connector_code = $connector_code;
            $session->expires_at = Carbon::now()->addMinutes(5);

            $session->save();
        } catch (\Exception $e) {
            store_error_log($e);
            return response()->view('errors.500', [], 500);
        }

        return redirect()->route('zora.start.session', ['token' => $token]);
    }

    public function session()
    {
        try {
            $token = request('token');

            $record = SessionToken::where('token', $token)->first();
            if (!$record || $record->isExpired()) {
                return response()->view('errors.404', [], 404);
            }

            $decoded = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);

            $station = Station::where('code', $decoded['station_code'])->first();
            $connector = Connector::where('connector_code', $decoded['connector_code'])->first();

            if (!$station || !$connector) {
                return response()->view('errors.404', [], 404);
            }

            // GET PRODUCTS
            $products = Tariff::where('tariff_type', 'duration')
                                ->where('active', 1)
                                ->get();

            return view('home.index', [
                'station' => $station ?? null,
                'connector' => $connector ?? null,
                'products' => $products ?? collect(),
            ]);

        } catch (\Throwable $e) {
            store_error_log($e);
            return response()->view('errors.500', [], 500);
        }
    }
}


