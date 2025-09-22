<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyOcppKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.ocpp.key');
        $provided = (string) $request->header('X-OCPP-Key');

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        if ($key = $request->header('X-Idempotency-Key')) {
            $request->attributes->set('idempotency_key', (string) $key);
        }

        return $next($request);
    }
}
