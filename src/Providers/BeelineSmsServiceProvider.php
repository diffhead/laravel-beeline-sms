<?php

namespace Ggs\LaravelBeelineSms\Providers;

use Illuminate\Support\ServiceProvider;

class BeelineSmsServiceProvider extends ServiceProvider
{
    /**
     * Publishes configuration file.
     *
     * @return  void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/laravel_beeline_sms.php' => config_path('laravel_beeline_sms.php'),
        ], 'laravel_beeline_sms-config');
    }

    /**
     * Make config publishment optional by merging the config from the package.
     *
     * @return  void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/laravel_beeline_sms.php',
            'laravel_beeline_sms'
        );
    }
}