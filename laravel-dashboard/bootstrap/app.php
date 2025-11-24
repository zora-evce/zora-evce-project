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

            $appDomain = config('app.domain', 'localhost'); // mebi.co.id
            $adminDomain = "cpo.$appDomain";
            $clientDomain = "zora.$appDomain";

            $isIpOrLocal = ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP));

            if (!$isIpOrLocal) {

                // ADMIN → cpo.mebi.co.id
                Route::middleware('web')
                    ->domain($adminDomain)
                    ->name('cpo.')
                    ->group(base_path('routes/admin.php'));

                // USER → zora.mebi.co.id
                Route::middleware('web')
                    ->domain($clientDomain)
                    ->name('zora.')
                    ->group(base_path('routes/web.php'));

            } else {

                // Local development
                Route::middleware('web')
                    ->prefix('cpo')
                    ->name('cpo.')
                    ->group(base_path('routes/admin.php'));

                Route::middleware('web')
                    ->prefix('zora')
                    ->name('zora.')
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

