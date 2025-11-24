<?php

namespace App\Http\Controllers\Stations;

use App\Helpers\ConstantsHelper;
use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\Brands;
use App\Models\Models;
use App\Models\Stations;
use App\Models\StationsV;
use App\Models\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class StationsController extends Controller
{
    public function index()
    {
        $data = self::bundleDataRegister();
        return view('/stations/index', get_defined_vars());
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

    public function registerNewStation(Request $request)
    {
        $post = ($request->post());
        DB::beginTransaction();
        $model = new Stations();
        unset($post['_token']);
        $query = $model->select('code')->where('code', $post['code'])->first();
        if (!empty($query)) {
            return redirect()->back()->with([
                'error' => 'Station Code Already Used!'
            ]);
        }
        $model->attributes = $post;
        if ($model->validate() === true) {
            if ($model->save()) {
                DB::commit();
                return redirect()->back()->with([
                    'success' => ConstantsHelper::MESSAGE_SUCCESS_SAVE
                ]);
            }
        } else {
            DB::rollback();
            return redirect()->back()->with([
                'error' => $model->validate()
            ]);
        }
    }

    private function bundleDataRegister()
    {
        $brands = Brands::all();
        $vendors = Vendors::all();
        $models = Models::all();
        $data = [
            'brands' => $brands,
            'vendors' => $vendors,
            'models' => $models,
        ];
        return $data;
    }

}
