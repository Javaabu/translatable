<?php

namespace Javaabu\Translatable\Tests;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Javaabu\Forms\FormsServiceProvider;
use Javaabu\Manifest\ManifestServiceProvider;
use Javaabu\Translatable\Tests\TestSupport\Providers\TestServiceProvider;
use Javaabu\Translatable\TranslatableServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Use CarbonImmutable for now() and model dates, as Laravel's starter kits do
     * (Date::use(CarbonImmutable::class)). TRANSLATABLE_IMMUTABLE_DATES=1 turns it on for every test.
     */
    protected bool $immutable_dates = false;

    protected function setUp(): void
    {
        // Before the app boots, so the service providers see the app's date class
        if ($this->immutable_dates || filter_var(getenv('TRANSLATABLE_IMMUTABLE_DATES'), FILTER_VALIDATE_BOOL)) {
            Date::use(CarbonImmutable::class);
        }

        parent::setUp();

        $this->app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');

        $this->app['config']->set('session.serialization', 'php');

        // Views are checked for markup; injection is javaabu/manifest's job
        $this->app['config']->set('manifest.inject', false);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Date::useDefault();
    }

    protected function defineRoutes($router): void
    {
        $router->get('{language}/articles/{article}', function ($lang, $article) {
            return "Article {$article} in {$lang}";
        })->name('articles.show');

        $router->get('{language}/posts/{post}', function ($lang, $post) {
            return "Post {$post} in {$lang}";
        })->name('posts.show');
    }

    protected function getPackageProviders($app): array
    {
        // javaabu/helpers is a dev dependency for AdminLanguage only; the rest runs without it
        return array_values(array_filter([
            TranslatableServiceProvider::class,
            class_exists('Javaabu\\Helpers\\HelpersServiceProvider') ? 'Javaabu\\Helpers\\HelpersServiceProvider' : null,
            ManifestServiceProvider::class,
            FormsServiceProvider::class,
            TestServiceProvider::class,
        ]));
    }
}
