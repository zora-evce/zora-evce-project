<?php

namespace App\Http\Controllers\Master;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\Tariff;
use App\Models\TransactionsV;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index()
    {
        $data = [];
        return view('master.tariff.index', $data);
    }

    public function getData(Request $request)
    {
        $model = new Tariff();
        $query = $model->select();
        return response()->json(GlobalHelper::dataTable($request, $query));
    }

}
