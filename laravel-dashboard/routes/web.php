<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Stations\StationDetailsController;
use App\Http\Controllers\Stations\StationsController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::group(['prefix' => 'stations'], function () {
    Route::get('/', [StationsController::class, 'index'])->name('stations');
    Route::get('/get-data', [StationsController::class, 'getData'])->name('stations.get-data');
    Route::group(['prefix' => 'details'], function () {
        Route::get('/', [StationDetailsController::class, 'indexDetails'])->name('stations.details');
        Route::get('/get-connectors', [StationDetailsController::class, 'getConnectors'])->name('stations.details.get-connectors');
        Route::get('/{id}/tab/{tab}', [StationDetailsController::class, 'loadTab'])->name('stations.details.tab');
    });
});
