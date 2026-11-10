<?php

namespace Javaabu\Translatable\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Support\LanguagePresets;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AddLanguagesCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_adds_the_default_locale_and_dhivehi_by_default(): void
    {
        $this->artisan('translatable:add-languages')
            ->expectsOutputToContain('Added 2 language(s).')
            ->assertSuccessful();

        $this->assertDatabaseHas('languages', ['code' => 'en', 'name' => 'English', 'is_rtl' => false, 'active' => true]);
        $this->assertDatabaseHas('languages', ['code' => 'dv', 'local_name' => 'ދިވެހި', 'flag' => 'mv', 'is_rtl' => true]);
    }

    #[Test]
    public function it_skips_languages_that_exist_and_warns_about_unknown_codes(): void
    {
        Language::query()->create(LanguagePresets::get('en'));

        $this->artisan('translatable:add-languages', ['codes' => ['en', 'ar', 'xx'], '--inactive' => true])
            ->expectsOutputToContain('`en` is already in the languages table.')
            ->expectsOutputToContain("There's no preset for `xx`")
            ->expectsOutputToContain('Added 2 language(s).')
            ->assertSuccessful();

        $this->assertDatabaseHas('languages', ['code' => 'ar', 'is_rtl' => true, 'active' => false]);
        $this->assertDatabaseHas('languages', ['code' => 'xx', 'name' => 'XX', 'active' => false]);
        $this->assertDatabaseCount('languages', 3);
    }

    #[Test]
    public function it_knows_its_presets(): void
    {
        $this->assertTrue(LanguagePresets::has('dv'));
        $this->assertFalse(LanguagePresets::has('xx'));
        $this->assertContains('en', LanguagePresets::codes());
    }
}
