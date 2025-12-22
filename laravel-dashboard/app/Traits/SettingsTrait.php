<?php
namespace App\Traits;

use App\Helpers\ConstantsHelper;
use App\Helpers\GlobalHelper;
use App\Models\Brands;
use App\Models\Connectors;
use App\Models\LookupC;
use App\Models\Models;
use App\Models\Stations;
use App\Models\StationsV;
use App\Models\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

trait SettingsTrait
{
    public function renderSettings($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $station_code = $station->code;
        $data = self::bundleDataStation($station_id);
        $url = self::getUrlQr($station_id, $station_code);
        $qr = !empty($url) ? self::generateQr($url) : null;
        return view('stations.details.partials.' . $tab, get_defined_vars());
    }

    private function bundleDataStation($station_id)
    {
        $location_type = LookupC::where('lookup_type', ConstantsHelper::LOCATION_TYPE)->get();
        $model_station = StationsV::find($station_id);
        $settings = [
            'name' => $model_station->name,
            'location_type_id' => $model_station->location_type_id,
            'brand_id' => $model_station->brand_id,
            'vendor_id' => $model_station->vendor_id,
            'model_id' => $model_station->model_id,
            'auth_key' => $model_station->auth_key
        ];
        $brands = Brands::all();
        $vendors = Vendors::all();
        $models = Models::all();
        $data = [
            'settings' => $settings,
            'dropdown_select' => [
                'location_type' => $location_type,
                'brands' => $brands,
                'vendors' => $vendors,
                'models' => $models,
            ]
        ];
        return $data;
    }

    public function saveSettingsSection(Request $request)
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

    private function generateQr($url)
    {
        $qr = QrCode::size(250)
        ->generate($url);
        return $qr;
    }

    public function downloadQr(Request $request)
    {
        $station_id = $request->get('station_id');
        $station_code = $request->get('station_code');

        $url = self::getUrlQr($station_id, $station_code);

        $qrPng = QrCode::format('png')
            ->size(500)
            ->margin(2)
            ->generate($url);

        $qrImage = imagecreatefromstring($qrPng);

        $qrWidth  = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);
        $textHeight = 60;

        $finalHeight = $qrHeight + $textHeight;

        $finalImage = imagecreatetruecolor($qrWidth, $finalHeight);
        $white = imagecolorallocate($finalImage, 255, 255, 255);
        imagefill($finalImage, 0, 0, $white);

        imagecopy($finalImage, $qrImage, 0, 0, 0, 0, $qrWidth, $qrHeight);

        $black = imagecolorallocate($finalImage, 0, 0, 0);

        $text = $station_code;
        $font = 5;

        $textWidth = imagefontwidth($font) * strlen($text);
        $x = ($qrWidth - $textWidth) / 2;
        $y = $qrHeight + 15;

        imagestring($finalImage, $font, (int)$x, $y, $text, $black);

        ob_start();
        imagepng($finalImage);
        $pngData = ob_get_clean();

        imagedestroy($qrImage);
        imagedestroy($finalImage);

        return response($pngData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qr_'.$station_code.'.png"');
    }

    private function getUrlQr($station_id, $station_code)
    {
        $connector = Connectors::where('station_id', $station_id)->first();
        $base_url_qr = env('BASE_URL_QR', 'http://zora.mebi.co.id');
        $url = null;
        if (!empty($station_code) && !empty($connector->connector_code)) {
            $url = $base_url_qr . '/start' . '/' . $station_code . '/' . $connector->connector_code;
        }
        return $url;
    }
}
