<?php

namespace App\Providers;

use App\Transports\Mail\MandrillTransport;
use Illuminate\Mail\MailServiceProvider;

class CustomMailServiceProvider extends MailServiceProvider
{
    /**
     * Register the Swift Transport instance.
     *
     * @return void
     */
    protected function registerSwiftTransport()
    {
        parent::registerSwiftTransport();
        $this->app['swift.transport']->extend('mandrill_api', static function ($app) {
            $config = $app['config']->get('services.mandrill', []);
    
            return new MandrillTransport($config);
        });
    }
}