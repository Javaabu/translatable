<?php

namespace Javaabu\Translatable\Tests\Unit\Models;

use FilesystemIterator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
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
    }

    #[Test]
    public function it_can_check_if_is_current_language(): void
    {
        $lang = Languages::get('dv');
        Languages::setCurrentLocale('dv');

        $this->assertTrue($lang->isCurrent());
    }

    #[Test]
    public function it_searches_languages_by_their_names_and_codes(): void
    {
        Languages::get('dv')->update(['local_name' => 'ދިވެހި']);

        $this->assertEquals(['dv'], Language::search('ދިވެ')->pluck('code')->all());
        $this->assertEquals(['en'], Language::search('Engl')->pluck('code')->all());
        $this->assertEquals(['en', 'dv'], Language::search('')->orderBy('id')->pluck('code')->all());
        $this->assertEquals(['dv'], Language::active()->search('dv')->pluck('code')->all());
    }

    #[Test]
    public function it_names_admin_links_after_the_language(): void
    {
        $this->assertEquals('Dhivehi', Languages::get('dv')->admin_link_name);
    }

    #[Test]
    public function it_doesnt_depend_on_javaabu_helpers(): void
    {
        // Only AdminLanguage, which apps opt into, may use helpers' classes (see #11).
        // The middleware calls is_api_request() only behind function_exists().
        $sources = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 3) . '/src', FilesystemIterator::SKIP_DOTS));
        $uses_helpers = [];

        foreach ($sources as $file) {
            if ($file->getExtension() !== 'php' || $file->getFilename() === 'AdminLanguage.php') {
                continue;
            }

            if (preg_match('/Javaabu\\\\Helpers|add_query_arg\(/', file_get_contents($file->getPathname()))) {
                $uses_helpers[] = $file->getFilename();
            }
        }

        $this->assertSame([], $uses_helpers);
    }
}
