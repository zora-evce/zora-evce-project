<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OcppEventController extends Controller
{
    // ---------------------------------------------------------------------
    // BOOT NOTIFICATION
    // ---------------------------------------------------------------------
    public function bootNotification(Request $r)
    {
        $p = $this->validated($r, [
            'station_code' => ['required','string','max:100'],
            'vendor'       => ['nullable','string','max:100'],
            'model'        => ['nullable','string','max:100'],
            'firmware'     => ['nullable','string','max:100'],
            'timestamp'    => ['nullable','date'],
        ]);
        $now = $this->ts($p['timestamp'] ?? null) ?? now();

        $res = DB::transaction(function () use ($p, $now) {
            $stationId = $this->upsertStation($p['station_code'], [
                'status'              => 'available',
                'connectivity_status' => 'online',
                'last_heartbeat_at'   => $now,
                'vendor'              => $p['vendor']   ?? null,
                'model'               => $p['model']    ?? null,
                'firmware_version'    => $p['firmware'] ?? null,
                'updated_at'          => now(),
            ]);

            $logId = $this->logWebhook('boot-notification', $p, ['ok'=>true], [
                'related_id' => $stationId,
            ]);

            return ['ok'=>true, 'station_id'=>$stationId, 'log_id'=>$logId];
        });

        return response()->json($res);
    }

    // ---------------------------------------------------------------------
    // AUTHORIZE
    // ---------------------------------------------------------------------
    public function authorize(Request $r)
    {
        $p = $this->validated($r, [
            'station_code' => ['required','string','max:100'],
            'idTag'        => ['required','string','max:255'],
            'timestamp'    => ['nullable','date'],
        ]);

        $stationId  = $this->getStationIdOrCreate($p['station_code']);
        $idTag      = $p['idTag'] ?? null;
        $isAllowed  = true;
        $cardStatus = 'unknown';

        // Optional card validation: only enforce if RFID table exists
        if ($idTag && Schema::hasTable('rfid_cards')) {
            $query = DB::table('rfid_cards')->where('id_tag', $idTag);

            if (Schema::hasColumn('rfid_cards', 'is_active')) {
                $query->where('is_active', 1);
            } elseif (Schema::hasColumn('rfid_cards', 'status')) {
                $query->where('status', 'active');
            }

            $card = $query->first();

            if (!$card) {
                $isAllowed  = false;
                $cardStatus = 'rejected';
            } else {
                $cardStatus = 'allowed';
            }
        }

        $result = [
            'ok'          => $isAllowed,
            'card_status' => $cardStatus,
        ];

        // Debug log for authorize: see payload and decision in laravel.log
        Log::info('OCPP authorize handled', [
            'payload' => $p,
            'result'  => $result,
        ]);

        $logId = $this->logWebhook('authorize', $p, $result, ['related_id'=>$stationId]);
        $result['log_id'] = $logId;

        return response()->json($result);
    }

    // ---------------------------------------------------------------------
    // START TRANSACTION  (aligned to your schema)
    // ---------------------------------------------------------------------
    public function startTransaction(Request $r)
    {
        Log::info('OCPP start-transaction request', ['json' => $r->all()]);

        $p = $this->validated($r, [
            'station_code'  => ['required','string','max:100'],
            'connector'     => ['required','integer','min:0'],
            'transactionId' => ['required','string','max:100'],
            'idTag'         => ['nullable','string','max:255'],
            'meterStart'    => ['nullable','numeric'],
            'timestamp'     => ['nullable','date'],
        ]);

        $idk = $r->attributes->get('idempotency_key');

        try {
            $res = DB::transaction(function () use ($p, $idk) {
                if ($idk && $this->idempotentExists('start-transaction', $idk)) {
                    Log::info('OCPP start-transaction idempotent hit', ['idempotency_key' => $idk]);
                    return ['ok'=>true, 'idempotent'=>true];
                }

                [$stationId, $connectorId] = $this->ensureStationAndConnector($p['station_code'], (int)$p['connector']);

                // charging_sessions (only existing columns)
                $sessionId = DB::table('charging_sessions')->insertGetId([
                    'station_id'   => $stationId,
                    'connector_id' => $connectorId,
                    'status'       => 'ongoing',
                    'start_method' => 'webhook',
                    'created_at'   => $this->ts($p['timestamp'] ?? null) ?? now(),
                    'updated_at'   => now(),
                ]);

                // ocpp_start_transactions (matches your table)
                DB::table('ocpp_start_transactions')->insert([
                    'session_id'      => $sessionId,
                    'station_id'      => $stationId,
                    'connector_id'    => $connectorId,
                    'id_tag'          => $p['idTag'] ?? null,
                    'meter_start'     => isset($p['meterStart']) ? (int)$p['meterStart'] : null,
                    'meter_start_kwh' => isset($p['meterStart']) ? ((float)$p['meterStart']/1000.0) : null,
                    'timestamp'       => $this->ts($p['timestamp'] ?? null) ?? now(),
                    'raw'             => json_encode($p),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $logId = $this->logWebhook('start-transaction', $p, ['ok'=>true], [
                    'related_id'      => $sessionId,
                    'idempotency_key' => $idk,
                ]);

                return ['ok'=>true, 'session_id'=>$sessionId, 'log_id'=>$logId];
            });

            return response()->json($res);
        } catch (\Throwable $e) {
            Log::error('OCPP start-transaction failed', ['error' => $e->getMessage()]);
            return response()->json(['ok'=>false, 'error'=>'server_error'], 500);
        }
    }

    // ---------------------------------------------------------------------
    // METER VALUES  (kept generic to likely schema)
    // ---------------------------------------------------------------------
	public function meterValues(Request $r)
	{
	    Log::info('OCPP meter-values request', ['json' => $r->all()]);

            // Patch: normalize transactionId & meterValue from raw.* if root is empty
            $payload = $r->all();
            if (
                (!isset($payload['transactionId']) || $payload['transactionId'] === null || $payload['transactionId'] === '')
                && isset($payload['raw']['transaction_id'])
            ) {
                $payload['transactionId'] = (string) $payload['raw']['transaction_id'];
            }

            if (
                (!isset($payload['meterValue']) || empty($payload['meterValue']))
                && isset($payload['raw']['meter_value'])
            ) {
                $payload['meterValue'] = $payload['raw']['meter_value'];
            }

            // replace request data so validator & business logic see normalized fields
            $r->replace($payload);

	    $p = $this->validated($r, [
	        'station_code'  => ['required','string','max:100'],
	        'connector'     => ['required','integer','min:0'],
	        'transactionId' => ['required','string','max:100'],
	        'meterValue'    => ['required','array'],
	    ]);

	    $idk = $r->attributes->get('idempotency_key');

	    $res = DB::transaction(function () use ($p, $idk) {
	        if ($idk && $this->idempotentExists('meter-values', $idk)) {
	            return ['ok'=>true, 'idempotent'=>true];
	        }

	        [$stationId, $connectorId] = $this->ensureStationAndConnector(
	            $p['station_code'],
	            (int)$p['connector']
	        );

		// 1) Prefer ongoing
		$session = DB::table('charging_sessions')
		    ->select('id')->where('station_id',$stationId)->where('connector_id',$connectorId)
		    ->where('status','ongoing')->latest('id')->first();
		$sessionId = $session->id ?? null;

		// 2) If none, take latest any-status session
		if (!$sessionId) {
		    $any = DB::table('charging_sessions')
		        ->select('id')->where('station_id',$stationId)->where('connector_id',$connectorId)
		        ->latest('id')->first();
		    $sessionId = $any->id ?? null;
		}

		// 3) If still none, auto-create a session so inserts never fail
		if (!$sessionId) {
		    $sessionId = DB::table('charging_sessions')->insertGetId([
		        'station_id'   => $stationId,
		        'connector_id' => $connectorId,
		        'status'       => 'ongoing',
		        'start_method' => 'webhook-auto',
		        'created_at'   => now(),
		        'updated_at'   => now(),
		    ]);
		}

	        foreach ($p['meterValue'] as $mv) {
	            $ts = $this->ts($mv['timestamp'] ?? null) ?? now();

	            // Defaults
	            $energy_kwh = null;
	            $power_kw   = null;
	            $voltage    = null;
	            $current    = null;

	            foreach (($mv['sampledValue'] ?? []) as $sv) {
	                $measurand = strtolower((string)($sv['measurand'] ?? ''));
	                $unit      = strtoupper((string)($sv['unit'] ?? ''));
	                $val       = $sv['value'] ?? null;
	                if (!is_numeric($val)) continue;
	                $num = (float)$val;

	                if ($measurand === 'energy.active.import.register') {
	                    $energy_kwh = ($unit === 'WH') ? ($num / 1000.0) :
	                                  ($unit === 'KWH' ? $num : $energy_kwh);
	                } elseif ($measurand === 'power.active.import') {
	                    $power_kw = ($unit === 'W') ? ($num / 1000.0) :
	                                ($unit === 'KW' ? $num : $power_kw);
	                } elseif ($measurand === 'voltage') {
	                    $voltage = $num;
	                } elseif ($measurand === 'current.import') {
	                    $current = $num;
	                }
	            }

	            DB::table('ocpp_meter_values')->insert([
	                'station_id'       => $stationId,
	                'connector_id'     => $connectorId,
	                'session_id'       => $sessionId,
	                'event_time'       => $ts,                // correct column
	                'meter_value_json' => json_encode($mv),   // correct column
	                'energy_kwh'       => $energy_kwh,
	                'power_kw'         => $power_kw,
	                'voltage'          => $voltage,
	                'current'          => $current,
	                'created_at'       => now(),
	                'updated_at'       => now(),
	            ]);
	        }

	        $logId = $this->logWebhook('meter-values', $p, ['ok'=>true], [
	            'related_id'      => $sessionId,
	            'idempotency_key' => $idk,
	        ]);

	        return ['ok'=>true, 'session_id'=>$sessionId, 'log_id'=>$logId];
	    });

	    return response()->json($res);
	}


    // ---------------------------------------------------------------------
    // STOP TRANSACTION  (best-effort mapping)
    // ---------------------------------------------------------------------
	public function stopTransaction(Request $r)
	{
	    Log::info('OCPP stop-transaction request', ['json' => $r->all()]);

	    $p = $this->validated($r, [
	        'station_code'  => ['required','string','max:100'],
	        'connector'     => ['required','integer','min:0'],
	        'transactionId' => ['required','string','max:100'],
	        'idTag'         => ['nullable','string','max:255'],
	        'meterStop'     => ['nullable','numeric'],
	        'reason'        => ['nullable','string','max:100'],
	        'timestamp'     => ['nullable','date'],
	        'total_kwh'     => ['nullable','numeric'],
	        'total_cost'    => ['nullable','numeric'],
	    ]);

	    $idk = $r->attributes->get('idempotency_key');

	    $res = DB::transaction(function () use ($p, $idk) {
	        if ($idk && $this->idempotentExists('stop-transaction', $idk)) {
	            return ['ok'=>true, 'idempotent'=>true];
	        }

	        [$stationId, $connectorId] = $this->ensureStationAndConnector($p['station_code'], (int)$p['connector']);

	        $session = DB::table('charging_sessions')
	            ->select('id')->where('station_id',$stationId)->where('connector_id',$connectorId)
	            ->latest('id')->first();
	        $sessionId = $session->id ?? null;

	        DB::table('ocpp_stop_transactions')->insert([
	            'session_id'       => $sessionId,
	            'station_id'       => $stationId,
	            'connector_id'     => $connectorId,
	            'event_time'       => $this->ts($p['timestamp'] ?? null) ?? now(),
	            'reason'           => $p['reason'] ?? null,
	            'meter_stop'       => isset($p['meterStop']) ? (int)$p['meterStop'] : null,
	            'meter_stop_kwh'   => isset($p['meterStop']) ? ((float)$p['meterStop'] / 1000.0) : null,
	            'total_energy_kwh' => $p['total_kwh'] ?? null,
	            'total_cost'       => $p['total_cost'] ?? null,
	            'raw'              => json_encode($p),
	            'created_at'       => now(),
	            'updated_at'       => now(),
	        ]);

	        if ($sessionId) {
	            DB::table('charging_sessions')->where('id',$sessionId)->update([
	                'status'     => 'stopped',
	                'end_method' => 'webhook',
	                'updated_at' => now(),
	            ]);
	        }

	        $logId = $this->logWebhook('stop-transaction', $p, ['ok'=>true], [
	            'related_id'      => $sessionId,
	            'idempotency_key' => $idk,
	        ]);

	        return ['ok'=>true, 'session_id'=>$sessionId, 'log_id'=>$logId];
	    });

	    return response()->json($res);
	}


    // ---------------------------------------------------------------------
    // STATUS NOTIFICATION
    // ---------------------------------------------------------------------
	public function statusNotification(Request $r)
	{
	    Log::info('OCPP status-notification request', ['json' => $r->all()]);

	    $p = $this->validated($r, [
	        'station_code' => ['required','string','max:100'],
	        'connector'    => ['required','integer','min:0'],
	        'status'       => ['required','string','max:50'],
	        'errorCode'    => ['nullable','string','max:50'],
	        'timestamp'    => ['nullable','date'],
	    ]);

	    $idk = $r->attributes->get('idempotency_key');

	    $res = DB::transaction(function () use ($p, $idk) {
	        if ($idk && $this->idempotentExists('status-notification', $idk)) {
	            return ['ok'=>true, 'idempotent'=>true];
	        }

	        [$stationId, $connectorId] = $this->ensureStationAndConnector(
	            $p['station_code'], (int)$p['connector']
	        );

	        // normalize status to lowercase to match DB CHECK constraint
	        $status = strtolower($p['status']);

	        // Allowed values in your stations.status check:
	        $allowed = [
	            'available','charging','faulted','preparing','suspended_ev',
	            'suspended_evse','finishing','reserved','unavailable'
	        ];
	        if (!in_array($status, $allowed, true)) {
	            // if unknown, don’t try to set it; just keep log/heartbeat
	            $status = null;
	        }

	        // 1) Mark station online + heartbeat
	        DB::table('stations')->where('id',$stationId)->update([
	            'connectivity_status' => 'online',
	            'last_heartbeat_at'   => $this->ts($p['timestamp'] ?? null) ?? now(),
	            // Optional: set station status if valid
	            ...( $status ? ['status' => $status] : [] ),
	            'updated_at' => now(),
	        ]);

	        // 2) Update connector status if valid
	        if ($status) {
	            DB::table('connectors')->where('id', $connectorId)->update([
	                'status'     => $status,
	                'updated_at' => now(),
	            ]);
	        }

	        $logId = $this->logWebhook('status-notification', $p, ['ok'=>true], [
	            'related_id'      => $connectorId,
	            'idempotency_key' => $idk,
	        ]);

	        return ['ok'=>true, 'station_id'=>$stationId, 'connector_id'=>$connectorId, 'log_id'=>$logId];
	    });

	    return response()->json($res);
	}

    // ---------------------------------------------------------------------
    // HEARTBEAT
    // ---------------------------------------------------------------------
    public function heartbeat(Request $r)
    {
        $p = $this->validated($r, [
            'station_code' => ['required','string','max:100'],
            'timestamp'    => ['nullable','date'],
        ]);

        $stationId = $this->getStationIdOrCreate($p['station_code']);

        DB::table('stations')->where('id',$stationId)->update([
            'connectivity_status' => 'online',
            'last_heartbeat_at'   => $this->ts($p['timestamp'] ?? null) ?? now(),
            'updated_at'          => now(),
        ]);

        $logId = $this->logWebhook('heartbeat', $p, ['ok'=>true], [
            'related_id' => $stationId,
        ]);

        return response()->json(['ok'=>true, 'log_id'=>$logId]);
    }

    // ============================== Helpers =================================

    private function validated(Request $r, array $rules): array
    {
        $in = $r->all();
        // Normalize request field names across different naming conventions and data types.
        // station code alias
        if (!isset($in['station_code']) && isset($in['stationCode'])) {
            $in['station_code'] = $in['stationCode'];
        }

        // connector alias
        if (!isset($in['connector']) && isset($in['connectorId'])) {
            $in['connector'] = $in['connectorId'];
        }
        if (!isset($in['connector']) && isset($in['connector_id'])) {
            $in['connector'] = $in['connector_id'];
        }

        // idTag alias
        if (!isset($in['idTag']) && isset($in['id_tag'])) {
            $in['idTag'] = $in['id_tag'];
        }

        // transactionId → cast ke string
        if (isset($in['transactionId']) && is_numeric($in['transactionId'])) {
            $in['transactionId'] = strval($in['transactionId']);
        }

        // meterValue alias
        if (!isset($in['meterValue']) && isset($in['meter_value'])) {
            $in['meterValue'] = $in['meter_value'];
        }
        if (!isset($in['meterValue']) && isset($in['meterValues'])) {
            $in['meterValue'] = $in['meterValues'];
        }

        return validator($in, $rules)->validate();
    }

    private function ts($maybe): ?Carbon
    {
        try { return $maybe ? Carbon::parse($maybe) : null; } catch (\Throwable $e) { return null; }
    }

    private function upsertStation(string $code, array $updates): int
    {
        $ex = DB::table('stations')->select('id')->where('code',$code)->first();
        if ($ex) { DB::table('stations')->where('id',$ex->id)->update($updates); return (int)$ex->id; }
        return (int) DB::table('stations')->insertGetId(array_merge([
            'code'=>$code,'name'=>$code,'status'=>'available','connectivity_status'=>'offline',
            'created_at'=>now(),'updated_at'=>now(),
        ], $updates));
    }

    private function getStationIdOrCreate(string $code): int
    {
        $row = DB::table('stations')->select('id')->where('code',$code)->first();
        if ($row) return (int)$row->id;
        return (int) DB::table('stations')->insertGetId([
            'code'=>$code,'name'=>$code,'status'=>'available','connectivity_status'=>'offline',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function ensureStationAndConnector(string $stationCode, int $connectorNumber): array
    {
        $stationId = $this->getStationIdOrCreate($stationCode);
        $conn = DB::table('connectors')->select('id')
            ->where('station_id',$stationId)->where('connector_number',$connectorNumber)->first();

        if ($conn) return [$stationId, (int)$conn->id];

        $connectorId = DB::table('connectors')->insertGetId([
            'station_id'=>$stationId,'connector_number'=>$connectorNumber,'status'=>'available',
            'created_at'=>now(),'updated_at'=>now(),
        ]);

        return [$stationId, (int)$connectorId];
    }

    private function idempotentExists(string $type, string $key): bool
    {
        return DB::table('webhook_logs')->where('type',$type)->where('idempotency_key',$key)->exists();
    }

    private function logWebhook(string $type, array $payload, array $response, array $refs = []): int
    {
        return (int) DB::table('webhook_logs')->insertGetId([
            'type'            => $type,
            'related_id'      => $refs['related_id']      ?? null,
            'idempotency_key' => $refs['idempotency_key'] ?? null,
            'payload'         => json_encode($payload),
            'response'        => json_encode($response),
            'status_code'     => 200,
            'received_at'     => now(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }
}
