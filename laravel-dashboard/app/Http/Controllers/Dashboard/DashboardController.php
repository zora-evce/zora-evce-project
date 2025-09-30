<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class DashboardController extends Controller
{
    public function index() {
        $allRoutes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => $route->gatherMiddleware(),
                'prefix' => $route->getPrefix(),
            ];
        });
        $groupedRoutes = $allRoutes->groupBy('prefix');
        // return $allRoutes;
        $data = [];
        return view('/dashboard/dashboard', $data);
    }

}
