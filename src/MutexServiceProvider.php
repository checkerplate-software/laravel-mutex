<?php

namespace CheckerplateSoftware\LaravelMutex;

use Illuminate\Support\ServiceProvider;

class MutexServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/mutex.php', 'mutex'
        );
    }
}