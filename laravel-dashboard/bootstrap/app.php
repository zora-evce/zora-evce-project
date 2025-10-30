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
            $appUrlHost = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';

            $adminDomain = 'admin.' . $appUrlHost;
            $clientDomain = 'client.' . $appUrlHost;
            $rootDomain = $appUrlHost;

            Route::middleware('web')
                 ->domain($adminDomain)
                 ->name('admin.')
                 ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                 ->domain($rootDomain)
                 ->group(base_path('routes/web.php'));

            Route::middleware('web')
                 ->domain($clientDomain)
                 ->name('client.')
                 ->group(base_path('routes/web.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\MidtransConfig::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
