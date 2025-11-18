<?php
namespace App\Traits;

use App\Helpers\GlobalHelper;
use App\Models\Connectors;
use App\Models\Stations;
use App\Models\WebhookLog;
use Illuminate\Http\Request;

trait OcppLogsTrait
{
    public function renderOcppLogs($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $data = null;
        return view('stations.details.partials.' . $tab, get_defined_vars());
    }

    public function getDataOcppLogs(Request $request)
    {
        $model = new WebhookLog();
        $query = $model->select();
        return response()->json(GlobalHelper::dataTable($request, $query));
    }
}
