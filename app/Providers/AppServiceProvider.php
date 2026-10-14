<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
    public function boot(): void
    {
        // Serverless hosts such as Vercel terminate TLS at the edge, so the
        // request reaching PHP is plain HTTP. Force HTTPS when generating URLs.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
