<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CheckoutService;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Transaction;
use App\Models\RemoteCommand;
use App\Helpers\GlobalHelper;

class PaymentController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function index()
    {
        return view('home.pay');
    }

    public function checkout(Request $request)
    {
        $data = $request->only([
            'quantity',
            'name', 'email', 'phone_number', 'station_id', 'connector_id', 'tariff_code'
        ]);

        $result = $this->checkoutService->processCheckout([
            'quantity'        => $data['quantity'] ?? null,
            'customer_name'   => $data['name'] ?? null,
            'customer_email'  => $data['email'] ?? null,
            'customer_phone'  => $data['phone_number'] ?? null,
            'station_id'      => $data['station_id'] ?? null,
            'connector_id'    => $data['connector_id'] ?? null,
            'tariff_code'     => $data['tariff_code'] ?? null,
        ]);
        return response()->json($result);
    }

    // webhook handler
    public function notification(Request $request)
    {
        $serverKey = config('midtrans.server_key');

        $signatureKey = hash("sha512",
            $request->order_id .
            $request->status_code .
            $request->gross_amount .
            $serverKey
        );

        if ($signatureKey !== $request->signature_key) {
            return response()->json(['message' => 'invalid signature'], 403);
        }

        // ambil order id (sesuaikan dengan penyimpanan)
        $orderId = $request->order_id;
        // temukan transaksi berdasarkan midtrans_order_id lalu update status
        $transaction = Transaction::where('midtrans_order_id', $orderId)->first();
        if (!$transaction) {
            return response()->json(['message' => 'transaction not found'], 404);
        }

        // contoh mapping status midtrans -> local
        if ($request->transaction_status === 'settlement' || $request->transaction_status === 'capture') {
            $transaction->payment_status = '200';
            $transaction->save();

            // After successful payment, enqueue RemoteStartTransaction command via Helper
            GlobalHelper::enqueueRemoteStartCommand($transaction);

        } elseif ($request->transaction_status === 'deny' || $request->transaction_status === 'cancel') {
            $transaction->status = 'failed';
        } elseif ($request->transaction_status === 'expire') {
            $transaction->status = 'expired';
        } else {
            $transaction->status = $request->transaction_status;
        }

        $transaction->save();

        return response()->json(['message' => 'ok']);
    }
}
