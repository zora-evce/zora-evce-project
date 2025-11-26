<?php
namespace App\Helpers;

use App\Models\LaboratoryExaminationM;
use App\Models\LookupC;
use App\Models\PackageM;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\RemoteCommand;
use App\Jobs\EnqueueRemoteStopCommandJob;

class GlobalHelper {
    public static function validation($data, $rules, $messages, $attributes = null){
        if (isset($data)) {
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                // return json_decode($validator->messages(), true);
                $string = implode(', ', $validator->messages()->all());
                return $string;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public static function dataTable($request, $query)
    {
        $totalRecords = $query->count();
        $search = [];

        if($request->has('search')){
            foreach ($request['columns'] as $column) {
                if (isset($column['searchable']) && $column['searchable'] === 'true' && isset($column['data'])) {
                    $search[] = $column['data'];
                }
            }
        }
        if ($request->has('search') && !empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];
            $query = $query->where(function ($q) use ($search, $searchValue) {
                foreach ($search as $column) {
                    if (strpos($column, '.') === false) {
                        $q->orWhere($column, 'ilike', '%' . $searchValue . '%');
                    } else {
                        $arr = explode(".", $column);
                        $q->orWhereHas($arr[0], function ($qRel) use ($arr, $searchValue) {
                            $qRel->where($arr[1], 'ilike', '%' . $searchValue . '%');
                        });
                    }
                }
            });
        }
        $filteredRecords = $query->count();
        if ($request->has('order') && is_array($request->order)) {
            foreach ($request->order as $order) {
                $columnIndex = $order['column'];
                $columnName = $request->columns[$columnIndex]['data'];
                $direction = $order['dir'];
                $query = $query->orderBy($columnName, $direction);
            }
        }
        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $data = $query->offset($start)->limit($length)->get();
        return [
            'draw' => $request->draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];
    }

    /**
     * Enqueue a RemoteStartTransaction command after successful payment.
     * Accepts a Transaction Eloquent model instance.
     *
     * @param Transaction $transaction
     * @return bool True if enqueued, false if not applicable.
     */
    public static function enqueueRemoteStartCommand(Transaction $transaction): bool
    {
        $station = $transaction->station;
        $connector = $transaction->connector;
        $tariff = $transaction->tariff;
        // Default payload
        $payload = [];
        if ($connector && isset($connector->idTag)) {
            $payload['idTag'] = $connector->idTag;
        }
        if ($tariff && isset($tariff->tariff_price)) {
            $payload['tariff_value'] = $tariff->tariff_price;
        }
        if ($station && $connector && !empty($payload)) {
            RemoteCommand::create([
                'station_id' => $station->id,
                'connector_id' => $connector->id,
                'command' => 'RemoteStartTransaction',
                'payload' => json_encode($payload),
                'status' => 'pending',
            ]);
            // Dispatch the RemoteStopTransaction job after X minutes
            // if ($tariff && isset($tariff->tariff_value) && is_numeric($tariff->tariff_value) && $tariff->tariff_value > 0 && isset($connector->idTag)) {
            //     $delayMinutes = (int) $tariff->tariff_value;
            //     EnqueueRemoteStopCommandJob::dispatch($transaction->id, $connector->idTag)
            //         ->delay(now()->addMinutes($delayMinutes));
            // }
            return true;
        }
        return false;
    }

    function formatPhoneToInternational($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    public static function generateDailyUniqueCode()
    {
        do {
            // generate 5 digit
            $code = random_int(10000, 99999);

            // cek apakah code sudah dipakai hari ini
            $exists = \App\Models\Transaction::whereDate('created_at', today())
                ->where('transactionId', $code)
                ->exists();

        } while ($exists);

        return $code;
    }

    public static function phoneConvert($number)
    {
        // Hapus semua karakter non-digit
        $number = preg_replace('/\D/', '', $number);

        // Jika sudah mulai 62
        if (strpos($number, '62') === 0) {
            return $number;
        }

        // Jika mulai 0 → ganti 62
        if (strpos($number, '0') === 0) {
            return '62' . substr($number, 1);
        }

        // Jika tanpa 0 & 62 (misal 812xxxx)
        if (strpos($number, '8') === 0) {
            return '62' . $number;
        }

        return $number; // fallback
    }
}
