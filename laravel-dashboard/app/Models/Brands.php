<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Brands extends BaseModel
{
    protected $table = 'brands';
    protected $fillable = [
        'brand_id',
        'brand_code',
        'brand_name',
        'deleted_at',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
}
