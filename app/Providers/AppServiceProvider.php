<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SavingAccountProductService;
use App\Services\CommissionConfig;

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
        $this->app->singleton(CommissionConfig::class, function ($app){
            return new CommissionConfig();
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
