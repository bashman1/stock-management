<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SavingAccountProductService;
use App\Services\CommissionConfigService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SavingAccountProductService::class, function ($app) {
            return new SavingAccountProductService();
        });
        $this->app->singleton(CommissionConfigService::class, function ($app){
            return new CommissionConfigService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
