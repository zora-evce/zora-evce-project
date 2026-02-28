<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Transactions\ChargepointsTransactionsController;
use App\Http\Controllers\Stations\StationDetailsController;
use App\Http\Controllers\Stations\StationsController;
use App\Http\Controllers\Users\UsersController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Master\TariffController;
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
        Route::get('/export-excel', [StationsController::class, 'exportExcel'])->name('stations.export-excel');
        Route::group(['prefix' => 'details'], function () {
            Route::get('/', [StationDetailsController::class, 'indexDetails'])->name('stations.details');
            Route::get('/get-connectors', [StationDetailsController::class, 'getConnectors'])->name('stations.details.get-connectors');
            Route::get('/{id}/tab/{tab}', [StationDetailsController::class, 'loadTab'])->name('stations.details.tab');
            Route::get('/get-ocpp-logs', [StationDetailsController::class, 'getDataOcppLogs'])->name('stations.details.get-ocpp-logs');
            Route::group(['prefix' => 'overview'], function () {
                Route::post('/register-new-connector', [StationDetailsController::class, 'registerNewConnector'])->name('stations.details.overview.register-new-connector');
            });
            Route::group(['prefix' => 'commands'], function () {
                Route::post('/start-transaction-command', [StationDetailsController::class, 'startTransactionCommand'])->name('stations.details.commands.start-transaction-command')
                ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
            });
            Route::group(['prefix' => 'tariff'], function () {
                Route::post('/save-tariff', [StationDetailsController::class, 'saveTariff'])->name('stations.details.tariff.save-tariff');
                Route::get('/get-tariff-in-use', [StationDetailsController::class, 'getTariffInUse'])->name('stations.details.tariff.get-tariff-in-use');
            });
            Route::group(['prefix' => 'settings'], function () {
                Route::get('/download-qr', [StationDetailsController::class, 'downloadQr'])->name('stations.details.settings.download-qr');
                Route::post('/save-settings-section', [StationDetailsController::class, 'saveSettingsSection'])->name('stations.details.settings.save-settings-section');
            });
            Route::group(['prefix' => 'transactions'], function () {
                Route::get('/get-transactions', [StationDetailsController::class, 'getDataTransactions'])->name('stations.details.transactions.get-transactions');
                Route::get('/detail-table/{id}', [StationDetailsController::class, 'transactionsDetailTable'])->name('stations.details.transactions.detail-table');
                Route::get('/export-excel-transactions', [StationDetailsController::class, 'exportExcelTransactions'])->name('stations.details.transactions.export-excel-transactions');
            });
            Route::group(['prefix' => 'location'], function () {
                Route::post('/save-station-location', [StationDetailsController::class, 'saveStationLocation'])->name('stations.details.location.save-station-location');
            });
        });
    });

    Route::group(['prefix' => 'transactions'], function () {
        Route::group(['prefix' => 'chargepoints'], function () {
            Route::get('/', [ChargepointsTransactionsController::class, 'index'])->name('transactions.chargepoints');
            Route::get('/get-data', [ChargepointsTransactionsController::class, 'getData'])->name('transactions.chargepoints.get-data');
            Route::get('/export-excel', [ChargepointsTransactionsController::class, 'exportExcel'])->name('transactions.chargepoints.export-excel');
        });
    });

    Route::group(['prefix' => 'master'], function () {
        Route::group(['prefix' => 'tariff'], function () {
            Route::get('/', [TariffController::class, 'index'])->name('master.tariff');
            Route::get('/get-data', [TariffController::class, 'getData'])->name('master.tariff.get-data');
            Route::post('/add-tariff', [TariffController::class, 'addTariff'])->name('master.tariff.add-tariff');
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
        Route::post('/create-account', [UsersController::class, 'createAccount'])->name('users.create-account');
    });

    Route::get('/my-account', [UsersController::class, 'myAccount'])->name('my-account');
    Route::group(['prefix' => 'actions'], function () {
        Route::post('/stop/action', [HomeController::class, 'stopAction'])->name('stop.action');
    });
});
