<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OcppEventController;

Route::prefix('ocpp')->middleware(\App\Http\Middleware\VerifyOcppKey::class)->group(function () {
    Route::post('/boot-notification',   [OcppEventController::class, 'bootNotification']);
    Route::post('/authorize',           [OcppEventController::class, 'authorize']);
    Route::post('/start-transaction',   [OcppEventController::class, 'startTransaction']);
    Route::post('/meter-values',        [OcppEventController::class, 'meterValues']);
    Route::post('/stop-transaction',    [OcppEventController::class, 'stopTransaction']);
    Route::post('/status-notification', [OcppEventController::class, 'statusNotification']);
    Route::post('/heartbeat',           [OcppEventController::class, 'heartbeat']);
    Route::post('/commands',            [\App\Http\Controllers\RemoteCommandController::class, 'enqueue']);
    Route::get('/commands/poll',        [\App\Http\Controllers\RemoteCommandController::class, 'poll']);
    Route::post('/commands/ack',        [\App\Http\Controllers\RemoteCommandController::class, 'ack']); // optional ack
});
