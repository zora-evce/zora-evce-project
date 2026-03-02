<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\Connector;
use App\Models\Stations;
use App\Models\Transaction;
use Illuminate\Http\Request;
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
        $stations = self::getStationsDropdown();
        $months = GlobalHelper::getMonths();
        return [
            'stations' => $stations,
            'months' => $months
        ];
    }

    private function getStationsDropdown()
    {
        $model = new Stations();
        $query = $model->select('id', 'code', 'name');
        if ($this->auth->id_role === 2) {
            $query = $query->wherIn('id', $this->station_ids);
        }
        return $query->get();
    }

    public function getDataDashboard(Request $request)
    {
        try {
            $qTxSum = Transaction::query()
                ->when($this->auth->id_role === 2, fn ($q) => $q->whereIn('station_id', $this->station_ids))
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
                ->when($this->auth->id_role === 2, fn ($q) => $q->whereIn('station_id', $this->station_ids))
                ->where('payment_status', 1)
                ->selectRaw("
                    SUM(CASE WHEN start_time IS NOT NULL AND stop_time IS NULL THEN 1 ELSE 0 END) AS ongoing,
                    SUM(CASE WHEN start_time IS NOT NULL AND stop_time IS NOT NULL THEN 1 ELSE 0 END) AS finished
                ")
                ->first();

            $sumPrice = Transaction::query()
                ->when($this->auth->id_role === 2, fn ($q) => $q->whereIn('station_id', $this->station_ids))
                ->where('payment_status', 1)
                ->sum('total_price');
            $sumPrice = GlobalHelper::convertToRupiah($sumPrice);

            $st = Connector::query()
                ->when($this->auth->id_role === 2, fn ($q) => $q->whereIn('station_id', $this->station_ids))
                ->selectRaw("
                    SUM(CASE WHEN connectivity_status = 'online' THEN 1 ELSE 0 END) AS online,
                    SUM(CASE WHEN connectivity_status = 'offline' THEN 1 ELSE 0 END) AS offline,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available,
                    SUM(CASE WHEN status = 'unavailable' THEN 1 ELSE 0 END) AS unavailable,
                    SUM(CASE WHEN status = 'charging' THEN 1 ELSE 0 END) AS charging,
                    SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) AS preparing
                ")
                ->where('deleted_at', null)
                ->first();

            $gmap_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4855.1234758635255!2d106.82332447582228!3d-6.165672493821592!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5ccfad31207%3A0xbe8fbd60a735cbd6!2sM%C3%B6venpick%20Hotel%20Jakarta%20City%20Centre!5e1!3m2!1sen!2sid!4v1766412653260!5m2!1sen!2sid';
            if ($this->auth->id_role === 2) {
                $gmap_embed = Stations::where('account_id', $this->auth->partner_id)->pluck('gmap_embed');
                $gmap_embed = $gmap_embed[0] ?? null;
                if (!empty($gmap_embed)) {
                    preg_match('/src="([^"]+)"/', $gmap_embed, $matches);
                    $gmap_url = $matches[1] ?? null;
                }
            }

            $data = [
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
                    'available' => (int) ($st->available ?? 0),
                    'unavailable' => (int) ($st->unavailable ?? 0),
                    'charging' => (int) ($st->charging ?? 0),
                    'preparing' => (int) ($st->preparing ?? 0),
                ],
                'gmap_url' => $gmap_url
            ];
            return response()->json([
                'ok' => true,
                'message' => 'Success.',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

}
