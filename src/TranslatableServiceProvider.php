<?php

namespace Javaabu\Translatable;

use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // declare publishes
//        $this->commands([
//            \Javaabu\Translatable\Commands\ImplementTranslatablesForModel::class,
//        ]);
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/translatable.php' => config_path('translatable.php'),
            ], 'translatable-config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // merge package config with user defined config
        $this->mergeConfigFrom(__DIR__ . '/../config/translatable.php', 'translatable');
    }
}
