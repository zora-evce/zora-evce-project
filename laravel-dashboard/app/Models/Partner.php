<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partner extends BaseModel
{
    use HasFactory;

    protected $table = 'partners';
    protected $primaryKey = 'partner_id';

    protected $fillable = [
        'partner_id',
        'partner_code',
        'partner_name',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
    ];
}
