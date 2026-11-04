<?php

namespace Javaabu\Translatable\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Helpers\AdminModel\AdminModel;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Models\AdminLanguage;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('helpers')]
class AdminLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('translatable.language_model', AdminLanguage::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        AdminLanguage::create(['name' => 'English', 'code' => 'en', 'locale' => 'en', 'flag' => '🇬🇧', 'is_rtl' => false, 'active' => true]);
        AdminLanguage::create(['name' => 'Dhivehi', 'code' => 'dv', 'locale' => 'dv', 'local_name' => 'ދިވެހި', 'flag' => '🇲🇻', 'is_rtl' => true, 'active' => true]);
    }

    #[Test]
    public function it_is_a_helpers_admin_model(): void
    {
        $language = Languages::get('dv');

        $this->assertInstanceOf(AdminLanguage::class, $language);
        $this->assertInstanceOf(AdminModel::class, $language);
        $this->assertEquals('Dhivehi', $language->admin_link_name);
        $this->assertEquals('Dhivehi', $language->logging_name);
        $this->assertEquals(['dv'], AdminLanguage::search('ދިވެ')->pluck('code')->all());
    }

    #[Test]
    public function it_keeps_the_base_morph_class(): void
    {
        $this->assertEquals(Language::class, (new AdminLanguage)->getMorphClass());
    }
}
