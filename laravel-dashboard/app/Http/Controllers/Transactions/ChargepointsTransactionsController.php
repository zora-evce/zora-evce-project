<?php

namespace App\Http\Controllers\Transactions;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\TransactionsV;
use Illuminate\Http\Request;

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
        return response()->json(GlobalHelper::dataTable($request, $query));
    }

}
