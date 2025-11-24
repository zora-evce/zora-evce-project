<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\URL;

class DynamicAssetUrl
{
    public function handle($request, Closure $next)
    {
        // Force Laravel use current domain for asset, route, URL
        URL::forceRootUrl($request->getSchemeAndHttpHost());

        // Detect HTTPS forwarded by Traefik
        if ($request->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        return $next($request);
    }
}
