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

            $root = config('app.domain'); // e.g. mebi.co.id

            // =====================================================
            // PRODUCTION DOMAINS (Traefik → cpo.mebi.co.id / zora.mebi.co.id)
            // =====================================================

            Route::middleware('web')
                ->domain("cpo.$root")
                ->name('cpo.')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->domain("zora.$root")
                ->name('zora.')
                ->group(base_path('routes/web.php'));


            // =====================================================
            // LOCAL CUSTOM SUBDOMAIN (cpo.localhost / zora.localhost)
            // =====================================================

            Route::middleware('web')
                ->domain("cpo.localhost")
                ->name('cpo.')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->domain("zora.localhost")
                ->name('zora.')
                ->group(base_path('routes/web.php'));


            // =====================================================
            // LOCAL ROOT (localhost/cpo/login)
            // =====================================================

            if (request()->getHost() === 'localhost') {

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

        // Dynamic Asset URL — penting untuk multi-domain & Traefik
        $middleware->append(\App\Http\Middleware\DynamicAssetUrl::class);

        // Contoh: tambahan middleware lain jika dipakai
        // $middleware->append(\App\Http\Middleware\MidtransConfig::class);

        $middleware->validateCsrfTokens(except: [
            'checkout/notification',
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
