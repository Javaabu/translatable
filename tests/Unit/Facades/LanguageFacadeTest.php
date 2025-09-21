<?php

namespace Javaabu\Translatable\Tests\Unit\Facades;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LanguageFacadeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::create([
            'name'   => 'English',
            'code'   => 'en',
            'locale' => 'en',
            'flag'   => '🇬🇧',
            'is_rtl' => false,
            'active' => true,
        ]);
        Language::create([
            'name'   => 'Dhivehi',
            'code'   => 'dv',
            'locale' => 'dv',
            'flag'   => '🇲🇻',
            'is_rtl' => true,
            'active' => true,
        ]);
        Language::create([
            'name'   => 'Japanese',
            'code'   => 'jp',
            'locale' => 'jp',
            'flag'   => '🇯🇵',
            'is_rtl' => false,
            'active' => true,
        ]);
    }

    #[Test]
    public function it_can_get_all_except_current_language()
    {
        Languages::setCurrentLocale('en');
        $all_languages = Languages::allExceptCurrent();

        $this->assertCount(2, $all_languages);
        $this->assertEquals('Dhivehi', $all_languages->first()->name);
        $this->assertEquals('Japanese', $all_languages->last()->name);
    }

    #[Test]
    public function it_can_get_current_language_code()
    {
        Languages::setCurrentLocale('en');
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
        Languages::setCurrentLocale('en');
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
        Languages::setCurrentLocale('en');
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

    #[Test]
    public function it_can_get_direction_for_languages()
    {
        $this->assertEquals('rtl', Languages::getDirection('dv'));
        $this->assertEquals('ltr', Languages::getDirection('en'));

        Languages::setCurrentLocale('dv');
        $this->assertEquals('rtl', Languages::getDirection());

        Languages::setCurrentLocale('en');
        $this->assertEquals('ltr', Languages::getDirection());
    }

    #[Test]
    public function is_can_check_if_a_locale_is_rtl()
    {
        $this->withoutExceptionHandling();

        Languages::setCurrentLocale('dv');
        $this->assertTrue(Languages::isRtl());

        Languages::setCurrentLocale('en');
        $this->assertFalse(Languages::isRtl());
    }
}
