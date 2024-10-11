<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RequestLoggingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->make(\Illuminate\Contracts\Http\Kernel::class)
            ->pushMiddleware(\App\Http\Middleware\RequestLoggingMiddleware::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('request_logging', function ($app) {
            return new \App\Http\RequestLogging\RequestLoggingManager($app);
        });
        $this->app->singleton(\App\Contracts\Http\RequestLogging\Store::class, function ($app) {
            return $app['request_logging']->driver();
        });
        $this->app->singleton(\App\Contracts\Http\RequestLogging\Repository::class,
            \App\Http\RequestLogging\Repository::class);
    }
}
