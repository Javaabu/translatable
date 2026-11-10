<?php

namespace Javaabu\Translatable\Tests\Feature\Views;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Javaabu\Manifest\Enums\NpmScopes;
use Javaabu\Manifest\Manifest;
use Javaabu\Manifest\ManifestServiceProvider;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\TranslatableServiceProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * The npm packages translatable asks apps for: only Inertia apps need them (Javaabu/manifest#17)
 */
class ManifestCheckTest extends TestCase
{
    /**
     * Only translatable registers with manifest, so the checks are about its own needs
     */
    protected function getPackageProviders($app): array
    {
        return [
            TranslatableServiceProvider::class,
            ManifestServiceProvider::class,
        ];
    }

    protected function tearDown(): void
    {
        File::delete(base_path('package.json'));
        File::deleteDirectory(base_path('node_modules'));

        parent::tearDown();
    }

    /**
     * Write the app's package.json and node_modules
     *
     * @param  array<string, string>  $installed  name => version
     */
    protected function app(array $installed): void
    {
        File::put(base_path('package.json'), json_encode(['devDependencies' => $installed]));

        foreach ($installed as $name => $version) {
            File::ensureDirectoryExists(base_path("node_modules/{$name}"));
            File::put(base_path("node_modules/{$name}/package.json"), json_encode(['name' => $name, 'version' => $version]));
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function check(array $options = []): array
    {
        Artisan::call('manifest:check', ['--json' => true, ...$options]);

        return collect(json_decode(Artisan::output(), true))->keyBy('name')->all();
    }

    #[Test]
    public function it_declares_its_npm_packages_for_pages_only(): void
    {
        $package = app(Manifest::class)->package('translatable');

        // No Blade asset imports anything: the React components are only imported through @javaabu/translatable
        $this->assertSame([], $package->assets());
        $this->assertSame([], $package->npmDependencies(NpmScopes::ASSETS));
        $this->assertSame([], $package->npmPeerDependencies(NpmScopes::ASSETS));

        $this->assertEquals(['@javaabu/forms', '@javaabu/i18n'], array_keys($package->npmDependencies(NpmScopes::PAGES)));
        $this->assertEquals(['react', 'react-dom'], array_keys($package->npmPeerDependencies(NpmScopes::PAGES)));
    }

    #[Test]
    public function a_blade_app_passes_without_the_react_packages(): void
    {
        $this->app(['vite' => '8.3.5', 'laravel-vite-plugin' => '3.2.0']);

        $this->assertSame([], $this->check());

        $this->artisan('manifest:check')->assertSuccessful();
    }

    #[Test]
    public function an_inertia_app_is_asked_for_the_react_packages(): void
    {
        $this->app(['@inertiajs/react' => '2.3.0', 'vite' => '8.3.5', 'laravel-vite-plugin' => '3.2.0']);

        $rows = $this->check();

        foreach (['@javaabu/forms', '@javaabu/i18n', 'react', 'react-dom'] as $name) {
            $this->assertEquals('missing', $rows[$name]['status'], $name);
            $this->assertEquals(['pages'], $rows[$name]['scopes'], $name);
            $this->assertEquals(['translatable'], $rows[$name]['needed_by'], $name);
        }

        $this->artisan('manifest:check')->assertFailed();
    }

    #[Test]
    public function an_inertia_app_passes_with_the_react_packages(): void
    {
        $this->app([
            '@inertiajs/react' => '2.3.0',
            '@javaabu/forms' => '1.0.0',
            '@javaabu/i18n' => '1.0.0',
            'react' => '19.2.0',
            'react-dom' => '19.2.0',
            'vite' => '8.3.5',
            'laravel-vite-plugin' => '3.2.0',
        ]);

        // github: constraints aren't version ranges, so any installed version satisfies them
        $this->artisan('manifest:check')->assertSuccessful();
    }
}
