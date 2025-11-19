<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Tariff extends BaseModel
{
    protected $table = "tariff";
    protected $fillable = [
        'tariff_id',
        'deleted_at',
        'created_at',
        'updated_at',
    ];
}
