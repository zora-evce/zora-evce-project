<?php
namespace App\Traits;

use App\Helpers\GlobalHelper;
use App\Models\Connectors;
use App\Models\Stations;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $type = $request->get('type');
        $station_id = $request->get('station_id');
        $query = DB::table('webhook_logs')
        ->selectRaw("
            id,
            type,
            COALESCE(payload->>'vendor', 'Unknown Vendor') AS vendor,
            COALESCE(payload->>'model', null) AS model,
            COALESCE(payload->>'station_code', 'N/A') AS station_code,
            COALESCE(payload->>'firmware', null) AS firmware,
            COALESCE(payload->>'timestamp', null) AS device_timestamp,
            deleted_at,
            created_at,
            updated_at
        ")->where('related_id', $station_id);
        if (!empty($type) && $type == 'heartbeat') {
            $query = $query->where('type', 'heartbeat');
        } else {
            $query = $query->where('type', '!=', 'heartbeat');
        }
        $query = $query->orderBy('id', 'ASC');
        $limited = DB::table(DB::raw("({$query->toSql()} LIMIT 100) as t"))->mergeBindings($query);
        return response()->json(GlobalHelper::dataTable($request, $limited));
    }
}
