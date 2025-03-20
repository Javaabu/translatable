<?php

namespace Javaabu\Translatable;

use Illuminate\Cache\CacheManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Javaabu\Translatable\DbTranslatable\DbTranslatableSchema;
use Javaabu\Translatable\JsonTranslatable\JsonTranslatableSchema;
use Javaabu\Translatable\Middleware\LocaleMiddleware;
use Javaabu\Translatable\Models\Language;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TranslatableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('translatable')
            ->hasConfigFile()
            // ->hasViews()
            ->hasMigration('create_languages_table');
    }

    public function packageBooted(): void
    {
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

    public function packageRegistered(): void
    {
        $this->registerSingletons();
        $this->registerDatabaseMacros();
        $this->registerMiddlewareAliases();
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

    public function registerDatabaseMacros(): void
    {
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

    public function registerMiddlewareAliases(): void
    {
        app('router')->aliasMiddleware('language', LocaleMiddleware::class);
    }
}
