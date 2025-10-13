<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'product_id',
        'customer_id',
        'quantity',
        'total_price',
        'customer_name',
        'customer_email',
        'customer_phone'
    ];
}
