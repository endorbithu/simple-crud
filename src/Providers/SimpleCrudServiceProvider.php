<?php

namespace Endorbit\SimpleCrud\Providers;

use Endorbit\SimpleCrud\Console\Commands\DeleteOldActivityLog;
use Illuminate\Console\Scheduling\Schedule;

class SimpleCrudServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot()
    {
        include __DIR__ . '/../../routes/web.php';
        $this->publishes([
            __DIR__ . '/../../publish/simplecrud.php' => config_path('simplecrud.php'),
            __DIR__ . '/../../publish/SimpleCrudExample.php' => app_path('SimpleCrud/SimpleCrudExample.php'),
            __DIR__ . '/../../publish/SimpleCrudListener.php' => app_path('SimpleCrud/SimpleCrudListener.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

            $this->commands([DeleteOldActivityLog::class]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('simplecrud:deleteoldactivitylog')->dailyAt('00:00');
            });
        }
    }

    public function register()
    {
        $this->loadViewsFrom(__DIR__ . '/../../views', 'simplecrud');
        $this->app->register(EventServiceProvider::class);

        parent::register();
    }
}

