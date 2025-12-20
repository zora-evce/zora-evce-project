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
use App\Models\Station;
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
        $ts = $this->ts($p['timestamp'] ?? null) ?? now();


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
            $p['station_code'] = $stationCode;
            $p['connector']    = $connectorNum;
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
            $p['station_code'] = $stationCode;
            $p['connector']    = $connectorNum;
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
            // 1. Pastikan station & connector ada (auto-create bila belum)
            //    Helper ini sudah ada di bawah: ensureStationAndConnector()
            [$stationId, $connectorId] = $this->ensureStationAndConnector($stationCode, $connectorNum);

            // GET stations
            $station = Station::where('code', $p['station_code'])->first();

            // GET transactionId from pool
		    // $pool = TransactionidPool::with('transaction.tariff')
            //                             ->where('station_id', $station->id)
            //                             ->where('connector_id', $p['connector'])
            //                             ->where('status', 0)
            //                             ->orderBy('id', 'desc')
            //                             ->first();
		    $pool = TransactionidPool::with('transaction')
                                        ->where('station_id', $stationId)
                                        ->where('connector_id', $connectorId)
                                        ->where('status', 0)
                                        ->orderBy('id', 'desc')
                                        ->first();
            // if ($pool) {
                // OVERWRITE transactionId from OCPP
				// $p['transactionId'] = $pool->transactionId;

                // DB::beginTransaction();

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
                // $startId = DB::table('ocpp_start_transactions')->insertGetId([
                //     'session_id'       => $sessionId,
                //     'station_id'       => $stationId,
                //     'connector_id'     => $connectorId,
                //     'id_tag'           => $idTag,
                //     'meter_start'      => $p['meterStart'] ?? null,
                //     'meter_start_kwh'  => null,                  // nanti diisi kalau sudah ada konversi
                //     'timestamp'        => $ts,
                //     'raw'              => json_encode($p['raw'] ?? $p),
                //     'created_at'       => now(),
                //     'updated_at'       => now(),
                // ]);
    
                // DB::commit();

                // 4. Log ke webhook_logs untuk tracking di dashboard
                $p['station_code'] = $stationCode;
                $p['connector']    = $connectorNum;

            // 3. Insert ke ocpp_start_transactions
            $startId = DB::table('ocpp_start_transactions')->insertGetId([
                'session_id'      => $sessionId,
                'station_id'      => $stationId,
                'connector_id'    => $connectorId,
                'id_tag'          => $idTag,
                'meter_start'     => $p['meterStart'] ?? null,
                'meter_start_kwh' => isset($p['meterStart']) ? ((float) $p['meterStart'] / 1000) : null,
                'timestamp'       => $ts,
                'raw'             => json_encode($p['raw'] ?? null),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

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
                    'related_id' => $stationId,
                ]
            );

          // 3b. Update transactionid_pool: isi id_transaction (OCPP transactionId) untuk kode transaksi yang sedang aktif
            if (!empty($p['transactionId'])) {
                DB::table('transactionid_pool')
                    ->where('station_id', (string) $stationId)   // stations.id disimpan varchar
                    ->where('connector_id', (int) $connectorId)  // connectors.id
                    ->where('status', 0)                         // row yang masih available
                    ->orderByDesc('id')
                    ->limit(1)
                    ->update([
                        // 'id_transaction' => (int) $p['transactionId'], // OCPP transactionId
                        'status'         => 1,
                        'updated_at'     => now(),
                    ]);
            }


            // // SET JOBS TO STOP REMOTE
            $delayMinutes = ($pool->transaction->duration) * 60;
            $job = EnqueueRemoteStopCommandJob::dispatch($stationId, $connectorId, $p->idTag)
                                        ->delay(now()->addMinutes($delayMinutes));
            // $jobId = $job->getJobId();

            // SET start_time and id_job_stop ON transactions
            $transaction = Transaction::find($pool->id_transaction);
            $transaction->start_time = date('Y-m-d H:i:s');
            $transaction->trx_id = $p['transactionId'];
            $transaction->id_job_stop = $jobId;
            $transaction->save();

            return $this->reply(true, 'StartTransaction saved');
            // }
        } catch (\Throwable $e) {

            // DB::rollBack();

            // Tetap log errornya ke webhook_logs
            $p['station_code'] = $stationCode;
            $p['connector']    = $connectorNum;

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
            'connector'    => ['nullable', 'integer'],
            'connectorId'  => ['nullable', 'integer'],
            'timestamp'    => ['nullable', 'string'],
            'meterValue'   => ['nullable'],
            'values'       => ['nullable'],
            'raw'          => ['nullable'],
        ]);

        $stationCode  = $p['station_code'] ?? null;

        $connectorNum = (int) ($p['connector'] ?? $p['connectorId'] ?? 0);
        if ($connectorNum <= 0) {
            return response()->json([
                'ok' => false,
                'message' => 'Missing connector or connectorId'
            ], 422);
        }

        $mvTs = $p['meterValue'][0]['timestamp'] ?? null;
        $ts   = $this->ts($p['timestamp'] ?? $mvTs ?? null) ?? now();


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
            // Tentukan payload meter value yang akan disimpan
            $mvJson = $p['values'] ?? null;

            if (empty($mvJson)) {
                // 1) kalau ada meterValue langsung (ambil item pertama biar format "lama")
                $mvJson = $p['meterValue'][0] ?? ($p['meterValue'] ?? null);

                // 2) kalau tidak ada, coba ambil dari raw wrapper: raw['meter_value']
                if (empty($mvJson)) {
                    $raw = $p['raw'] ?? null;
                    if (is_array($raw) && !empty($raw['meter_value'])) {
                        $mvJson = $raw['meter_value'][0] ?? $raw['meter_value'];
                    }
                }

                // 3) fallback terakhir (jangan kosong)
                if (empty($mvJson)) {
                    $mvJson = $p['raw'] ?? $p;
                }
            }

            DB::table('ocpp_meter_values')->insert([
                'station_id'       => $station->id,
                'connector_id'     => $connector->id,
                'session_id'       => $sessionId,
                'event_time'       => $ts,
                'meter_value_json' => json_encode($mvJson),
                'energy_kwh'       => null,
                'power_kw'         => null,
                'voltage'          => null,
                'current'          => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::commit();

            /**
             * 5. Log ke webhook_logs untuk debugging
             */
            $p['station_code'] = $stationCode;
            $p['connector']    = $connectorNum;
            $this->logWebhook(
                'MeterValues',
                $p,
                [
                    'ok'          => true,
                    'session_id'  => $sessionId,
                    'station_id'  => $station->id,
                    'connector_id'=> $connector->id,
                ],
                [
                    'related_id' => $station->id
                ]
            );

            return $this->reply(true, 'MeterValues saved');

        } catch (\Throwable $e) {

            DB::rollBack();

            // log juga errornya
            $p['station_code'] = $stationCode;
            $p['connector']    = $connectorNum;
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

            $meterStop = isset($p['meterStop']) ? (float) $p['meterStop'] : null;

            $start = DB::table('ocpp_start_transactions')
                ->where('session_id', $sessionId)
                ->orderByDesc('id')
                ->first();

            $meterStart = $start?->meter_start ? (float) $start->meter_start : null;

            $meterStopKwh = $meterStop !== null ? ($meterStop / 1000) : null;
            $totalEnergyKwh = ($meterStop !== null && $meterStart !== null)
                ? max(0, ($meterStop - $meterStart) / 1000)
                : null;

            // 3. → Insert ke ocpp_stop_transactions
            $stopId = DB::table('ocpp_stop_transactions')->insertGetId([
                'session_id'       => $sessionId,
                'station_id'       => $stationId,
                'connector_id'     => $connectorId,
                'event_time'       => $ts,
                'reason'           => $p['reason'] ?? null,
                'meter_stop'       => $p['meterStop'] ?? null,
                'meter_stop_kwh'   => $meterStopKwh,
                'total_energy_kwh' => $totalEnergyKwh,
                'total_cost'       => null,
                'raw'              => $p['raw'] ?? $p,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // 3c. Update tabel transactions (jika ada mapping OCPP transactionId)
            if (!empty($p['transactionId'])) {
                $trx = Transaction::where('transactionId', (string) $p['transactionId'])->first();
                if ($trx) {
                    $trx->stop_time = now();
                    $trx->save();
                }
            }

            // Update session energy
            DB::table('charging_sessions')->where('id', $sessionId)->update([
                'total_energy_kwh' => $totalEnergyKwh,
                'updated_at'       => now(),
            ]);

            // if (!empty($p['transactionId'])) {
            //     DB::table('transactionid_pool')
            //         ->where('id_transaction', (int) $p['transactionId'])
            //         ->where('station_id', (string) $stationId)
            //         ->where('connector_id', (int) $connectorId)
            //         ->where('status', 1)
            //         ->update([
            //             'status' => 0,
            //             'updated_at' => now(),
            //         ]);
            // }

            DB::commit();

            // 4. → Logging ke webhook_logs
            $p['station_code'] = $stationCode;
            $p['connector']    = $connectorNum;
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
                    'related_id' => $stationId,
                ]
            );

            // SET stop_time and id_job_stop ON transactions
			$transaction = Transaction::where("transactionId", $p['transactionId'])
                                        ->orderBy('id', 'desc')
                                        ->first();
			$transaction->stop_time = date('Y-m-d H:i:s');
			$transaction->save();

            // SEND WA
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

            return $this->reply(true, 'StopTransaction saved');

        } catch (\Throwable $e) {
            DB::rollBack();

            // Tetap log errornya
            $p['station_code'] = $stationCode;
            $p['connector']    = $connectorNum;
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
                            'end_method' => 'auto',
                            'updated_at' => now(),
                        ]);
                }
            }

            DB::commit();

            /**
             * 5. Logging ke webhook_logs
             */
            $p['station_code'] = $stationCode;
            $p['connector']    = $connectorNum;
            $this->logWebhook(
                'StatusNotification',
                $p,
                [
                    'ok'          => true,
                    'station_id'  => $station->id,
                    'connector_id'=> $connector->id,
                    'status'      => $status,
                    'error'       => $p['errorCode'] ?? null,
                ],
                [
                    'related_id' => $station->id
                ]
            );

            return $this->reply(true, 'StatusNotification saved');

        } catch (\Throwable $e) {

            DB::rollBack();
            $p['station_code'] = $stationCode;
            $p['connector']    = $connectorNum;
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
            'connector'    => ['nullable', 'integer'],
            'timestamp'    => ['nullable', 'string'],
            'raw'          => ['nullable'],
        ]);

        $stationCode  = $p['station_code'];
        $connectorNum = $p['connector'] ?? 1;
        $ts           = $this->ts($p['timestamp']) ?? now();

        try {
            DB::beginTransaction();

            // 1. Pastikan station & connector ada
            [$stationId, $connectorId] = $this->ensureStationAndConnector($stationCode, (int)$connectorNum);

            // 2. Update station heartbeat — JANGAN UPDATE kolom status!
            DB::table('stations')->where('id', $stationId)->update([
                'last_heartbeat_at' => $ts,
                'updated_at'        => now(),
            ]);

            // 3. Update connector connectivity_status
            if ($connectorId) {
                DB::table('connectors')->where('id', $connectorId)->update([
                    'connectivity_status' => 'online',
                    'updated_at'          => now(),
                ]);
            }

            // 4. Log ke ocpp_events
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

            // 5. Webhook logs
            $p['station_code'] = $stationCode;
            $p['connector']    = $connectorNum;

            $this->logWebhook(
                'Heartbeat',
                $p,
                [
                    'ok'          => true,
                    'station_id'  => $stationId,
                    'connector_id'=> $connectorId,
                ],
                [
                    'related_id' => $stationId
                ]
            );

            return $this->reply(true, 'Heartbeat saved');

        } catch (\Throwable $e) {

            DB::rollBack();

            $p['station_code'] = $stationCode;
            $p['connector']    = $connectorNum;

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

    private function getStationIdOrCreate(string $stationCode): int
    {
        $row = DB::table('stations')->where('code', $stationCode)->first();
        if ($row) {
            return (int)$row->id;
        }

        return (int) DB::table('stations')->insertGetId([
            'code'              => $stationCode,
            'name'              => $stationCode,
            'vendor'            => null,
            'model'             => null,
            'firmware'          => null,
            'connectors_count'  => 0,
            'status'            => 'offline',
            'last_heartbeat_at' => null,
            'created_at'        => now(),
            'updated_at'        => now(),
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
        // Create or fetch station
        $stationId = $this->getStationIdOrCreate($stationCode);

        // Fetch connector
        $connector = DB::table('connectors')
            ->where('station_id', $stationId)
            ->where('connector_number', $connectorNumber)
            ->first();

        if (!$connector) {
            $connectorId = DB::table('connectors')->insertGetId([
                'station_id'       => $stationId,
                'connector_number' => $connectorNumber,
                'status'           => 'available',
                'connectivity_status' => 'offline',
                'power_kw'         => null,
                'last_status_at'   => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Update connectors_count
            DB::table('stations')->where('id', $stationId)->increment('connectors_count');

            return [$stationId, $connectorId];
        }

        return [$stationId, $connector->id];
    }



    private function idempotentExists(string $type, string $key): bool
    {
        return DB::table('webhook_logs')->where('type',$type)->where('idempotency_key',$key)->exists();
    }

    /**
     * Writes OCPP event logs safely into webhook_logs.
     *
     * @param string $type
     * @param array  $payload
     * @param array  $meta
     * @param array  $extra
     * @return int
     */
    private function logWebhook(string $type, array $payload = [], array $meta = [], array $extra = []): int
    {
        try {

            $record = [
                'type'         => $type,
                'station_code' => $payload['station_code'] ?? ($extra['station_code'] ?? null),
                'connector'    => $payload['connector'] ?? ($extra['connector'] ?? null),
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

            return (int) DB::table('webhook_logs')->insertGetId([
                'type'        => $type . '_Error',
                'level'       => 'error',
                'payload'     => json_encode($payload),
                'response'    => json_encode(['error' => $e->getMessage()]),
                'received_at' => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    /**
     * Standard OCPP API reply format.
     *
     * @param bool $ok
     * @param string|null $message
     * @param int $httpCode
     * @param array $extra
     * @return \Illuminate\Http\JsonResponse
     */
    private function reply(bool $ok, ?string $message = null, int $httpCode = 200, array $extra = [])
    {
        $response = array_merge([
            'ok'      => $ok,
            'message' => $message,
        ], $extra);

        return response()->json($response, $httpCode);
    }


}
