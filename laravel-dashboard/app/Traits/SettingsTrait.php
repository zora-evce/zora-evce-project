<?php
namespace App\Traits;

use App\Helpers\GlobalHelper;
use App\Models\Connectors;
use App\Models\Stations;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

trait SettingsTrait
{
    public function renderSettings($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $station_code = $station->code;
        $data = null;
        $url = self::getUrlQr($station_id, $station_code);
        $qr = !empty($url) ? self::generateQr($url) : null;
        return view('stations.details.partials.' . $tab, get_defined_vars());
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
