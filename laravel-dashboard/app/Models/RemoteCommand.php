<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoteCommand extends Model
{
    protected $table = "remote_commands";
    protected  $fillable = [
        'station_id',
        'connector_id',
        'command',
        'payload',
        'status',
        'created_at',
        'updated_at'
    ];
}
