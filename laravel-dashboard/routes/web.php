<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;


// CLIENT
Route::get('/start/{station_code}/{connector_code}', [HomeController::class, 'start']);
Route::get('/start/session', [HomeController::class, 'session'])->name('start.session');
Route::get('/stop', [HomeController::class, 'stop'])->name('stop');
// Alias to match templates expecting zora.* route names
Route::post('/stop/action', [HomeController::class, 'stopAction'])->name('stop.action');

// Route::get('/pay', [PaymentController::class, 'index'])->name('');
Route::post('/checkout', [PaymentController::class, 'checkout'])->name('checkout');
Route::get('/checkout/after', [PaymentController::class, 'after'])->name('checkout.after');
Route::get('/checkout/status/{orderId}', [PaymentController::class, 'status'])->name('checkout.status');
Route::post('/checkout/notification', [PaymentController::class, 'notification'])->name('checkout.notification'); // endpoint webhook

// TEST STOP ROUTE
// Route::get('/teststop', function () {
//     return view('home.teststop');
// })->name('teststop');
