<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\McuProgramM;
use App\Models\McuT;

class Connectors extends BaseModel
{
    use HasFactory;
    protected $table = 'connectors';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'station_id',
        'connecator_number',
        'status',
        'power_kw',
        'connector_code',
        'last_status_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
