<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client;


class TwilioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');

            if (!$sid || !$token) {
                throw new \Exception('Twilio credentials are not set.');
            }

            return new Client($sid, $token);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
