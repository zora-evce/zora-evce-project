<?php

namespace App\Http\Controllers\Stations;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\Connectors;
use App\Models\LookupC;
use App\Models\Stations;
use App\Traits\OverviewTrait;
use Illuminate\Http\Request;

class StationDetailsController extends Controller
{
    use OverviewTrait;

    public function indexDetails(Request $request)
    {
        $station_id = $request->get('id');
        $data = [];
        $tabs = self::getTabs();
        return view('/stations/details/index-details', get_defined_vars());
    }

    private static function getTabs()
    {
        $lookup = LookupC::select('lookup_id', 'lookup_code', 'lookup_value', 'additional_value')
            ->where('lookup_type', 'station_detail_tab')
            ->orderBy('lookup_order', 'asc')
            ->get();

        $tabs = $lookup->map(function ($tab) {
            $data = json_decode($tab->additional_value, true);
            return [
                'lookup_id' => $tab->lookup_id,
                'lookup_code' => $tab->lookup_code,
                'lookup_value' => $tab->lookup_value,
                'tab_name' => $data['tab_name'] ?? null,
            ];
        });
        return $tabs;
    }

    public function loadTab($id, $tab)
    {
        $viewName = 'stations.details.partials.' . $tab;
        if (view()->exists($viewName)) {
            return view($viewName, [
                'station' => Stations::findOrFail($id),
                'station_id' => $id
            ]);
        }
        return response("<p class='text-muted'>No details available for this tab.</p>");
    }

    public function getConnectors(Request $request)
    {
        $station_id = $request->get('station_id');
        $model = new Connectors();
        $query = $model->select()->where('station_id', $station_id);
        return response()->json(GlobalHelper::dataTable($request, $query));
    }

}
