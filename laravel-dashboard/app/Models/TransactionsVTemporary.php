<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\McuProgramM;
use App\Models\McuT;

class TransactionsVTemporary extends BaseModel
{
    use HasFactory;
    protected $table = 'transactions_v_temporary';
}
