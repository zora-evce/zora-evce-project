<?php

namespace App\Http\Controllers\Transactions;

use App\Exports\TransactionsChargepointsExport;
use App\Helpers\ConstantsHelper;
use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\LookupC;
use App\Models\Stations;
use App\Models\TransactionsV;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ChargepointsTransactionsController extends Controller
{
    public $station_ids = [];

    public function __construct()
    {
        parent::__construct();
        $this->station_ids = self::getStationIds();
    }

    public function index()
    {
        $data = self::bundleDataTransactions();
        return view('transactions.chargepoints.index', get_defined_vars());
    }

    private function getStationIds()
    {
        return Stations::where('account_id', $this->auth->partner_id)->pluck('id');
    }

    public function transactionsDetailTable($id)
    {
        $model = new TransactionsV();
        $data = $model->select()->where('id', $id)->first();
        return view('transactions.chargepoints.partials.detail-table', compact('data'))->render();
    }

    public function getData(Request $request)
    {
        $idRole = (int) ($this->auth->id_role ?? 0);
        $stationIds = $this->station_ids ?? [];
        $model = new TransactionsV();
        $query = $model->select();
        if ($idRole == 2) {
            $query->whereIn('station_id', $stationIds);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end   = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }
        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', $request->transaction_id);
        }
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'ILIKE', '%'.$request->customer_name.'%');
        }
        if ($request->has('payment_status') && $request->payment_status !== '' && $request->payment_status !== null) {
            $query->where('payment_status', $request->payment_status);
        }
        $query->orderBy('created_at', 'desc');
        return response()->json(GlobalHelper::dataTable($request, $query));
    }

    public function exportExcel(Request $request)
    {
        $idRole = (int) ($this->auth->id_role ?? 0);
        $stationIds = $this->station_ids ?? [];
        $filters = $request->only([
            'start_date',
            'end_date',
            'transaction_id',
            'customer_name',
            'payment_status',
        ]);
        if ($idRole == 2) {
            $filters['station_ids'] = $stationIds;
        }
        $fileName = 'Transactions' . '.xlsx';
        return Excel::download(new TransactionsChargepointsExport($filters), $fileName);
    }

    private function bundleDataTransactions()
    {
        $payment_status = LookupC::where('lookup_type', ConstantsHelper::PAYMENT_STATUS)->get();
        return [
            'payment_status' => $payment_status
        ];
    }

}
