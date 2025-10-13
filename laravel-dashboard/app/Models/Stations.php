<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\McuProgramM;
use App\Models\McuT;

class Stations extends BaseModel
{
    use HasFactory;
    protected $table = 'stations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'code',
        'name',
        'brand',
        'status',
        'connectivity_status',
        'last_heartbeat_at',
        'connectors_count',
        'vendor',
        'model',
        'firmware_version',
        'deleted_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
