<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Stations\StationsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::group(['prefix' => 'stations'], function () {
    Route::get('/', [StationsController::class, 'index'])->name('stations');
    Route::get('/get-data', [DashboardController::class, 'index'])->name('stations.get-data');
});
