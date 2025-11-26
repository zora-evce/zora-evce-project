<?php

namespace App\Models;

use App\Helpers\GlobalHelper;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'brand_id',
        'vendor_id',
        'model_id',
        'firmware_version',
        'tariff_id',
        'account_id',
        'address',
        'deleted_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $rules = [
        'code' => 'required',
        'name' => 'required',
        'brand_id' => 'required',
        'vendor_id' => 'required',
        'model_id' => 'required'
    ];

    public $customMessages = [
        'code.required' => 'Station Code cannot be empy.',
        'name.required' => 'Station Name cannot be empty.',
        'brand_id.required' => 'Brand cannot be empty.',
        'vendor_id.required' => 'Vendor cannot be empty.',
        'model_id.required' => 'Model cannot be empty.',
    ];

    public $attributes = [
        'id' => 'id',
        'code' => 'code',
        'name' => 'name',
        'brand_id' => 'brand_id',
        'vendor_id' => 'vendor_id',
        'model_id' => 'model_id',
    ];

    public function validate(){
        return GlobalHelper::validation($this->toArray(), $this->rules, $this->customMessages);
    }
}
