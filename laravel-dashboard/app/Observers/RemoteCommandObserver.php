<?php

namespace App\Observers;

use App\Models\RemoteCommand;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class RemoteCommandObserver
{
    private const REDIS_CHANNEL = 'ocpp:commands';

    /**
     * Handle the RemoteCommand "created" event.
     * Publish command to Redis when a new command is created with status 'pending'.
     */
    public function created(RemoteCommand $remoteCommand): void
    {
        if ($remoteCommand->status === 'pending') {
            $this->publishToRedis($remoteCommand);
        }
    }

    /**
     * Publish command to Redis channel.
     */
    private function publishToRedis(RemoteCommand $remoteCommand): void
    {
        try {
            // Get station code from station_id
            $station = DB::table('stations')
                ->where('id', $remoteCommand->station_id)
                ->select('code')
                ->first();

            if (!$station) {
                \Log::warning("RemoteCommandObserver: Station not found for station_id: {$remoteCommand->station_id}");
                return;
            }

            // Get connector number if connector_id exists
            $connectorNumber = null;
            if ($remoteCommand->connector_id) {
                $connector = DB::table('connectors')
                    ->where('id', $remoteCommand->connector_id)
                    ->select('connector_number')
                    ->first();
                $connectorNumber = $connector ? $connector->connector_number : null;
            }

            // Prepare payload
            $payload = $remoteCommand->payload ? json_decode($remoteCommand->payload, true) : [];

            // Build Redis message
            $redisPayload = [
                'id' => $remoteCommand->id,
                'command' => $remoteCommand->command,
                'cp_id' => $station->code, // station code (ChargePoint ID)
                'station_id' => $remoteCommand->station_id,
                'connector_id' => $remoteCommand->connector_id,
                'connector' => $connectorNumber, // connector number for OCPP
                'payload' => $payload,
                'status' => $remoteCommand->status,
            ];

            // Publish to Redis
            Redis::publish(self::REDIS_CHANNEL, json_encode($redisPayload));

            \Log::info("RemoteCommandObserver: Published command to Redis", [
                'command_id' => $remoteCommand->id,
                'cp_id' => $station->code,
                'command' => $remoteCommand->command,
            ]);
        } catch (\Exception $e) {
            \Log::error("RemoteCommandObserver: Failed to publish to Redis", [
                'command_id' => $remoteCommand->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

