<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Stations\StationDetailsController;
use App\Http\Controllers\Stations\StationsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;


// CLIENT
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/start/{station_code}/{connector_code}', [HomeController::class, 'start']);
Route::get('/start/session', [HomeController::class, 'session'])->name('start.session');

Route::get('/pay', [PaymentController::class, 'index']);
Route::post('/checkout', [PaymentController::class, 'checkout']);
Route::post('/midtrans/webhook', [PaymentController::class, 'handleWebhook']); // endpoint webhook

// ADMIN
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::group(['prefix' => 'stations'], function () {
    Route::get('/', [StationsController::class, 'index'])->name('stations');
    Route::get('/get-data', [StationsController::class, 'getData'])->name('stations.get-data');
    Route::get('/detail-table/{id}', [StationsController::class, 'detailTable'])->name('stations.detail-table');
    Route::group(['prefix' => 'details'], function () {
        Route::get('/', [StationDetailsController::class, 'indexDetails'])->name('stations.details');
        Route::get('/get-connectors', [StationDetailsController::class, 'getConnectors'])->name('stations.details.get-connectors');
        Route::get('/{id}/tab/{tab}', [StationDetailsController::class, 'loadTab'])->name('stations.details.tab');
    });
});

// TEST STOP ROUTE
Route::get('/teststop', function () {
    return view('home.teststop');
})->name('teststop');
