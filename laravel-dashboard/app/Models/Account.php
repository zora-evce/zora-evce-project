<?php

namespace App\Models;

use App\Helpers\GlobalHelper;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends BaseModel
{
    use HasFactory;

    protected $table = 'accounts';
    protected $primaryKey = 'account_id';

    protected $fillable = [
        'account_id',
        'account_code',
        'account_name',
        'contract_number',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public $rules = [
        'account_code' => 'required|unique:accounts,account_code',
        'account_name' => 'required',
    ];

    public $customMessages = [
        'account_code.required' => 'Account Code cannot be empty.',
        'account_code.unique' => 'Account Code already used.',
        'account_name.required' => 'Account Name cannot be empty.',
    ];

    public $attributes = [
        'account_id' => 'account_id',
        'account_code' => 'account_code',
        'account_name' => 'account_name',
        'contract_number' => 'contract_number',
    ];

    public function validate(){
        return GlobalHelper::validation($this->toArray(), $this->rules, $this->customMessages);
    }
}

