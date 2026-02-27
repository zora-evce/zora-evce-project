<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\Connector;
use App\Models\Stations;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
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

        $qTxSum = Transaction::query()
            ->when($idRole === 2, fn ($q) => $q->whereIn('station_id', $stationIds))
            ->selectRaw("DATE(start_time) as trx_date, COUNT(id) as transaction_sum")
            ->groupByRaw("DATE(start_time)")
            ->whereNotNull('start_time')
            ->where('payment_status', 1)
            ->orderBy('trx_date')
            ->limit(7)
            ->get();
        $txDate = $qTxSum->pluck('trx_date')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->values()
            ->toArray();

        $txSum = $qTxSum->pluck('transaction_sum')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->toArray();

        $tx = Transaction::query()
            ->when($idRole === 2, fn ($q) => $q->whereIn('station_id', $stationIds))
            ->where('payment_status', 1)
            ->selectRaw("
                SUM(CASE WHEN start_time IS NOT NULL AND stop_time IS NULL THEN 1 ELSE 0 END) AS ongoing,
                SUM(CASE WHEN start_time IS NOT NULL AND stop_time IS NOT NULL THEN 1 ELSE 0 END) AS finished
            ")
            ->first();

        $sumPrice = Transaction::query()
            ->when($idRole === 2, fn ($q) => $q->whereIn('station_id', $stationIds))
            ->where('payment_status', 1)
            ->sum('total_price');
        $sumPrice = GlobalHelper::convertToRupiah($sumPrice);

        $st = Connector::query()
            ->when($idRole === 2, fn ($q) => $q->whereIn('station_id', $stationIds))
            ->selectRaw("
                SUM(CASE WHEN connectivity_status = 'online' THEN 1 ELSE 0 END) AS online,
                SUM(CASE WHEN connectivity_status = 'offline' THEN 1 ELSE 0 END) AS offline
            ")
            ->first();

        $gmap_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4855.1234758635255!2d106.82332447582228!3d-6.165672493821592!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5ccfad31207%3A0xbe8fbd60a735cbd6!2sM%C3%B6venpick%20Hotel%20Jakarta%20City%20Centre!5e1!3m2!1sen!2sid!4v1766412653260!5m2!1sen!2sid';
        if ($idRole === 2) {
            $gmap_embed = Stations::where('account_id', $this->auth->partner_id)->pluck('gmap_embed');
            $gmap_embed = $gmap_embed[0] ?? null;
            if (!empty($gmap_embed)) {
                preg_match('/src="([^"]+)"/', $gmap_embed, $matches);
                $gmap_url = $matches[1] ?? null;
            }
        }

        return [
            'tx_sum' => [
                'tx_date'  => $txDate,
                'tx_sum'   => $txSum,
            ],
            'transactions' => [
                'ongoing'  => (int) ($tx->ongoing ?? 0),
                'finished' => (int) ($tx->finished ?? 0),
                'sum_price'=> $sumPrice
            ],
            'stations' => [
                'online'  => (int) ($st->online ?? 0),
                'offline' => (int) ($st->offline ?? 0),
            ],
            'gmap_url' => $gmap_url
        ];
    }

}
