<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OcppBridgeController extends Controller
{
    /**
     * POST /api/ocpp/remote-start-direct
     * Body: { station_code, idTag, connectorId? }
     * Forwards to OCPP Flask API /api/remote-start
     */
    public function remoteStartDirect(Request $r)
    {
        $p = validator($r->all(), [
            'station_code' => ['required','string','max:100'],
            'idTag'        => ['required','string','max:255'],
            'connectorId'  => ['nullable','integer','min:0'],
        ])->validate();

        $base = rtrim(config('services.ocpp_http.base', env('OCPP_HTTP_BASE', 'http://127.0.0.1:9100')), '/');
        $key  = config('services.ocpp_http.key', env('OCPP_KEY'));

        if (!$key) {
            return response()->json(['ok'=>false,'error'=>'OCPP_KEY not configured'], 500);
        }

        $resp = Http::withHeaders(['X-OCPP-Key' => $key])
            ->timeout(10)
            ->post($base.'/api/remote-start', $p);

        if ($resp->successful()) {
            return response()->json($resp->json());
        }

        return response()->json([
            'ok'    => false,
            'error' => 'remote_start_failed',
            'code'  => $resp->status(),
            'body'  => $resp->json() ?? $resp->body(),
        ], 502);
    }
}



