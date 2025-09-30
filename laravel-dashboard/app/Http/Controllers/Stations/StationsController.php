<?php

namespace App\Http\Controllers\Stations;

use App\Http\Controllers\Controller;

class StationsController extends Controller
{
    public function index()
    {
        $data = [];
        return view('/stations/index', $data);
    }

    public function getData()
    {
        return true;
    }

}
