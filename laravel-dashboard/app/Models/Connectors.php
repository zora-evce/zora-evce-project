<?php

namespace App\Models;

use App\Helpers\GlobalHelper;
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
        'connector_number',
        'status',
        'power_kw',
        'connector_code',
        'last_status_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $rules = [
        'station_id' => 'required',
        'connector_number' => 'required|unique:connectors,connector_number',
        'power_kw' => 'required',
        'connector_code' => 'required|unique:connectors,connector_code'
    ];

    public $customMessages = [
        'station_id.required' => 'Station ID cannot be empty.',
        'connector_number.required' => 'Connector Number cannot be empty.',
        'connector_number.unique' => 'Connector Number already used.',
        'power_kw.required' => 'Power KW be empty.',
        'connector_code.required' => 'Connector Code cannot be empty.',
        'connector_code.unique' => 'Connector Code already used.'
    ];

    public $attributes = [
        'id' => 'id',
        'station_id' => 'station_id',
        'connector_number' => 'connector_number',
        'power_kw' => 'power_kw',
        'connector_code' => 'connector_code',
    ];

    public function validate(){
        return GlobalHelper::validation($this->toArray(), $this->rules, $this->customMessages);
    }

}
