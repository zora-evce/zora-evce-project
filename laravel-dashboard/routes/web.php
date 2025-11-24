<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


// CLIENT
// Alias to match templates expecting zora.* route names
Route::get('/login', [AuthController::class, 'index'])->name('login');

Route::middleware(\App\Http\Middleware\MidtransConfig::class)->group(function () {
    // semua route client yang kamu sudah punya:
    Route::get('/start/{station_code}/{connector_code}', [HomeController::class, 'start']);
    Route::get('/start/session', [HomeController::class, 'session'])->name('start.session');
    Route::get('/stop', [HomeController::class, 'stop'])->name('stop');
    Route::post('/stop/action', [HomeController::class, 'stopAction'])->name('stop.action');

    Route::post('/checkout', [PaymentController::class, 'checkout'])->name('checkout');
    Route::get('/checkout/after', [PaymentController::class, 'after'])->name('checkout.after');
    Route::get('/checkout/status/{orderId}', [PaymentController::class, 'status'])->name('checkout.status');
    Route::post('/checkout/notification', [PaymentController::class, 'notification'])->name('checkout.notification');

    Route::get('/generate-password', [HomeController::class, 'generatePassword'])->name('generate.password');
});
