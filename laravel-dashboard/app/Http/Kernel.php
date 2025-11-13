<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware groups.
     * Keep this minimal; our API routes use the 'api' group.
     */
    protected $middlewareGroups = [
        'api' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        // 'web' can be added later if you serve web routes.
        'web' => [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     * (Laravel 11/12 uses $middlewareAliases instead of $routeMiddleware)
     */
    protected $middlewareAliases = [
        // Common aliases you may use (minimal set)
        'throttle'    => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        // Our custom API key checks
        'ocpp.key'    => \App\Http\Middleware\VerifyOcppKey::class,
        'charger.key' => \App\Http\Middleware\VerifyChargerKey::class,
    ];

    /**
     * Route middleware (for older Laravel versions / compatibility).
     */
    protected $routeMiddleware = [
        'ocpp.key'    => \App\Http\Middleware\VerifyOcppKey::class,
        'charger.key' => \App\Http\Middleware\VerifyChargerKey::class,
    ];
}
