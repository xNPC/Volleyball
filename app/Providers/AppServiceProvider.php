<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Services\GroupStandingsService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GroupStandingsService::class, function ($app) {
            return new GroupStandingsService();
        });

        $this->app->bind(ResetsUserPasswords::class, ResetUserPassword::class);
    }

    public function boot(): void
    {
        //Paginator::useBootstrapFive();
        Paginator::useBootstrapFour();
    }
}
