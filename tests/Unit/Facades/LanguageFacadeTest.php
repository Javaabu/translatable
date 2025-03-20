<?php

namespace Javaabu\Translatable\Tests\Unit\Facades;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LanguageFacadeTest extends TestCase {
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();


        Language::create([
            'name' => 'English',
            'code' => 'en',
            'locale' => 'en',
            'flag' => '🇬🇧',
            'is_rtl' => false,
            'active' => true,
        ]);
        Language::create([
            'name' => 'Dhivehi',
            'code' => 'dv',
            'locale' => 'dv',
            'flag' => '🇲🇻',
            'is_rtl' => true,
            'active' => true,
        ]);
        Language::create([
            'name' => 'Japanese',
            'code' => 'jp',
            'locale' => 'jp',
            'flag' => '🇯🇵',
            'is_rtl' => false,
            'active' => true,
        ]);
    }

    #[Test]
    public function it_can_get_all_except_current_language()
    {
        $all_languages = Languages::allExceptCurrent();

        $this->assertCount(2, $all_languages);
        $this->assertEquals('Dhivehi', $all_languages->first()->name);
        $this->assertEquals('Japanese', $all_languages->last()->name);
    }

    #[Test]
    public function it_can_get_current_language_code()
    {
        $lang = Languages::get();
        $this->assertEquals('en', $lang->code);
    }

    #[Test]
    public function it_can_get_default_language()
    {
        $lang = Languages::default();

        $this->assertEquals('en', $lang->code);
    }

    #[Test]
    public function it_can_set_to_translation_locale()
    {
        config()->set('translatable.default_locale', 'dv');
        app()->setLocale('fr');
        Languages::setToTranslationLocale();
        $this->assertEquals('dv', app()->getLocale());
    }

    #[Test]
    public function it_can_set_to_app_locale()
    {
        app()->setLocale('fr');
        Languages::setToAppLocale();
        $this->assertEquals('en', app()->getLocale());
    }

    #[Test]
    public function it_can_check_if_language_is_current_language()
    {
        $is_current = Languages::isCurrent('en');
        $is_not_current = Languages::isCurrent('fr');

        $this->assertTrue($is_current);
        $this->assertFalse($is_not_current);

        $is_current = Languages::isCurrent(Languages::get('en'));
        $this->assertTrue($is_current);
    }

    #[Test]
    public function it_can_check_if_language_is_default_language()
    {
        config()->set('translatable.default_locale', 'dv');
        $is_default = Languages::isDefault('dv');
        $is_not_default = Languages::isDefault('en');

        $this->assertTrue($is_default);
        $this->assertFalse($is_not_default);

        // using language instance
        $is_default = Languages::isDefault(Languages::get('dv'));
        $this->assertTrue($is_default);

        // fallback to current language
        $is_not_default = Languages::isDefault();
        $this->assertFalse($is_not_default);
    }
}
