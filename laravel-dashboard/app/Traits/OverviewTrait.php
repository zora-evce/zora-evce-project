<?php
namespace App\Traits;

use Illuminate\Http\Request;

trait OverviewTrait
{
    public function indexOverview(Request $request)
    {
        $data = [];
        return view('/stations/details/index-details', $data);
    }
}
