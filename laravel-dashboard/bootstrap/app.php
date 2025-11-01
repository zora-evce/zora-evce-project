<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            $host = request()->getHost();

            $isIpOrLocal = ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP));

            if (!$isIpOrLocal) {
                // --- DOMAIN-BASED ROUTING ---
                $appUrlHost = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';

                $adminDomain = 'cpo.' . $appUrlHost;
                $clientDomain = 'zora.' . $appUrlHost;
                $rootDomain = $appUrlHost;

                Route::middleware('web')
                     ->domain($adminDomain)
                     ->name('cpo.')
                     ->group(base_path('routes/admin.php'));

                // Route::middleware('web')
                //      ->domain($rootDomain)
                //      ->group(base_path('routes/web.php'));

                Route::middleware('web')
                     ->domain($clientDomain)
                     ->name('zora.')
                     ->group(base_path('routes/web.php'));

            } else {
                // --- IP-BASED (PREFIX) ROUTING ---
                Route::middleware('web')
                     ->prefix('cpo')
                     ->name('cpo.')
                     ->group(base_path('routes/admin.php'));

                Route::middleware('web')
                     ->group(base_path('routes/web.php'));
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\MidtransConfig::class);
        $middleware->validateCsrfTokens(except: [
            'checkout/notification',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

