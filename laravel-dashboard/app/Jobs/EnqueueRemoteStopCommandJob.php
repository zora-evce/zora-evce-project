<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Transaction;
use App\Models\RemoteCommand;

class EnqueueRemoteStopCommandJob implements ShouldQueue
{
    use Queueable;

    protected $stationId;
    protected $connectorId;
    protected $transactionId;

    /**
     * Create a new job instance.
     */
    public function __construct($stationId, $connectorId, $transactionId)
    {
        $this->stationId = $stationId;
        $this->connectorId = $connectorId;
        $this->transactionId = $transactionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $payload = [];
        $payload['transactionId'] = $this->transactionId;

        RemoteCommand::create([
            'station_id' => $this->stationId,
            'connector_id' => $this->connectorId,
            'command' => 'RemoteStopTransaction',
            'payload' => !empty($payload) ? json_encode($payload) : null,
            'status' => 'pending',
        ]);
    }
}
