<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;
use App\Models\Transaction;

class TransactionidPool extends BaseModel
{
    protected $table = "transactionid_pool";

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transactionId', 'transactionId');
    }
}
