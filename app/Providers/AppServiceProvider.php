<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SavingAccountProductService;
use App\Services\CommissionConfigService;
use App\Services\MailSenderService;
use App\Services\ScheduledTaskService;

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
        $this->app->singleton(MailSenderService::class, function ($app){
            return new MailSenderService();
        });
        $this->app->singleton(ScheduledTaskService::class, function ($app){
            return new ScheduledTaskService(new MailSenderService());
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
