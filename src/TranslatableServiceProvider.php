<?php

namespace Javaabu\Translatable;

use Carbon\Carbon;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Javaabu\Translatable\DbTranslatable\DbTranslatableSchema;
use Javaabu\Translatable\JsonTranslatable\JsonTranslatableSchema;
use Javaabu\Translatable\Middleware\LocaleMiddleware;
use Javaabu\Translatable\Models\Language;

class TranslatableServiceProvider extends ServiceProvider
{
    protected array $migrations = [
        'create_languages_table',
    ];

    public function boot(): void
    {
        $this->offerPublishing();

        $this->registerRouteModelBindings();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'translatable');

        Blade::componentNamespace('Javaabu\\Translatable\\Views\\Components', 'translatable');

        $this->app->singleton(LanguageRegistrar::class, function ($app) {
            $config = $this->app['config']['translatable'];

            $language_class = $config['language_model'] ?? Language::class;
            $cache_manager = $this->app->make(CacheManager::class);

            $cache_expiration_time = Arr::get($config, 'cache.expiration_time');
            $cache_key = Arr::get($config, 'cache.key');
            $cache_driver = Arr::get($config, 'cache.driver');

            return new LanguageRegistrar(
                $language_class,
                $cache_manager,
                $cache_expiration_time,
                $cache_key,
                $cache_driver
            );
        });

        // Register the translatable macros for URL generation
        Redirect::macro('translateRoute', function ($routeName, $parameters = [], $status = 302, $headers = []) {
            $url = translate_route($routeName, $parameters);
            return redirect()->to($url, $status, $headers);
        });

        Redirect::macro('translateAction', function ($action, $parameters = [], $status = 302, $headers = []) {
            $url = translate_action($action, $parameters);
            return redirect()->to($url, $status, $headers);
        });

        Request::macro('portal', function (): string {
            $req         = request();
            $adminDomain = config('app.admin_domain');          // e.g. 'admin.ncs.test' or null
            $adminPrefix = config('app.admin_prefix', 'admin'); // e.g. 'admin'

            // admin via dedicated domain
            if ($adminDomain && $req->getHost() === $adminDomain) {
                return 'admin';
            }

            // admin via prefix: ncs.test/admin/...
            if ($req->segment(1) === $adminPrefix) {
                return 'admin';
            }

            // pattern: ncs.test/{locale}/{portal}
            return $req->segment(2) ?? 'public';
        });

        Request::macro('isPortal', function (string $portal): bool {
            return request()->portal() === $portal;
        });
    }

    public function register(): void
    {
        // merge package config with user defined config
        $this->mergeConfigFrom(__DIR__ . '/../config/translatable.php', 'translatable');
        $this->registerSingletons();
        $this->registerDatabaseMacros();

        // Register middleware alias
        app('router')->aliasMiddleware('language', LocaleMiddleware::class);
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
     * Database Migration Macros that can be used instead
     * of calling the static functions directly.
     *
     * @return void
     */
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

    /**
     * Offer publishing
     *
     * @return void
     */
    public function offerPublishing(): void
    {
        // declare publishes
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/translatable.php' => config_path('translatable.php'),
            ], 'translatable-config');

            // Publish views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/translatable'),
            ], 'translatable-views');

            // Publish flags
            $this->publishes([
                __DIR__ . '/../resources/dist/flags' => public_path('vendors/flags'),
            ], 'translatable-flags');

            // Publish migrations
            foreach ($this->migrations as $migration) {
                $vendorMigration = __DIR__ . '/../database/migrations/' . $migration . '.php';
                $appMigration = $this->generateMigrationName($migration, now()->addSecond());

                $this->publishes([
                    $vendorMigration => $appMigration,
                ], 'translatable-migrations');
            }
        }
    }

    /**
     * Register route model binding with the given language class
     *
     * @return void
     */
    public function registerRouteModelBindings(): void
    {
        $config = $this->app['config']['translatable'];

        $language_class = $config['language_model'] ?? Language::class;

        Route::bind('language', function ($value, $route) use ($language_class) {
            return $language_class::where('locale', $value)->firstOrFail();
        });
    }

    protected function generateMigrationName(string $migrationFileName, Carbon $now): string
    {
        $migrationsPath = 'migrations/' . dirname($migrationFileName) . '/';
        $migrationFileName = basename($migrationFileName);

        $len = strlen($migrationFileName) + 4;

        if (Str::contains($migrationFileName, '/')) {
            $migrationsPath .= Str::of($migrationFileName)->beforeLast('/')->finish('/');
            $migrationFileName = Str::of($migrationFileName)->afterLast('/');
        }

        foreach (glob(database_path("{$migrationsPath}*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName . '.php')) {
                return $filename;
            }
        }

        $timestamp = $now->format('Y_m_d_His');
        $migrationFileName = Str::of($migrationFileName)->snake()->finish('.php');

        return database_path($migrationsPath . $timestamp . '_' . $migrationFileName);
    }
}
