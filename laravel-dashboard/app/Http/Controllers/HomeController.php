<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use App\Models\SessionToken;
use App\Models\Connector;
use App\Models\Station;
use App\Models\Tariff;
use App\Models\WebhookLog;
use App\Models\Transaction;
use App\Models\RemoteCommand;
use App\Services\QontakService;
use App\Helpers\GlobalHelper;

class HomeController extends Controller
{

    public function index()
    {
        return view('welcome');
    }

    public function start($station_code, $connector_code)
    {
        try {
            $station = Station::where('code', $station_code)->first();
            $connector = Connector::where('connector_code', $connector_code)->first();

            if (!$station || !$connector) {
                return response()->view('errors.404', [], 404);
            }

            // CHECK IF CONNECTOR ACTIVE
            if (!in_array($connector->status, ['available', 'preparing'])) {
                return response()->view('errors.connector_inactive', [
                    'station_code' => $station->name,
                    'connector_code' => $connector->connector_number,
                ], 409);
            }

            $payload = json_encode([
                'station_code' => $station_code,
                'connector_code' => $connector_code,
                'ts' => time(),
            ]);

            $token = Crypt::encryptString($payload);

            $session = new SessionToken;
            $session->token = $token;
            $session->station_code = $station_code;
            $session->connector_code = $connector_code;
            $session->expires_at = Carbon::now()->addMinutes(5);

            $session->save();
        } catch (\Exception $e) {
            store_error_log($e);
            return response()->view('errors.500', [], 500);
        }

        return redirect()->route('zora.start.session', ['token' => $token]);
    }

    public function session()
    {
        try {
            $token = request('token');

            $record = SessionToken::where('token', $token)->first();
            if (!$record || $record->isExpired()) {
                return response()->view('errors.404', [], 404);
            }

            $decoded = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);

            $station = Station::where('code', $decoded['station_code'])->first();
            $connector = Connector::where('connector_code', $decoded['connector_code'])->first();

            if (!$station || !$connector) {
                return response()->view('errors.404', [], 404);
            }

            // GET PRODUCTS
            $products = Tariff::where('tariff_type', 'minute')
                                ->where('tariff_id', $station->tariff_id)
                                ->where('active', 1)
                                ->first();

            return view('home.index', [
                'station' => $station ?? null,
                'connector' => $connector ?? null,
                'products' => $products ?? collect(),
            ]);

        } catch (\Throwable $e) {
            store_error_log($e);
            return response()->view('errors.500', [], 500);
        }
    }

    public function stop()
    {
        return view('home.stop');
    }

    public function stopAction(Request $request)
    {
        $data = $request->validate([
            'transactionId' => ['required','string','max:50'],
        ]);

        $transaction = Transaction::where('transactionId', $data['transactionId'])->with(['station', 'connector', 'tariff'])->first();
        if (! $transaction) {
            return response()->json([
                'ok' => false,
                'message' => 'Transaction ID not found.',
            ], 404);
        }

        // Update transaction flags
        $transaction->manual_stop = 1;
        $transaction->stop_time = Carbon::now();
        $transaction->save();

        // Enqueue RemoteStopTransaction command
        $connector = $transaction->connector;
        $payload = [];
        // if ($connector && isset($connector->idTag) && $connector->idTag) {
        //     $payload['idTag'] = $connector->idTag;
        // }
        $payload['transactionId'] = $transaction->transactionId;

        RemoteCommand::create([
            'station_id'   => $transaction->station_id,
            'connector_id' => $transaction->connector_id,
            'command'      => 'RemoteStopTransaction',
            'payload'      => !empty($payload) ? json_encode($payload) : null,
            'status'       => 'pending',
        ]);

        // Delete queued stop job if exists
        if (!empty($transaction->id_job_stop)) {
            DB::table('jobs')->where('id', $transaction->id_job_stop)->delete();
            $transaction->id_job_stop = null;
            $transaction->save();
        }

        $response = Http::post(url('/api/stop-transaction'), [
            'station_code' => $transaction->station->code,
            'connector' => $transaction->connector_id,
            'transactionId' => $transaction->transactionId,
            'idTag' => 'CARD',
            'meterStop' => 100,
            'reason' => '-',
            'timestamp' => date('Y-m-d H:i:s'),
            'total_kwh' => 100,
            'total_cost' => 100
        ]);

        $isSendWA = env('IS_SEND_WA');

        if ($isSendWA) {
            $phone = GlobalHelper::phoneConvert($transaction->phone);
            $qontak = new QontakService();

            try {
                $qontak->sendWhatsApp($phone, [
                    "name"            => $transaction->name,
                    "order_id"        => $transaction->midtrans_order_id,
                ]);

                DB::table('transactions')->where('id',$transaction->id)->update([
                    'wa_status'  => 1,
                ]);
            } catch (\Throwable $exception) {
                Log::error('Failed to send whatsapp.', [
                    "name"            => $transaction->name,
                    "order_id"        => $transaction->midtrans_order_id,
                ]);
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Force stop requested.',
        ]);
    }
}


