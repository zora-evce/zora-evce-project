<?php

namespace App\Http\Controllers\Stations;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\Stations;
use App\Models\StationsV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class StationsController extends Controller
{
    public function index()
    {
        $data = [];
        return view('/stations/index', $data);
    }

    public function getData(Request $request)
    {
        $model = new StationsV();
        $query = $model->select();
        return response()->json(GlobalHelper::dataTable($request, $query));
    }

    public function detailTable($id)
    {
        $data = StationsV::findOrFail($id);
        return view('stations.partials.detail-table', compact('data'))->render();
    }

}
