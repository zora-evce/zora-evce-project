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
    protected $idTag;

    /**
     * Create a new job instance.
     */
    public function __construct($stationId, $connectorId, $idTag)
    {
        $this->stationId = $stationId;
        $this->connectorId = $connectorId;
        $this->idTag = $idTag;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        RemoteCommand::create([
            'station_id' => $this->stationId,
            'connector_id' => $this->connectorId,
            'command' => 'RemoteStopTransaction',
            'payload' => json_encode(['idTag' => $this->idTag]),
            'status' => 'pending',
        ]);
    }
}
