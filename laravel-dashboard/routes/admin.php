<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Transactions\ChargepointsTransactionsController;
use App\Http\Controllers\Stations\StationDetailsController;
use App\Http\Controllers\Stations\StationsController;
use App\Http\Controllers\Users\UsersController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// ADMIN - Public routes (no authentication required)
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('forgot-password.post');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('reset-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.post');

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change-password.post');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::group(['prefix' => 'stations'], function () {
        Route::get('/', [StationsController::class, 'index'])->name('stations');
        Route::get('/get-data', [StationsController::class, 'getData'])->name('stations.get-data');
        Route::get('/detail-table/{id}', [StationsController::class, 'detailTable'])->name('stations.detail-table');
        Route::post('/register-new-station', [StationsController::class, 'registerNewStation'])->name('stations.register-new-station');
        Route::group(['prefix' => 'details'], function () {
            Route::get('/', [StationDetailsController::class, 'indexDetails'])->name('stations.details');
            Route::get('/get-connectors', [StationDetailsController::class, 'getConnectors'])->name('stations.details.get-connectors');
            Route::get('/{id}/tab/{tab}', [StationDetailsController::class, 'loadTab'])->name('stations.details.tab');
            Route::get('/get-transactions', [StationDetailsController::class, 'getDataTransactions'])->name('stations.details.get-transactions');
            Route::get('/get-ocpp-logs', [StationDetailsController::class, 'getDataOcppLogs'])->name('stations.details.get-ocpp-logs');
            Route::group(['prefix' => 'overview'], function () {
                Route::post('/register-new-connector', [StationDetailsController::class, 'registerNewConnector'])->name('stations.details.overview.register-new-connector');
            });
            Route::group(['prefix' => 'tariff'], function () {
                Route::post('/save-tariff', [StationDetailsController::class, 'saveTariff'])->name('stations.details.tariff.save-tariff');
            });
            Route::group(['prefix' => 'settings'], function () {
                Route::get('/download-qr', [StationDetailsController::class, 'downloadQr'])->name('stations.details.settings.download-qr');
            });
        });
    });

    Route::group(['prefix' => 'transactions'], function () {
        Route::group(['prefix' => 'chargepoints'], function () {
            Route::get('/', [ChargepointsTransactionsController::class, 'index'])->name('transactions.chargepoints');
            Route::get('/get-data', [ChargepointsTransactionsController::class, 'getData'])->name('transactions.chargepoints.get-data');
        });
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UsersController::class, 'index'])->name('users');
        Route::get('/get-data', [UsersController::class, 'getData'])->name('users.get-data');
        Route::get('/create', [UsersController::class, 'create'])->name('users.create');
        Route::post('/', [UsersController::class, 'store'])->name('users.store');
        Route::get('/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/{id}', [UsersController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [UsersController::class, 'destroy'])->name('users.destroy');
        Route::get('/{id}/detail', [UsersController::class, 'detail'])->name('users.detail');
    });
});
