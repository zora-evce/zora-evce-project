<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Models extends BaseModel
{
    protected $table = 'models';
    protected $fillable = [
        'model_id',
        'model_code',
        'model_name',
        'deleted_at',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
}
