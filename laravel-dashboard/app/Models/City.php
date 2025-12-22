<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Model;

class City extends BaseModel
{
    protected $table = 'city';
    protected $fillable = [
        'city_id',
        'city_code',
        'city_name',
        'deleted_at',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
}
