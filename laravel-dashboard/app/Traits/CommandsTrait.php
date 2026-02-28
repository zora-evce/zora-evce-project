<?php
namespace App\Traits;

use App\Helpers\GlobalHelper;
use App\Models\Connector;
use App\Models\Connectors;
use App\Models\RemoteCommand;
use App\Models\Stations;
use App\Models\Tariff;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait CommandsTrait
{
    public function renderCommands($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $data = self::getBundleDataCommands($station_id);
        return view('stations.details.partials.' . $tab, get_defined_vars());
    }

    private static function getBundleDataCommands($station_id)
    {
        $transactionStartIds = self::getTransactions($station_id, 'start');
        $transactionStopIds = self::getTransactions($station_id, 'stop');
        return [
            'transactions' => [
                'transactionStartIds' => $transactionStartIds,
                'transactionStopIds' => $transactionStopIds
            ]
        ];
    }

    private static function getTransactions($station_id, $type = 'stop')
    {
        $query = Transaction::query()
            ->select([
                'id',
                'transactionId',
                'name',
                'station_id',
                'connector_id',
                DB::raw("extract(epoch from (stop_time - start_time)) as duration_seconds"),
            ])
            ->where('station_id', $station_id)
            ->where('payment_status', 1);
        if ($type == 'start') {
            $query = $query->whereColumn('stop_time', '>', 'start_time');
        }
        $data = $query->orderByDesc('id')
            ->limit(5)
            ->get();
        return $data;
    }

    public function startTransactionCommand(Request $request)
    {
        Log::info($request->post());
        $data = $request->validate([
            'transactionId' => ['required','string','max:50'],
        ]);

        $transaction = Transaction::where('transactionId', $data['transactionId'])
            ->where('payment_status', 1)
            ->orderBy('id', 'desc')
            ->first();

        if (!$transaction) {
            return response()->json([
                'ok' => false,
                'message' => 'Transaction ID not found.',
            ], 404);
        }
        GlobalHelper::enqueueRemoteStartCommand($transaction);
        return response()->json([
            'ok' => true,
            'message' => 'Force start requested.',
        ]);
    }
}
