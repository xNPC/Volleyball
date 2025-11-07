<?php

namespace App\Providers;

use App\Services\GroupStandingsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GroupStandingsService::class, function ($app) {
            return new GroupStandingsService();
        });
    }

    public function boot(): void
    {
        //
    }
}
