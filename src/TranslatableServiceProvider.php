<?php

namespace Javaabu\Translatable;

use DateTimeInterface;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Javaabu\Manifest\Manifest;
use Javaabu\Translatable\Commands\AddLanguagesCommand;
use Javaabu\Translatable\DbTranslatable\DbTranslatableSchema;
use Javaabu\Translatable\JsonTranslatable\JsonTranslatableSchema;
use Javaabu\Translatable\Middleware\LocaleMiddleware;
use Javaabu\Translatable\Models\Language;

class TranslatableServiceProvider extends ServiceProvider
{
    protected array $migrations = [
        'create_languages_table',
        'add_local_name_to_languages_table',
    ];

    public function boot(): void
    {
        $this->offerPublishing();

        $this->registerRouteModelBindings();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'translatable');

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'translatable');

        Blade::componentNamespace('Javaabu\\Translatable\\Views\\Components', 'translatable');

        $this->registerInertiaSharedData();

        $this->registerReactComponents();

        // Register the translatable macros for URL generation
        Redirect::macro('translateRoute', function ($routeName, $parameters = [], $status = 302, $headers = []) {
            $url = translate_route($routeName, $parameters);

            return redirect()->to($url, $status, $headers);
        });

        Redirect::macro('translateAction', function ($action, $parameters = [], $status = 302, $headers = []) {
            $url = translate_action($action, $parameters);

            return redirect()->to($url, $status, $headers);
        });

        Request::macro('portal', function (): ?string {
            $req = request();
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
            return $req->segment(2) ?? null;
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
        $this->app->scoped(LanguageRegistrar::class, function ($app) {
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
                $cache_driver,
            );
        });

        $this->app->singleton(Translatable::class, function () {
            return new Translatable;
        });
        $this->app->alias(Translatable::class, 'translatable');

        $this->app->scoped(Languages::class, function () {
            return new Languages;
        });
        $this->app->alias(Languages::class, 'languages');
    }

    /**
     * Ship the React components to apps as @javaabu/translatable, when javaabu/manifest is installed
     */
    public function registerReactComponents(): void
    {
        $this->callAfterResolving(Manifest::class, function ($manifest) {
            if ($manifest->hasPackage('translatable')) {
                return;
            }

            // No Blade assets: the React components are only imported through @javaabu/translatable,
            // so Blade apps aren't asked for their npm packages (manifest's pages scope)
            $manifest->package('translatable', __DIR__ . '/../resources')
                ->js('js')
                ->npm([
                    '@javaabu/forms' => 'github:Javaabu/react-forms#next-gen',
                    '@javaabu/i18n' => 'github:Javaabu/i18n#build-on-git-install',
                ], peer_dependencies: [
                    'react' => '^18.3 || ^19.0',
                    'react-dom' => '^18.3 || ^19.0',
                ], for: 'pages');
        });
    }

    /**
     * Share the languages with Inertia pages when the views are Inertia React
     */
    public function registerInertiaSharedData(): void
    {
        if (config('translatable.views') !== 'inertia') {
            return;
        }

        if (! config('translatable.inertia.share', true)) {
            return;
        }

        if (! class_exists(Inertia::class)) {
            return;
        }

        Inertia::share(config('translatable.inertia.prop', 'translatable'), fn () => Translatable::shared());
    }

    /**
     * Database Migration Macros that can be used instead
     * of calling the static functions directly.
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
     */
    public function offerPublishing(): void
    {
        // declare publishes
        if ($this->app->runningInConsole()) {
            $this->commands([
                AddLanguagesCommand::class,
            ]);

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
            foreach ($this->migrations as $index => $migration) {
                $vendorMigration = __DIR__ . '/../database/migrations/' . $migration . '.php';
                $appMigration = $this->generateMigrationName($migration, now()->addSeconds($index + 1));

                $this->publishes([
                    $vendorMigration => $appMigration,
                ], 'translatable-migrations');
            }
        }
    }

    /**
     * Register route model binding with the given language class
     */
    public function registerRouteModelBindings(): void
    {
        $config = $this->app['config']['translatable'];

        $language_class = $config['language_model'] ?? Language::class;

        Route::bind('language', function ($value, $route) use ($language_class) {
            return $language_class::where('locale', $value)->firstOrFail();
        });
    }

    protected function generateMigrationName(string $migrationFileName, DateTimeInterface $now): string
    {
        $migrationsPath = 'migrations/' . dirname($migrationFileName) . '/';
        $migrationFileName = basename($migrationFileName);

        $len = mb_strlen($migrationFileName) + 4;

        if (Str::contains($migrationFileName, '/')) {
            $migrationsPath .= Str::of($migrationFileName)->beforeLast('/')->finish('/');
            $migrationFileName = Str::of($migrationFileName)->afterLast('/');
        }

        foreach (glob(database_path("{$migrationsPath}*.php")) as $filename) {
            if ((mb_substr($filename, -$len) === $migrationFileName . '.php')) {
                return $filename;
            }
        }

        $timestamp = $now->format('Y_m_d_His');
        $migrationFileName = Str::of($migrationFileName)->snake()->finish('.php');

        return database_path($migrationsPath . $timestamp . '_' . $migrationFileName);
    }
}
