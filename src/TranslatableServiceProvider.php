<?php

namespace Javaabu\Translatable;

use Illuminate\Cache\CacheManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Javaabu\Translatable\DbTranslatable\DbTranslatableSchema;
use Javaabu\Translatable\JsonTranslatable\JsonTranslatableSchema;
use Javaabu\Translatable\Models\Language;

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

        $this->app->singleton(LanguageRegistrar::class, function ($app) {
            $config = $this->app['config']['translatable'];

            $language_class = $config['language_model'] ?? Language::class;
            $cache_manager = $this->app->make(CacheManager::class);

            $cache_expiration_time = Arr::get($config, 'cache.expiration_time');
            $cache_key = Arr::get($config, 'cache.key');

            return new LanguageRegistrar(
                $language_class,
                $cache_manager,
                $cache_expiration_time,
                $cache_key
            );
        });
    }

    public function registerSingletons(): void
    {
        $this->app->singleton(Translatable::class, function () {
            return new Translatable();
        });

        $this->app->alias(Translatable::class, 'translatable');


        $this->app->singleton(Languages::class, function () {
            return new Languages();
        });

        $this->app->alias(Languages::class, 'languages');

    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // merge package config with user defined config
        $this->mergeConfigFrom(__DIR__ . '/../config/translatable.php', 'translatable');

        $this->registerSingletons();

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
