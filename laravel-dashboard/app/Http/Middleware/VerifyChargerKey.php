<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VerifyChargerKey
{
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil kode charger dari body (station_code) atau header (X-Station-Code)
        $stationCode = $request->input('station_code') ?? $request->header('X-Station-Code');
        $providedKey = $request->header('X-OCPP-Key');

        if (!$stationCode || !$providedKey) {
            return response()->json(['ok' => false, 'error' => 'missing station_code or key'], 401);
        }

        $station = DB::table('stations')->where('code', $stationCode)->first();

        if (!$station || !hash_equals((string)$station->auth_key, (string)$providedKey)) {
            return response()->json(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        // simpan info station ke request attribute (opsional untuk controller)
        $request->attributes->set('station', $station);

        // teruskan idempotency key kalau ada (tetap dukung pola lama)
        if ($key = $request->header('X-Idempotency-Key')) {
            $request->attributes->set('idempotency_key', (string) $key);
        }

        return $next($request);
    }
}
