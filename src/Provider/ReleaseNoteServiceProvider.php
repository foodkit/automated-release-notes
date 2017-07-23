<?php

namespace FoodKit\ReleaseNote\Provider;

use Illuminate\Support\ServiceProvider;

class ReleaseNoteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/release-notes.php' => config_path('release-notes.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/release-notes.php', 'release-notes');
    }
}
