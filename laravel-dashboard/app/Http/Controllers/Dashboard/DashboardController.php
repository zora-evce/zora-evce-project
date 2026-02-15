<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Connector;
use App\Models\Stations;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class DashboardController extends Controller
{
    public $station_ids = [];

    public function __construct()
    {
        parent::__construct();
        $this->station_ids = self::getStationIds();
    }

    public function index() {
        $data = self::getBundleDataDashboard();
        return view('/dashboard/dashboard', $data);
    }

    private function getStationIds()
    {
        return Stations::where('account_id', $this->auth->partner_id)->pluck('id');

    }

    private function getBundleDataDashboard()
    {
        $idRole = (int) ($this->auth->id_role ?? 0);
        $stationIds = $this->station_ids ?? [];

        $tx = Transaction::query()
            ->when($idRole === 2, fn ($q) => $q->whereIn('station_id', $stationIds))
            ->selectRaw("
                SUM(CASE WHEN start_time IS NOT NULL AND stop_time IS NULL THEN 1 ELSE 0 END) AS ongoing,
                SUM(CASE WHEN start_time IS NOT NULL AND stop_time IS NOT NULL THEN 1 ELSE 0 END) AS finished
            ")
            ->first();

        $st = Connector::query()
            ->when($idRole === 2, fn ($q) => $q->whereIn('station_id', $stationIds))
            ->selectRaw("
                SUM(CASE WHEN connectivity_status = 'online' THEN 1 ELSE 0 END) AS online,
                SUM(CASE WHEN connectivity_status = 'offline' THEN 1 ELSE 0 END) AS offline
            ")
            ->first();

        return [
            'transactions' => [
                'ongoing'  => (int) ($tx->ongoing ?? 0),
                'finished' => (int) ($tx->finished ?? 0),
            ],
            'stations' => [
                'online'  => (int) ($st->online ?? 0),
                'offline' => (int) ($st->offline ?? 0),
            ],
        ];
    }

}
