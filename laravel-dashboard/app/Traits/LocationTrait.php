<?php
namespace App\Traits;

use App\Helpers\ConstantsHelper;
use App\Helpers\GlobalHelper;
use App\Models\City;
use App\Models\Connectors;
use App\Models\Stations;
use App\Models\StationsV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait LocationTrait
{
    public function renderLocation($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $station = StationsV::findOrFail($station_id);
        $city = City::all();
        $gmap_embed = $station->gmap_embed;
        $gmap_url = null;
        if (!empty($gmap_embed)) {
            preg_match('/src="([^"]+)"/', $gmap_embed, $matches);
            $gmap_url = $matches[1] ?? null;
        }
        return view('stations.details.partials.' . $tab, get_defined_vars());
    }

    public function saveStationLocation(Request $request)
    {
        try{
            $post = $request->post();
            unset($post['_token']);
            DB::beginTransaction();
            if (empty($post['id'])) {
                return redirect()->back()->with([
                    'error' => ConstantsHelper::MESSAGE_ERROR_SAVE
                ]);
            }
            $model = Stations::find($post['id']);
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
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with([
                'error' => ConstantsHelper::MESSAGE_ERROR_SAVE.' '.$e->getMessage()
            ]);
        }
    }
}
