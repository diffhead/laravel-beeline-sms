<?php

namespace SaintSample\LaravelBeelineSms\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use SaintSample\LaravelBeelineSms\Contracts\BeelineSmsMessageContract;
use SaintSample\LaravelBeelineSms\Jobs\UpdateStatusJob;

class BeelineSmsServiceProvider extends ServiceProvider
{
    //TODO do auto publish config
    /**
     * Publishes configuration file.
     *
     * @return  void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/laravel_beeline_sms.php' => config_path('laravel_beeline_sms.php'),
        ], 'laravel_beeline_sms-config');

        $this->publishes([
            __DIR__ . '/../../database/migrations/create_sms_messages_tables.php.stub' => $this->getMigrationFileName('create_sms_messages_tables.php'),
        ], 'laravel_beeline_sms-migrations');


        //TODO needs hard refactoring
        $this->app->booted(function () {

            /**
             * @var Schedule $schedule
             */
            $schedule = $this->app->make(Schedule::class);

            if (config('laravel_beeline_sms.messages.auto_update_statuses')) {
                $schedule->call(function () {
                    /**
                     * @var Model $model
                     */
                    $model = App::make(config('laravel_beeline_sms.messages.model'));

                    $model::query()
                        ->whereNotIn(
                            $model->getStatusField(),
                            [
                                'delivered',
                                'rejected',
                                'undeliverable',
                                'error',
                                'delivered',
                            ]
                        )
                        ->get()
                        ->each(function (BeelineSmsMessageContract $model) {
                            UpdateStatusJob::dispatch($model);
                        });
                })->everyFiveMinutes();
            }
        });
    }

    /**
     * Make config publishment optional by merging the config from the package.
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind('laravel-beeline-sms', function () {
            return App::make(App::make('config')->get('laravel_beeline_sms.driver'));
        });

        $this->mergeConfigFrom(
            __DIR__.'/../../config/laravel_beeline_sms.php',
            'laravel_beeline_sms'
        );
    }

    protected function getMigrationFileName($migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path.'*_'.$migrationFileName);
            })
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}