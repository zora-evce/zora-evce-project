<?php
namespace App\Traits;

use App\Helpers\ConstantsHelper;
use App\Helpers\GlobalHelper;
use App\Models\Connectors;
use App\Models\Stations;
use App\Models\StationsV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait OverviewTrait
{
    public function renderOverview($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $station = StationsV::findOrFail($station_id);
        $data = null;
        return view('stations.details.partials.' . $tab, get_defined_vars());
    }

    public function getConnectors(Request $request)
    {
        $station_id = $request->get('station_id');
        $model = new Connectors();
        $query = $model->select()->where('station_id', $station_id);
        return response()->json(GlobalHelper::dataTable($request, $query));
    }

    public function registerNewConnector(Request $request)
    {
        $post = ($request->post());
        DB::beginTransaction();
        $model = new Connectors();
        unset($post['_token']);
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
}
