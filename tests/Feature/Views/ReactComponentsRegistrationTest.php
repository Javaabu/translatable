<?php

namespace Javaabu\Translatable\Tests\Feature\Views;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Inertia;
use Javaabu\Manifest\Manifest;
use Javaabu\Translatable\Testing\InteractsWithLanguages;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\TranslatableServiceProvider;
use PHPUnit\Framework\Attributes\Test;

class ReactComponentsRegistrationTest extends TestCase
{
    use InteractsWithLanguages;
    use RefreshDatabase;

    #[Test]
    public function it_ships_the_react_components_as_javaabu_translatable(): void
    {
        $package = app(Manifest::class)->package('translatable');

        $this->assertEquals('@javaabu/translatable', $package->importAlias());
        $this->assertEquals('js', $package->jsPath());
        $this->assertFileExists($package->path('js/index.ts'));
    }

    #[Test]
    public function it_registers_with_manifest_once(): void
    {
        (new TranslatableServiceProvider($this->app))->registerReactComponents();

        $this->assertTrue(app(Manifest::class)->hasPackage('translatable'));
    }

    #[Test]
    public function it_shares_the_languages_with_inertia_pages_when_the_views_are_inertia(): void
    {
        $this->setUpLanguages(['en', 'dv']);

        $this->assertNull(Inertia::getShared('translatable'));

        config(['translatable.views' => 'inertia']);
        (new TranslatableServiceProvider($this->app))->registerInertiaSharedData();

        $shared = value(Inertia::getShared('translatable'));

        $this->assertEquals(['en', 'dv'], array_column($shared['locales'], 'code'));
        $this->assertEquals('ދިވެހި', $shared['locales'][1]['native_name']);
    }

    #[Test]
    public function it_can_use_another_prop_or_not_share(): void
    {
        config(['translatable.views' => 'inertia', 'translatable.inertia.prop' => 'languages']);
        (new TranslatableServiceProvider($this->app))->registerInertiaSharedData();

        $this->assertNotNull(Inertia::getShared('languages'));

        Inertia::flushShared();
        config(['translatable.inertia.share' => false]);
        (new TranslatableServiceProvider($this->app))->registerInertiaSharedData();

        $this->assertNull(Inertia::getShared('languages'));
    }
}
