<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Transaction;
use App\Models\RemoteCommand;

class EnqueueRemoteStopCommandJob implements ShouldQueue
{
    use Queueable;

    protected $transactionId;
    protected $idTag;

    /**
     * Create a new job instance.
     */
    public function __construct($transactionId, $idTag)
    {
        $this->transactionId = $transactionId;
        $this->idTag = $idTag;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $transaction = Transaction::find($this->transactionId);
        if (!$transaction) return;
        $station = $transaction->station;
        $connector = $transaction->connector;
        if ($station && $connector && $this->idTag) {
            RemoteCommand::create([
                'station_id' => $station->id,
                'connector_id' => $connector->id,
                'command' => 'RemoteStopTransaction',
                'payload' => json_encode(['idTag' => $this->idTag]),
                'status' => 'pending',
            ]);
        }
    }
}
