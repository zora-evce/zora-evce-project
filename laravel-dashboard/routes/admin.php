<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Transactions\ChargepointsTransactionsController;
use App\Http\Controllers\Stations\StationDetailsController;
use App\Http\Controllers\Stations\StationsController;
use Illuminate\Support\Facades\Route;

// ADMIN
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::group(['prefix' => 'stations'], function () {
    Route::get('/', [StationsController::class, 'index'])->name('stations');
    Route::get('/get-data', [StationsController::class, 'getData'])->name('stations.get-data');
    Route::get('/detail-table/{id}', [StationsController::class, 'detailTable'])->name('stations.detail-table');
    Route::group(['prefix' => 'details'], function () {
        Route::get('/', [StationDetailsController::class, 'indexDetails'])->name('stations.details');
        Route::get('/get-connectors', [StationDetailsController::class, 'getConnectors'])->name('stations.details.get-connectors');
        Route::get('/{id}/tab/{tab}', [StationDetailsController::class, 'loadTab'])->name('stations.details.tab');
        Route::get('/get-transactions', [StationDetailsController::class, 'getDataTransactions'])->name('stations.details.get-transactions');
        Route::get('/get-ocpp-logs', [StationDetailsController::class, 'getDataOcppLogs'])->name('stations.details.get-ocpp-logs');
    });
});
Route::group(['prefix' => 'transactions'], function () {
    Route::group(['prefix' => 'chargepoints'], function () {
        Route::get('/', [ChargepointsTransactionsController::class, 'index'])->name('transactions.chargepoints');
        Route::get('/get-data', [ChargepointsTransactionsController::class, 'getData'])->name('transactions.chargepoints.get-data');
    });
});
