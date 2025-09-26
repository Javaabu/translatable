<?php

namespace Javaabu\Translatable\Tests;

use Javaabu\Helpers\HelpersServiceProvider;
use Javaabu\Translatable\Tests\TestSupport\Providers\TestServiceProvider;
use Javaabu\Translatable\TranslatableServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');

        $this->app['config']->set('session.serialization', 'php');
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
        return [
            TranslatableServiceProvider::class,
            HelpersServiceProvider::class,
            TestServiceProvider::class,
        ];
    }
}
