<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Services\QontakService;
use App\Helpers\GlobalHelper;
use App\Models\TransactionidPool;
use App\Models\Transaction;
use App\Jobs\EnqueueRemoteStopCommandJob;



class OcppEventController extends Controller
{
    // ---------------------------------------------------------------------
    // BOOT NOTIFICATION
    // ---------------------------------------------------------------------
    public function bootNotification(Request $r)
    {
        $p = $this->validated($r, [
            'station_code'          => ['required', 'string', 'max:100'],
            'chargePointVendor'     => ['nullable', 'string', 'max:255'],
            'chargePointModel'      => ['nullable', 'string', 'max:255'],
            'chargeBoxSerialNumber' => ['nullable', 'string', 'max:255'],
            'firmwareVersion'       => ['nullable', 'string', 'max:255'],
            'iccid'                 => ['nullable', 'string', 'max:255'],
            'imsi'                  => ['nullable', 'string', 'max:255'],
            'meterSerialNumber'     => ['nullable', 'string', 'max:255'],
            'meterType'             => ['nullable', 'string', 'max:255'],
            'timestamp'             => ['nullable', 'string'],
            'connector'             => ['nullable', 'integer'], // optional
            'raw'                   => ['nullable'],            // isi payload mentah dari Python
        ]);

        $stationCode = $p['station_code'];
        $connectorNum = $p['connector'] ?? 1;
        $ts = $this->ts($p['timestamp']) ?? now();

        try {
            DB::beginTransaction();

            /**
             * 1. Pastikan station & connector sudah terdaftar
             *    (helper ini sudah kamu punya)
             */
            [$stationId, $connectorId] = $this->ensureStationAndConnector($stationCode, (int)$connectorNum);

            /**
             * 2. Update informasi device pada tabel stations
             */
            DB::table('stations')->where('id', $stationId)->update([
                'vendor'         => $p['chargePointVendor'] ?? null,
                'model'          => $p['chargePointModel'] ?? null,
                'serial_number'  => $p['chargeBoxSerialNumber'] ?? null,
                'firmware'       => $p['firmwareVersion'] ?? null,
                'iccid'          => $p['iccid'] ?? null,
                'imsi'           => $p['imsi'] ?? null,
                'meter_serial'   => $p['meterSerialNumber'] ?? null,
                'meter_type'     => $p['meterType'] ?? null,
                'last_boot_at'   => $ts,
                'updated_at'     => now(),
            ]);

            /**
             * 3. Simpan event BootNotification ke tabel ocpp_events
             */
            DB::table('ocpp_events')->insert([
                'station_id' => $stationId,
                'connector_id' => $connectorId,
                'name' => 'BootNotification',
                'level' => 'info',
                'detail' => json_encode($p),
                'event_time' => $ts,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            /**
             * 4. Logging ke webhook_logs (supaya bisa dilihat di dashboard)
             */
            $this->logWebhook(
                'BootNotification',
                $p,
                [
                    'ok'          => true,
                    'station_id'  => $stationId,
                    'connector_id'=> $connectorId,
                ],
                [
                    'related_id' => $stationId,
                ]
            );

            return $this->reply(true, 'BootNotification saved');

        } catch (\Throwable $e) {

            DB::rollBack();

            // Tetap log error untuk debugging
            $this->logWebhook(
                'BootNotification',
                $p,
                [
                    'ok'    => false,
                    'error' => $e->getMessage(),
                ]
            );

            return $this->reply(false, $e->getMessage(), 500);
        }
    }


    // ---------------------------------------------------------------------
    // AUTHORIZE
    // ---------------------------------------------------------------------
    public function authorize(Request $r)
    {
        $p = $this->validated($r, [
            'station_code' => ['required', 'string', 'max:100'],
            'idTag'        => ['required', 'string', 'max:255'],
            'connector'    => ['nullable', 'integer'], // optional, kalau Python kirim connector
            'timestamp'    => ['nullable', 'date'],
        ]);

        // Pastikan station + optional connector tercatat rapi di DB
        $stationCode  = $p['station_code'];
        $stationId    = $this->getStationIdOrCreate($stationCode);
        $connectorId  = null;

        if (isset($p['connector'])) {
            // Kalau ada connector, pastikan row di connectors ada
            [$stationIdFromEnsure, $connectorId] = $this->ensureStationAndConnector(
                $stationCode,
                (int) $p['connector']
            );
            $stationId = $stationIdFromEnsure;
        }

        $idTag      = $p['idTag'] ?? null;
        $isAllowed  = true;
        $cardStatus = 'unknown';

        // Optional card validation: hanya kalau tabel rfid_cards memang ada
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
            'ok'           => $isAllowed,
            'card_status'  => $cardStatus,
            'station_id'   => $stationId,
            'connector_id' => $connectorId,
        ];

        // Debug ke laravel.log
        Log::info('OCPP authorize handled', [
            'payload'      => $p,
            'result'       => $result,
            'station_code' => $stationCode,
            'station_id'   => $stationId,
            'connector_id' => $connectorId,
        ]);

        // Simpan juga ke webhook_logs supaya konsisten dengan event lain
        $logId = $this->logWebhook('authorize', $p, $result, [
            'related_id' => $stationId,
        ]);

        $result['log_id'] = $logId;

        return response()->json($result);
    }


    // ---------------------------------------------------------------------
    // START TRANSACTION  (final, aligned to schema & helpers)
    // ---------------------------------------------------------------------
    public function startTransaction(Request $r)
    {
        $p = $this->validated($r, [
            'station_code'   => ['required', 'string', 'max:100'],
            'connector'      => ['required', 'integer'],
            'transactionId'  => ['nullable'],                // dari OCPP (string/number), disimpan di log saja
            'idTag'          => ['nullable', 'string', 'max:255'],
            'meterStart'     => ['nullable', 'numeric'],
            'timestamp'      => ['nullable', 'string'],
            'raw'            => ['nullable'],                // payload mentah dari Python (optional)
        ]);

        $stationCode  = $p['station_code'];
        $connectorNum = (int) $p['connector'];
        $idTag        = $p['idTag'] ?? null;
        $ts           = $this->ts($p['timestamp']) ?? now();

        try {
            DB::beginTransaction();

            // 1. Pastikan station & connector ada (auto-create bila belum)
            //    Helper ini sudah ada di bawah: ensureStationAndConnector()
            [$stationId, $connectorId] = $this->ensureStationAndConnector($stationCode, $connectorNum);

            // 2. Buat charging session baru
            $sessionId = DB::table('charging_sessions')->insertGetId([
                'station_id'   => $stationId,
                'connector_id' => $connectorId,
                'status'       => 'ongoing',
                'start_method' => 'webhook',
                'created_at'   => $ts,
                'updated_at'   => $ts,
            ]);

            // 3. Insert ke ocpp_start_transactions
            $startId = DB::table('ocpp_start_transactions')->insertGetId([
                'session_id'       => $sessionId,
                'station_id'       => $stationId,
                'connector_id'     => $connectorId,
                'id_tag'           => $idTag,
                'meter_start'      => $p['meterStart'] ?? null,
                'meter_start_kwh'  => null,                  // nanti diisi kalau sudah ada konversi
                'timestamp'        => $ts,
                'raw'              => json_encode($p['raw'] ?? $p),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::commit();

            // 4. Log ke webhook_logs untuk tracking di dashboard
            $this->logWebhook(
                'StartTransaction',
                $p,
                [
                    'ok'          => true,
                    'session_id'  => $sessionId,
                    'start_id'    => $startId,
                    'station_id'  => $stationId,
                    'connector_id'=> $connectorId,
                ],
                [
                    'related_id' => $sessionId,
                ]
            );

            return $this->reply(true, 'StartTransaction saved');

        } catch (\Throwable $e) {

            DB::rollBack();

            // Tetap log errornya ke webhook_logs
            $this->logWebhook(
                'StartTransaction',
                $p,
                [
                    'ok'    => false,
                    'error' => $e->getMessage(),
                ]
            );

            return $this->reply(false, $e->getMessage(), 500);
        }
    }




    // ---------------------------------------------------------------------
    // METER VALUES  (kept generic to likely schema)
    // ---------------------------------------------------------------------
    public function meterValues(Request $r)
    {
        $p = $this->validated($r, [
            'station_code' => ['required', 'string', 'max:100'],
            'connector'    => ['required', 'integer'],
            'timestamp'    => ['nullable', 'string'],
            'values'       => ['nullable'], // sesuai payload Python OCPP server
            'raw'          => ['nullable'], // payload mentah
        ]);

        $stationCode  = $p['station_code'];
        $connectorNum = (int) $p['connector'];
        $ts           = $this->ts($p['timestamp']) ?? now();

        try {
            DB::beginTransaction();

            /**
             * 1. Ambil station
             */
            $station = DB::table('stations')
                ->where('code', $stationCode)
                ->first();

            if (!$station) {
                throw new \Exception("Station not found: {$stationCode}");
            }

            /**
             * 2. Ambil connector
             */
            $connector = DB::table('connectors')
                ->where('station_id', $station->id)
                ->where('connector_number', $connectorNum)
                ->first();

            if (!$connector) {
                throw new \Exception("Connector not found: {$stationCode} / {$connectorNum}");
            }

            /**
             * 3. Ambil session aktif (ongoing)
             */
            $session = DB::table('charging_sessions')
                ->where('station_id', $station->id)
                ->where('connector_id', $connector->id)
                ->where('status', 'ongoing')
                ->orderByDesc('id')
                ->first();

            /**
             * Kalau tidak ada session → buat dummy session agar logging tetap jalan
             * MeterValues tidak boleh gagal
             */
            if (!$session) {
                $sessionId = DB::table('charging_sessions')->insertGetId([
                    'station_id'   => $station->id,
                    'connector_id' => $connector->id,
                    'status'       => 'ongoing',
                    'start_method' => 'unknown',
                    'created_at'   => $ts,
                    'updated_at'   => $ts,
                ]);
            } else {
                $sessionId = $session->id;
            }

            /**
             * 4. Insert ke ocpp_meter_values
             */
            DB::table('ocpp_meter_values')->insert([
                'station_id'      => $station->id,
                'connector_id'    => $connector->id,
                'session_id'      => $sessionId,
                'event_time'      => $ts,
                'meter_value_json'=> json_encode($p['values'] ?? $p['raw'] ?? $p),
                'energy_kwh'      => null, // bisa dihitung jika meterStart/Stop tersedia
                'power_kw'        => null,
                'voltage'         => null,
                'current'         => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::commit();

            /**
             * 5. Log ke webhook_logs untuk debugging
             */
            $this->logWebhook(
                'MeterValues',
                $p,
                [
                    'ok'          => true,
                    'session_id'  => $sessionId,
                    'station_id'  => $station->id,
                    'connector_id'=> $connector->id,
                ]
            );

            return $this->reply(true, 'MeterValues saved');

        } catch (\Throwable $e) {

            DB::rollBack();

            // log juga errornya
            $this->logWebhook(
                'MeterValues',
                $p,
                [
                    'ok'    => false,
                    'error' => $e->getMessage()
                ]
            );

            return $this->reply(false, $e->getMessage(), 500);
        }
    }




     // ---------------------------------------------------------------------
    // STOP TRANSACTION  (final, clean, aligned with DB schema)
    // ---------------------------------------------------------------------
    public function stopTransaction(Request $r)
    {
        $p = $this->validated($r, [
            'station_code'   => ['required', 'string', 'max:100'],
            'connector'      => ['required', 'integer'],
            'transactionId'  => ['nullable'],              // OCPP transactionId (log only)
            'idTag'          => ['nullable', 'string'],
            'meterStop'      => ['nullable', 'numeric'],
            'timestamp'      => ['nullable', 'string'],
            'reason'         => ['nullable', 'string'],
            'raw'            => ['nullable'],
        ]);

        $stationCode  = $p['station_code'];
        $connectorNum = (int) $p['connector'];
        $ts           = $this->ts($p['timestamp']) ?? now();

        try {
            DB::beginTransaction();

            // 1. → Dapatkan station_id & connector_id (auto-create bila belum ada)
            [$stationId, $connectorId] = $this->ensureStationAndConnector($stationCode, $connectorNum);

            // 2. → Cari charging session ongoing
            $session = DB::table('charging_sessions')
                ->where('station_id', $stationId)
                ->where('connector_id', $connectorId)
                ->where('status', 'ongoing')
                ->orderByDesc('id')
                ->first();

            if (!$session) {
                // Jika session tidak ditemukan, buat session dummy agar StopTransaction tidak error
                $sessionId = DB::table('charging_sessions')->insertGetId([
                    'station_id'   => $stationId,
                    'connector_id' => $connectorId,
                    'status'       => 'stopped',
                    'start_method' => 'unknown',
                    'end_method'   => 'webhook',
                    'created_at'   => $ts,
                    'updated_at'   => $ts,
                ]);
            } else {
                $sessionId = $session->id;

                // Tutup session
                DB::table('charging_sessions')
                    ->where('id', $sessionId)
                    ->update([
                        'status'     => 'stopped',
                        'end_method' => 'webhook',
                        'updated_at' => now(),
                    ]);
            }

            // 3. → Insert ke ocpp_stop_transactions
            $stopId = DB::table('ocpp_stop_transactions')->insertGetId([
                'session_id'       => $sessionId,
                'station_id'       => $stationId,
                'connector_id'     => $connectorId,
                'event_time'       => $ts,
                'reason'           => $p['reason'] ?? null,
                'meter_stop'       => $p['meterStop'] ?? null,
                'meter_stop_kwh'   => null,
                'total_energy_kwh' => null,
                'total_cost'       => null,
                'raw'              => json_encode($p['raw'] ?? $p),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::commit();

            // 4. → Logging ke webhook_logs
            $this->logWebhook(
                'StopTransaction',
                $p,
                [
                    'ok'          => true,
                    'session_id'  => $sessionId,
                    'stop_id'     => $stopId,
                    'station_id'  => $stationId,
                    'connector_id'=> $connectorId,
                ],
                [
                    'related_id' => $sessionId,
                ]
            );

            return $this->reply(true, 'StopTransaction saved');

        } catch (\Throwable $e) {
            DB::rollBack();

            // Tetap log errornya
            $this->logWebhook(
                'StopTransaction',
                $p,
                [
                    'ok'    => false,
                    'error' => $e->getMessage(),
                ]
            );

            return $this->reply(false, $e->getMessage(), 500);
        }
    }





    // ---------------------------------------------------------------------
    // STATUS NOTIFICATION
    // ---------------------------------------------------------------------
    public function statusNotification(Request $r)
    {
        $p = $this->validated($r, [
            'station_code'     => ['required', 'string', 'max:100'],
            'connector'        => ['required', 'integer'],
            'status'           => ['required', 'string', 'max:40'],
            'errorCode'        => ['nullable', 'string', 'max:40'],
            'info'             => ['nullable', 'string'],
            'timestamp'        => ['nullable', 'string'],
            'raw'              => ['nullable'],
        ]);

        $stationCode  = $p['station_code'];
        $connectorNum = (int) $p['connector'];
        $ts           = $this->ts($p['timestamp']) ?? now();
        $status       = strtolower($p['status']);

        try {
            DB::beginTransaction();

            /**
             * 1. Cari station
             */
            $station = DB::table('stations')
                ->where('code', $stationCode)
                ->first();

            if (!$station) {
                throw new \Exception("Station not found: {$stationCode}");
            }

            /**
             * 2. Cari connector
             */
            $connector = DB::table('connectors')
                ->where('station_id', $station->id)
                ->where('connector_number', $connectorNum)
                ->first();

            if (!$connector) {
                throw new \Exception("Connector not found: {$stationCode} / {$connectorNum}");
            }

            /**
             * 3. Update connector status
             */
            DB::table('connectors')
                ->where('id', $connector->id)
                ->update([
                    'status'        => $status,
                    'last_status_at'=> $ts,
                    'updated_at'    => now(),
                ]);

            /**
             * 4. Handle automatic session transitions
             */
            if ($status === 'charging') {

                // Apakah ada session ongoing?
                $session = DB::table('charging_sessions')
                    ->where('station_id', $station->id)
                    ->where('connector_id', $connector->id)
                    ->where('status', 'ongoing')
                    ->first();

                if (!$session) {
                    // Buat session baru jika charger mulai charging tanpa StartTransaction
                    $sessionId = DB::table('charging_sessions')->insertGetId([
                        'station_id'   => $station->id,
                        'connector_id' => $connector->id,
                        'start_method' => 'auto',
                        'status'       => 'ongoing',
                        'created_at'   => $ts,
                        'updated_at'   => $ts,
                    ]);
                }
            }

            if ($status === 'available' || $status === 'finishing') {

                // Cari session ongoing
                $session = DB::table('charging_sessions')
                    ->where('station_id', $station->id)
                    ->where('connector_id', $connector->id)
                    ->where('status', 'ongoing')
                    ->orderByDesc('id')
                    ->first();

                if ($session) {
                    // Tutup session otomatis
                    DB::table('charging_sessions')
                        ->where('id', $session->id)
                        ->update([
                            'status'     => 'stopped',
                            'end_method' => 'status-notification',
                            'updated_at' => now(),
                        ]);
                }
            }

            DB::commit();

            /**
             * 5. Logging ke webhook_logs
             */
            $this->logWebhook(
                'StatusNotification',
                $p,
                [
                    'ok'          => true,
                    'station_id'  => $station->id,
                    'connector_id'=> $connector->id,
                    'status'      => $status,
                    'error'       => $p['errorCode'] ?? null,
                ]
            );

            return $this->reply(true, 'StatusNotification saved');

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->logWebhook(
                'StatusNotification',
                $p,
                [
                    'ok'    => false,
                    'error' => $e->getMessage(),
                ]
            );

            return $this->reply(false, $e->getMessage(), 500);
        }
    }



    // ---------------------------------------------------------------------
    // HEARTBEAT
    // ---------------------------------------------------------------------
    public function heartbeat(Request $r)
    {
        $p = $this->validated($r, [
            'station_code' => ['required', 'string', 'max:100'],
            'connector'    => ['nullable', 'integer'], // optional
            'timestamp'    => ['nullable', 'string'],
            'raw'          => ['nullable'],
        ]);

        $stationCode  = $p['station_code'];
        $connectorNum = $p['connector'] ?? 1;
        $ts           = $this->ts($p['timestamp']) ?? now();

        try {
            DB::beginTransaction();

            /**
             * 1. Pastikan station & connector ada
             */
            [$stationId, $connectorId] = $this->ensureStationAndConnector($stationCode, (int)$connectorNum);

            /**
             * 2. Update last heartbeat waktu
             */
            DB::table('stations')->where('id', $stationId)->update([
                'last_heartbeat_at' => $ts,
                'updated_at'        => now(),
            ]);

            /**
             * 3. Update connector sebagai online (opsional)
             */
            DB::table('connectors')->where('id', $connectorId)->update([
                'connectivity_status' => 'online',
                'updated_at'          => now(),
            ]);

            /**
             * 4. Simpan event ke ocpp_events (untuk riwayat)
             */
            DB::table('ocpp_events')->insert([
                'station_id'   => $stationId,
                'connector_id' => $connectorId,
                'name'         => 'Heartbeat',
                'level'        => 'info',
                'detail'       => json_encode($p),
                'event_time'   => $ts,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            DB::commit();

            /**
             * 5. Logging ke webhook_logs
             */
            $this->logWebhook(
                'Heartbeat',
                $p,
                [
                    'ok'          => true,
                    'station_id'  => $stationId,
                    'connector_id'=> $connectorId,
                ]
            );

            return $this->reply(true, 'Heartbeat saved');

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->logWebhook(
                'Heartbeat',
                $p,
                [
                    'ok'    => false,
                    'error' => $e->getMessage(),
                ]
            );

            return $this->reply(false, $e->getMessage(), 500);
        }
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

/**
 * Pastikan station & connector ada.
 * - Station di-handle oleh getStationIdOrCreate()
 * - Connector dicari berdasarkan (station_id, connector_number)
 * - Jika tidak ada, dibuat baru dengan status 'available'
 * - Sekaligus update stations.connectors_count
 */
    private function ensureStationAndConnector(string $stationCode, int $connectorNumber): array
    {
        // 1. Pastikan station sudah ada
        $stationId = $this->getStationIdOrCreate($stationCode);

        // 2. Cari connector existing (hanya yang tidak soft-deleted)
        $connector = DB::table('connectors')
            ->select('id')
            ->where('station_id', $stationId)
            ->where('connector_number', $connectorNumber)
            ->whereNull('deleted_at')
            ->first();

        if ($connector) {
            return [$stationId, (int) $connector->id];
        }

        // 3. Buat connector baru kalau belum ada
        $connectorId = DB::table('connectors')->insertGetId([
            'station_id'       => $stationId,
            'connector_number' => $connectorNumber,
            'status'           => 'available',
            'power_kw'         => null,
            'last_status_at'   => null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // 4. Update jumlah connector di stations (aman untuk 1–2 connector)
        DB::table('stations')
            ->where('id', $stationId)
            ->increment('connectors_count', 1);

        return [$stationId, (int) $connectorId];
    }


    private function idempotentExists(string $type, string $key): bool
    {
        return DB::table('webhook_logs')->where('type',$type)->where('idempotency_key',$key)->exists();
    }

/**
 * Save OCPP event into webhook_logs table.
 * This function must NEVER throw an exception.
 */
    private function logWebhook(string $type, array $payload = [], array $meta = [], array $extra = []): int
    {
        try {
            $record = [
                'type'         => $type,
                'station_code' => $payload['station_code'] ?? null,
                'connector'    => $payload['connector'] ?? null,
                'level'        => $meta['level'] ?? 'info',
                'status'       => $meta['status'] ?? null,
                'related_id'   => $extra['related_id'] ?? null,
                'payload'      => json_encode($payload),
                'response'     => json_encode($meta),
                'received_at'  => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ];

            return (int) DB::table('webhook_logs')->insertGetId($record);

        } catch (\Throwable $e) {
            // fallback logging, cannot fail
            return (int) DB::table('webhook_logs')->insertGetId([
                'type'        => 'WebhookError',
                'level'       => 'error',
                'payload'     => json_encode($payload),
                'response'    => json_encode(['error' => $e->getMessage()]),
                'received_at' => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

}
