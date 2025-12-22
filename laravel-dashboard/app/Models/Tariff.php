<?php

namespace App\Models;

use App\Helpers\GlobalHelper;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Tariff extends BaseModel
{
    protected $table = "tariff";
    protected $primaryKey = 'tariff_id';
    protected $fillable = [
        'tariff_id',
        'tariff_code',
        'tariff_name',
        'tariff_type',
        'tariff_value',
        'tariff_price',
        'tax_rate',
        'active',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public $rules = [
        // 'tariff_id' => 'required',
        'tariff_code' => 'required|unique:tariff,tariff_code',
        'tariff_name' => 'required',
        'tariff_type' => 'required',
        'tariff_value' => 'required',
        'tariff_price' => 'required',
    ];

    public $customMessages = [
        // 'tariff_id.required' => 'Tariff ID cannot be empty.',
        'tariff_code.required' => 'Tariff Code cannot be empty.',
        'tariff_code.unique' => 'Tariff Code already used.',
        'tariff_name.required' => 'Tariff Name cannot be empty.',
        'tariff_type.required' => 'Tariff Type cannot be empty.',
        'tariff_value.required' => 'Tariff Value cannot be empty.',
        'tariff_price.required' => 'Tariff Price cannot be empty.',
    ];

    public $attributes = [
        'tariff_id' => 'tariff_id',
        'tariff_code' => 'tariff_code',
        'tariff_name' => 'tariff_name',
        'tariff_type' => 'tariff_type',
        'tariff_value' => 'tariff_value',
        'tariff_price' => 'tariff_price',
        'tax_rate' => 'tax_rate',
        'active' => 'active'
    ];

    public function validate(){
        return GlobalHelper::validation($this->toArray(), $this->rules, $this->customMessages);
    }
}
