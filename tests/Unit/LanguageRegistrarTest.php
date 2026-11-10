<?php

namespace Javaabu\Translatable\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\LanguageRegistrar;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Support\LanguagePresets;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LanguageRegistrarTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_loads_the_languages_once_per_request(): void
    {
        Language::query()->create(LanguagePresets::get('en'));

        app()->forgetScopedInstances();

        DB::enableQueryLog();

        Languages::all();
        Languages::all();
        Languages::get('en');

        $this->assertCount(1, DB::getQueryLog());

        // The next request reads them from the cache
        app()->forgetScopedInstances();
        DB::flushQueryLog();
        Languages::all();
        $this->assertCount(0, DB::getQueryLog());
        $this->assertEquals(['en'], Languages::all()->pluck('code')->all());
    }

    #[Test]
    public function it_does_not_cache_an_empty_list(): void
    {
        $this->assertTrue(Languages::all()->isEmpty());
        $this->assertNull(Cache::get('translation.languages.cache'));

        DB::table('languages')->insert(LanguagePresets::get('dv'));
        app()->forgetScopedInstances();

        $this->assertEquals(['dv'], Languages::all()->pluck('code')->all());
    }

    #[Test]
    public function it_forgets_the_languages_when_one_changes(): void
    {
        $english = Language::query()->create(LanguagePresets::get('en'));
        Language::query()->create(LanguagePresets::get('dv'));

        $this->assertCount(2, Languages::all());

        $english->update(['active' => false]);

        $this->assertEquals(['dv'], Languages::all()->pluck('code')->values()->all());

        $english->delete();
        $english->restore();
        $english->update(['active' => true]);

        $this->assertCount(2, Languages::all());
    }

    #[Test]
    public function it_returns_no_languages_when_the_table_is_missing(): void
    {
        DB::statement('drop table languages');

        $registrar = app(LanguageRegistrar::class);
        $registrar->forgetCachedLanguages();

        $this->assertTrue($registrar->getLanguages()->isEmpty());
    }
}
