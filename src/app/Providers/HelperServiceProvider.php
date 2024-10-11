<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(OfficeAllyHelper::class, function ($app) {
            return function(string $accountName) : OfficeAllyHelper
            {
                return new OfficeAllyHelper($accountName);
            };
        });
    }
}
