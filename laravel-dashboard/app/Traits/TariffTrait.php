<?php
namespace App\Traits;

use App\Helpers\GlobalHelper;
use App\Models\Connectors;
use App\Models\Stations;
use App\Models\StationsV;
use Illuminate\Http\Request;

trait TariffTrait
{
    public function renderTariff($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $station = StationsV::findOrFail($station_id);
        $data = null;
        return view('stations.details.partials.' . $tab, get_defined_vars());
    }
}
