<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;

class PaymentReceipt extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The transaction instance.
     */
    public Transaction $transaction;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction->loadMissing(['station', 'connector', 'tariff']);
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $token = Crypt::encryptString($this->transaction->id);
        $this->transaction->token = $token;

        $subjectId = $this->transaction->transactionId
            ?: ($this->transaction->midtrans_order_id ?? '');
        $subjectSuffix = $subjectId ? " #{$subjectId}" : '';

        return $this->subject("Payment Receipt{$subjectSuffix}")
            ->view('emails.receipt')
            ->with([
                'transaction' => $this->transaction,
            ]);
    }
}


