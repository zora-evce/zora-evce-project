<?php
namespace App\Traits;

use App\Helpers\GlobalHelper;
use App\Models\Connectors;
use App\Models\Stations;
use Illuminate\Http\Request;

trait ConfigurationTrait
{
    public function renderConfiguration($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $data = null;
        return view('stations.details.partials.' . $tab, get_defined_vars());
    }
}
