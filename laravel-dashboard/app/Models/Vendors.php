<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Vendors extends BaseModel
{
    protected $table = 'vendors';
    protected $fillable = [
        'vendor_id',
        'vendor_code',
        'vendor_name',
        'deleted_at',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
}
