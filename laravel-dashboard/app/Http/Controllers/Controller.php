<?php

namespace App\Http\Controllers;

use App\Models\Stations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    public $auth;

    public function __construct ()
    {
        $this->auth = Auth::user();
    }
}
