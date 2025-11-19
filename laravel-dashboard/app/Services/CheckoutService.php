<?php
namespace App\Services;

use Midtrans\Snap;
use Midtrans\Config;
use App\Repositories\TransactionRepositoryInterface;
use App\Models\TransactionidPool;

class CheckoutService
{
    protected $repo;

    public function __construct(TransactionRepositoryInterface $repo)
    {
        $this->repo = $repo;

        // konfigurasi Midtrans di service (jika belum lewat middleware)
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function processCheckout(array $data)
    {
        // --------------------
        // 1. Generate unique transactionId (4-char random, no collision)
        $transactionId = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);

        // 2. Get station and connector (you may need to adapt these if more info needed from $data)
        $station_id = $data['station_id'] ?? null;
        $connector_id = $data['connector_id'] ?? null;
        $tariff_code = $data['tariff_code'] ?? null;

        // 3. Fetch executed_price from Tariff (backend, trustable)
        $executed_price = null;
        if ($tariff_code) {
            $tariff = \App\Models\Tariff::where('tariff_code', $tariff_code)->first();
            $executed_price = $tariff ? $tariff->tariff_price : 0;
        }

        // 4. Save Transaction as per the requirement
        $transaction = $this->repo->create([
            'transactionId'     => $transactionId,
            'name'              => $data['customer_name'] ?? null,
            'email'             => $data['customer_email'] ?? null,
            'phone'             => $data['customer_phone'] ?? null,
            'station_id'        => $station_id,
            'connector_id'      => $connector_id,
            'tariff_code'       => $tariff_code,
            'executed_price'    => $executed_price,
            // midtrans_order_id to be filled after making order_id
            'midtrans_order_id' => null,
            'email_status'      => 0,
            'wa_status'         => 0,
            'payment_status'    => 0,
            'manual_stop'       => 0,
            'start_time'        => null,
            'stop_time'         => null,
        ]);

        $orderId = 'ZOR-' . $transaction->id . '-' . time();
        $transaction->midtrans_order_id = $orderId;
        $transaction->save();

        // Save transactionId to transactionid_pool
        $pool = new TransactionidPool;
        $pool->transactionId = $transactionId;
        $pool->id_transaction = $transaction->id;
        $pool->station_id = $station_id;
        $pool->connector_id = $connector_id;
        $pool->status = 0;

        $pool->save();

        // --------------------
        // Prepare params for Midtrans
        $params = [
            'transaction_details' => [
                'order_id'      => $orderId,
                'gross_amount'  => (float)($executed_price), // enforced from backend
            ],
            'customer_details' => [
                'first_name'    => $data['customer_name'] ?? 'Customer',
                'email'         => $data['customer_email'] ?? null,
                'phone'         => $data['customer_phone'] ?? null,
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return ['transaction' => $transaction, 'snap_token' => $snapToken];
    }
}
