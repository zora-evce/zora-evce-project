<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Transaction extends BaseModel
{
    protected $fillable = [
        'transactionId',
        'name',
        'email',
        'phone',
        'station_id',
        'connector_id',
        'tariff_code',
        'executed_price',
        'midtrans_order_id',
        'email_status',
        'wa_status',
        'payment_status',
        'manual_stop',
        'start_time',
        'stop_time',
        // timestamps auto-set
    ];

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function connector()
    {
        return $this->belongsTo(Connector::class, 'connector_id');
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class, 'tariff_code', 'tariff_code');
    }
}
