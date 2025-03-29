<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\UrlGenerator;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        Schema::defaultStringLength(191);

        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }
    }
}
