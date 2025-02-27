<?php

namespace Javaabu\Translatable;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;
use Javaabu\Translatable\DbTranslatable\DbTranslatableSchema;
use Javaabu\Translatable\JsonTranslatable\JsonTranslatableSchema;

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

        // add macros for easier database initialization
        Blueprint::macro('dbTranslatable', function () {
            DbTranslatableSchema::columns($this);
        });

        Blueprint::macro('dropDbTranslatable', function () {
            DbTranslatableSchema::revert($this);
        });

        Blueprint::macro('jsonTranslatable', function () {
            JsonTranslatableSchema::columns($this);
        });

        Blueprint::macro('dropJsonTranslatable', function () {
            JsonTranslatableSchema::revert($this);
        });
    }
}
