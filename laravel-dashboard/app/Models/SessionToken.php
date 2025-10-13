<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SessionToken extends Model
{
    protected $table = "session_token";

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return now()->greaterThanOrEqualTo($this->expires_at);
    }
}
