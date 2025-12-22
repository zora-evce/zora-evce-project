<?php
namespace App\Traits;

use App\Exports\TransactionsStationsExport;
use App\Helpers\GlobalHelper;
use App\Models\Connectors;
use App\Models\Stations;
use App\Models\TransactionsV;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

trait TransactionsTrait
{
    public function renderTransactions($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $data = null;
        return view('stations.details.partials.' . $tab, get_defined_vars());
    }

    public function getDataTransactions(Request $request)
    {
        $model = new TransactionsV();
        $query = $model->select()->where('station_id', $request->get('station_id'));
        return response()->json(GlobalHelper::dataTable($request, $query));
    }

    public function exportExcelTransactions(Request $request)
    {
        $filters = $request->only(['station_id']);
        $station = Stations::findOrFail($filters['station_id']);
        $station_name = $station->name;
        $station_code = $station->code;
        $fileName = 'Transacations ' . $station_name . '-' . $station_code . '.xlsx';
        return Excel::download(new TransactionsStationsExport($filters), $fileName);
    }
}
