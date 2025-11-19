<?php
namespace App\Traits;

use App\Helpers\ConstantsHelper;
use App\Helpers\GlobalHelper;
use App\Models\Connectors;
use App\Models\Stations;
use App\Models\StationsV;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait TariffTrait
{
    public function renderTariff($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $station = StationsV::findOrFail($station_id);
        $data = null;
        $tariff = self::getTariff();
        Log::info($tariff);
        return view('stations.details.partials.' . $tab, get_defined_vars());
    }

    private function getTariff()
    {
        return Tariff::all();
    }

    public function saveTariff(Request $request)
    {
        Stations::find($request->station_id)->update([
            'tariff_id'  => $request->tariff_id,
        ]);
        return redirect()->back()->with([
            'success' => ConstantsHelper::MESSAGE_SUCCESS_SAVE
        ]);
    }
}
