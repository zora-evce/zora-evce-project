<?php
namespace App\Services;

use Midtrans\Snap;
use Midtrans\Config;
use App\Repositories\TransactionRepositoryInterface;

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
        // simpan dulu transaksi (status pending)
        $transaction = $this->repo->create([
            'product_id' => $data['product_id'],
            'customer_id' => $data['customer_id'],
            'quantity' => $data['quantity'],
            'total_price' => $data['total_price'],
            'status' => 'pending',
        ]);

        $orderId = 'ORDER-' . $transaction->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (float)$data['total_price'],
            ],
            'customer_details' => [
                'first_name' => $data['customer_name'] ?? 'Customer',
                'email' => $data['customer_email'] ?? null,
                'phone' => $data['customer_phone'] ?? null,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        // simpan midtrans_order_id jika perlu
        $transaction->midtrans_order_id = $orderId;
        $transaction->save();

        return ['transaction' => $transaction, 'snap_token' => $snapToken];
    }
}
