<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OcppEventController;
use App\Http\Controllers\RemoteCommandController;

// Healthcheck sederhana
Route::get('/ping', fn () => response()->json(['ok' => true], 200));

// Group OCPP – pakai middleware class langsung (bukan alias string)
Route::prefix('ocpp')
    ->middleware(\App\Http\Middleware\VerifyChargerKey::class)
    ->group(function () {
        // Debug endpoint (tanpa DB write berat)
        Route::post('/_debug_heartbeat', function () {
            return response()->json(['ok' => true]);
        });

        Route::post('/boot-notification', [OcppEventController::class, 'bootNotification']);
        Route::post('/authorize',        [OcppEventController::class, 'authorize']);
        Route::post('/heartbeat',        [OcppEventController::class, 'heartbeat']);
        Route::post('/status-notification', [OcppEventController::class, 'statusNotification']);
        Route::post('/meter-values',        [OcppEventController::class, 'meterValues']);
        Route::post('/start-transaction',   [OcppEventController::class, 'startTransaction']);
        Route::post('/stop-transaction',    [OcppEventController::class, 'stopTransaction']);

        // Remote commands
        Route::post('/commands',      [RemoteCommandController::class, 'enqueue']);
        Route::post('/commands/ack',  [RemoteCommandController::class, 'ack']);
        Route::get('/commands/poll',  [RemoteCommandController::class, 'poll']);
    });
