<?php
namespace App\Traits;

use App\Exports\TransactionsStationsExport;
use App\Helpers\ConstantsHelper;
use App\Helpers\GlobalHelper;
use App\Models\Connectors;
use App\Models\LookupC;
use App\Models\Stations;
use App\Models\TransactionsV;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

trait TransactionsTrait
{
    public function renderTransactions($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $data = self::bundleDataTransactions();
        return view('stations.details.partials.' . $tab, get_defined_vars());
    }

    public function getDataTransactions(Request $request)
    {
        $model = new TransactionsV();
        $query = $model->select()->where('station_id', $request->get('station_id'));
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

    public function transactionsDetailTable($id)
    {
        $model = new TransactionsV();
        $data = $model->select()->where('id', $id)->first();
        return view('stations.details.partials.transactions-partials.detail-table', compact('data'))->render();
    }

    public function exportExcelTransactions(Request $request)
    {
        $filters = $request->only([
            'station_id',
            'start_date',
            'end_date',
            'transaction_id',
            'customer_name',
            'payment_status',
        ]);

        $station = Stations::findOrFail($filters['station_id']);
        $station_name = $station->name;
        $station_code = $station->code;

        $fileName = 'Transactions ' . $station_name . '-' . $station_code . '.xlsx';

        return Excel::download(new TransactionsStationsExport($filters), $fileName);
    }

    private function bundleDataTransactions()
    {
        $payment_status = LookupC::where('lookup_type', ConstantsHelper::PAYMENT_STATUS)->get();
        return [
            'payment_status' => $payment_status
        ];
    }
}
