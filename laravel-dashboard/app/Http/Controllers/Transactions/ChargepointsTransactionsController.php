<?php

namespace App\Http\Controllers\Transactions;

use App\Exports\TransactionsChargepointsExport;
use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\TransactionsV;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ChargepointsTransactionsController extends Controller
{
    public function index()
    {
        $data = [];
        return view('transactions.chargepoints.index', $data);
    }

    public function getData(Request $request)
    {
        $model = new TransactionsV();
        $query = $model->select();
        if (!empty($request->get('transaction_id'))) {
            $query = $query->where('transaction_id', $request->get('transaction_id'));
        }
        return response()->json(GlobalHelper::dataTable($request, $query));
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['filter_code', 'filter_name', 'filter_status', 'filter_city']);
        $fileName = 'Transactions' . '.xlsx';
        return Excel::download(new TransactionsChargepointsExport($filters), $fileName);
    }

}
