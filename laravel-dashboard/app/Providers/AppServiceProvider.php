<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\TransactionRepositoryInterface;
use App\Repositories\TransactionRepository;
use App\Support\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        View::composer('*', function ($view) use ($request) {
            $view->with('breadcrumbs', Breadcrumbs::generate($request));
        });
    }
}
