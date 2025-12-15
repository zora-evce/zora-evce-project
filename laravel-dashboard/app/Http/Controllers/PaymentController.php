<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CheckoutService;
use App\Models\Transaction;
use App\Helpers\GlobalHelper;
use App\Mail\PaymentReceipt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\SessionToken;

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
            'quantity', 'duration', 'name', 'email', 'phone_number', 'station_id', 'connector_id', 'tariff_code'
        ]);

        $result = $this->checkoutService->processCheckout([
            'quantity'        => $data['quantity'] ?? null,
            'duration'        => $data['duration'] ?? null,
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

        $shouldEnqueueRemoteStart = false;
        $shouldSendReceipt = false;

        if ($request->transaction_status === 'settlement' || $request->transaction_status === 'capture') {
            $transaction->payment_status = '1';
            $shouldEnqueueRemoteStart = true;
            $shouldSendReceipt = true;
        } elseif ($request->transaction_status === 'deny' || $request->transaction_status === 'cancel') {
            // $transaction->status = 'failed';
            $transaction->payment_status = '3';
        } elseif ($request->transaction_status === 'expire') {
            // $transaction->status = 'expired';
            $transaction->payment_status = '5';
        } else {
            // $transaction->status = $request->transaction_status;
            $transaction->payment_status = '7';
        }

        $transaction->save();

        if ($shouldEnqueueRemoteStart) {
            // After successful payment, enqueue RemoteStartTransaction command via Helper
            GlobalHelper::enqueueRemoteStartCommand($transaction);
        }

        if ($shouldSendReceipt) {
            $emailStatus = 0;
            try {
                Mail::to($transaction->email)->send(new PaymentReceipt($transaction));
                $emailStatus = 1;
            } catch (\Throwable $exception) {
                Log::error('Failed to send payment receipt email.', [
                    'transaction_id' => $transaction->id,
                    'order_id' => $transaction->midtrans_order_id,
                    'error' => $exception->getMessage(),
                ]);
            }

            // Update email_status directly in database using DB facade to ensure it's saved
            $updated = DB::table('transactions')
                ->where('id', $transaction->id)
                ->update(['email_status' => (int)$emailStatus]);

            // Log for debugging
            Log::info('Email status update attempt', [
                'transaction_id' => $transaction->id,
                'email_status' => $emailStatus,
                'rows_affected' => $updated,
            ]);

            // Refresh the model instance to reflect the updated value
            $transaction->refresh();

            // try {
            //     $phone = GlobalHelper::formatPhoneToInternational($transaction->phone);
            //     $customer_name = $transaction->name;
            //     $station_name = $transaction->station->name;
            //     $connector_number = $transaction->connector_id;
            //     $start_time = date("Y-m-d H:i");
            //     $amount = number_format($transaction->executed_price, 0, ',', '.');
            //     $company_name = "Zora";

            //     $wa = Http::withToken(env('WHATSAPP_TOKEN'))
            //             ->post('https://graph.facebook.com/v18.0/' . env('WHATSAPP_PHONE_ID') . '/messages', [
            //                 'messaging_product' => 'whatsapp',
            //                 'to' => $phone,
            //                 'type' => 'text',
            //                 'text' => [
            //                     'body' => "Hello {$customer_name},\n\n".
            //                             "Your payment for the EV charging session has been *successfully received*. ⚡\n\n".
            //                             "🔋 *Transaction Details:*\n".
            //                             "• Station: {$station_name}\n".
            //                             "• Connector: {$connector_number}\n".
            //                             "• Start time: {$start_time}\n".
            //                             "• Total payment: Rp{$amount}\n\n".
            //                             "You can now start your charging session via the app or directly at the station.\n\n".
            //                             "Thank you for choosing {$company_name}! 🌱\n\n".
            //                             "—\n".
            //                             "_This is an automated message. Please do not reply._"
            //                 ]
            //             ]);
            // } catch (\Throwable $exception) {
            //     Log::error('Failed to send payment receipt whatsapp.', [
            //         'transaction_id' => $transaction->id,
            //         'order_id' => $transaction->midtrans_order_id,
            //         'error' => $exception->getMessage(),
            //     ]);
            // }
        }

        return response()->json(['message' => 'ok']);
    }

    // simple status endpoint for client polling by Midtrans order_id
    public function status(string $orderId)
    {
        $transaction = Transaction::where('midtrans_order_id', $orderId)->first();
        if (!$transaction) {
            return response()->json(['exists' => false], 404);
        }
        return response()->json([
            'exists' => true,
            'payment_status' => (int) ($transaction->payment_status ?? 0),
        ]);
    }

    // post-payment thank you page
    public function after()
    {
        // If token is passed, delete it from session_tokens table
        $token = request('token');
        if ($token) {
            try {
                SessionToken::where('token', $token)->delete();
            } catch (\Throwable $e) {
                Log::warning('Failed deleting session token on post-payment page', [
                    'token' => substr($token, 0, 16) . '...',
                    'error' => $e->getMessage(),
                ]);
            }
        }
        return view('home.after');
    }
}
