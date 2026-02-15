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

trait CommandsTrait
{
    public function renderCommands($tab, Stations $station, Request $request)
    {
        $station_id = $station->id;
        $data = null;
        return view('stations.details.partials.' . $tab, get_defined_vars());
    }

    public function startTransactionCommand(Request $request)
    {
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

        $connectors = Connector::where('id', $transaction['connector_id'])->first();
        $id_tag = $connectors['idTag'];

        $tariff = Tariff::where('tariff_code', $transaction['tariff_code'])->first();
        $tariff_value = $tariff['tariff_price'];
        $payload = [
            'idTag' => $id_tag,
            'tariff_value' => $tariff_value,
        ];

        $values = [
            'station_id' => $transaction['station_id'],
            'connector_id' => $transaction['connector_id'],
            'command' => $transaction['RemoteStartTransaction'],
            'payload' => json_encode($payload),
            'status' => 'pending',
        ];

        $remote_commands = RemoteCommand::where('station_id', $transaction['station_id'])
            ->where('connector_id', $transaction['connector_id'])
            ->where('command', 'RemoteStartTransaction')
            ->orderBy('id', 'desc')
            ->first();
        if (!$remote_commands){
            RemoteCommand::create($values);
            return response()->json([
                'ok' => true,
                'message' => 'Force start requested.',
            ]);
        }

        if ($remote_commands->status != 'pending') {
            RemoteCommand::where('station_id', $transaction['station_id'])
                ->where('connector_id', $transaction['connector_id'])
                ->where('command', 'RemoteStartTransaction')
                ->update([
                    'status' => 'pending'
                ]);
            return response()->json([
                'ok' => true,
                'message' => 'Force start requested.',
            ]);
        }
        return response()->json([
            'ok' => true,
            'message' => 'Force start requested.',
        ]);
    }
}
