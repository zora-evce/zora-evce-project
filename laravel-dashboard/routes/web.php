<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;


// CLIENT
Route::get('/start/{station_code}/{connector_code}', [HomeController::class, 'start']);
Route::get('/start/session', [HomeController::class, 'session'])->name('start.session');

Route::get('/pay', [PaymentController::class, 'index']);
Route::post('/checkout', [PaymentController::class, 'checkout']);
Route::post('/checkout/notification', [PaymentController::class, 'notification']); // endpoint webhook

// TEST STOP ROUTE
Route::get('/teststop', function () {
    return view('home.teststop');
})->name('teststop');
