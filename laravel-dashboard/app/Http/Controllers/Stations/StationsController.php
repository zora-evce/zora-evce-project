<?php

namespace App\Http\Controllers\Stations;

use App\Helpers\ConstantsHelper;
use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\Brands;
use App\Models\City;
use App\Models\LookupC;
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
        $data = self::bundleDataStations();
        return view('/stations/index', get_defined_vars());
    }

    public function getData(Request $request)
    {
        $model = new StationsV();
        $query = $model->select();
        if (!empty($request->get('filter_code'))) {
            $query = $query->where('code', 'ILIKE', '%' . $request->get('filter_code') . '%');
        }
        if (!empty($request->get('filter_name'))) {
            $query = $query->where('name', 'ILIKE', '%' . $request->get('filter_name') . '%');
        }
        if (!empty($request->get('filter_status'))) {
            $query = $query->where('connectivity_status', $request->get('filter_status'));
        }
        if (!empty($request->get('filter_city'))) {
            $query = $query->where('city_id', $request->get('filter_city'));
        }
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

    private function bundleDataStations()
    {
        $connectivity_status = LookupC::where('lookup_type', ConstantsHelper::CONNECTIVITY_STATUS)->get();
        $city = City::all();
        $brands = Brands::all();
        $vendors = Vendors::all();
        $models = Models::all();
        $data = [
            'connectivity_status' => $connectivity_status,
            'city' => $city,
            'brands' => $brands,
            'vendors' => $vendors,
            'models' => $models
        ];
        return $data;
    }

}
