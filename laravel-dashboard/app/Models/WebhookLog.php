<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $table = "webhook_logs";
    protected $casts = [
        'payload' => 'array',
    ];
}
