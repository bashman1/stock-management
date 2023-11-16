<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SavingAccountProductService;

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
