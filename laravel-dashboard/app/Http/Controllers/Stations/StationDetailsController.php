<?php

namespace App\Http\Controllers\Stations;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\LookupC;
use App\Models\Stations;
use App\Models\StationsV;
use App\Traits\CommandsTrait;
use App\Traits\OverviewTrait;
use Illuminate\Http\Request;

class StationDetailsController extends Controller
{
    use OverviewTrait;
    use CommandsTrait;

    public $station_id;

    public function __construct()
    {}

    public function indexDetails(Request $request)
    {
        $station_id = $request->get('id');
        $station = StationsV::findOrFail($station_id);
        $station_name = $station->name;
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

    public function loadTab(Request $request, $id, $tab)
    {
        $station = Stations::findOrFail($id);
        $method = 'render' . ucfirst($tab);

        if (method_exists($this, $method)) {
            return $this->{$method}($tab, $station, $request);
        }

        return response("<p class='text-muted'>No details available for this tab.</p>", 200)
            ->header('Content-Type', 'text/html');
    }

}
