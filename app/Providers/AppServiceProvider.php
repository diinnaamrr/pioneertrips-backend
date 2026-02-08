<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Force HTTPS if APP_URL is HTTPS or in production
        if (config('app.url') && str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        } elseif($this->app->environment('live', 'production')) {
            URL::forceScheme('https');
        }
        Paginator::useBootstrap();
    }
}
