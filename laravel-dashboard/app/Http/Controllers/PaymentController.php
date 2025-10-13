<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CheckoutService;
use Midtrans\Config;
use Midtrans\Snap;

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
            'product_id','customer_id','quantity','total_price',
            'name','email','phone_number'
        ]);

        $result = $this->checkoutService->processCheckout([
            'product_id' => $data['product_id'],
            'customer_id' => $data['customer_id'],
            'quantity' => $data['quantity'],
            'total_price' => $data['total_price'],
            'customer_name' => $data['name'],
            'customer_email' => $data['email'],
            'customer_phone' => $data['phone_number'],
        ]);

        return response()->json($result);
    }

    // webhook handler
    public function handleWebhook(Request $request)
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
        $transaction = \App\Models\Transaction::where('midtrans_order_id', $orderId)->first();
        if (!$transaction) {
            return response()->json(['message' => 'transaction not found'], 404);
        }

        // contoh mapping status midtrans -> local
        if ($request->transaction_status === 'settlement' || $request->transaction_status === 'capture') {
            $transaction->status = 'paid';
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
