<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Stations\StationsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// CLIENT
Route::get('/start/{station_code}/{connector_code}', [HomeController::class, 'start']);
Route::get('/start/session', [HomeController::class, 'session'])->name('start.session');

Route::get('/pay', [PaymentController::class, 'index']);
Route::post('/checkout', [PaymentController::class, 'checkout']);
Route::post('/midtrans/webhook', [PaymentController::class, 'handleWebhook']); // endpoint webhook

// ADMIN
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::group(['prefix' => 'stations'], function () {
    Route::get('/', [StationsController::class, 'index'])->name('stations');
    Route::get('/get-data', [DashboardController::class, 'index'])->name('stations.get-data');
});

// TEST STOP ROUTE
Route::get('/teststop', function () {
    return view('home.teststop');
})->name('teststop');
