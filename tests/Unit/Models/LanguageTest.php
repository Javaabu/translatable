<?php

namespace Javaabu\Translatable\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LanguageTest extends TestCase
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
    }

    #[Test]
    public function it_can_check_if_is_current_language(): void
    {
        $lang = Languages::get('dv');
        Languages::setCurrentLocale('dv');

        $this->assertTrue($lang->isCurrent());
    }
}
